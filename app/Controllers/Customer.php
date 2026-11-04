<?php

namespace App\Controllers;

use App\Models\MenuModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\TableModel;

class Customer extends BaseController
{
    private $menuModel;
    private $orderModel;
    private $orderItemModel;
    private $tableModel;

    public function __construct()
    {
        $this->menuModel = new MenuModel();
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->tableModel = new TableModel();
    }

    // HALAMAN UTAMA: Cek Identitas Dulu Sebelum Masuk Katalog
    public function index()
    {
        $nomorMeja = $this->request->getGet('meja') ?? '01';
        $currentSession = session_id();

        $mejaData = $this->tableModel->where('nomor_meja', $nomorMeja)->first();

        if ($mejaData) {
            // AUTO-RESET 30 MENIT
            $orderTerakhir = $this->orderModel
                ->where('nomor_meja', $nomorMeja)
                ->orderBy('id', 'DESC')
                ->first();

            if ($orderTerakhir) {
                $waktuPatokan = (!empty($orderTerakhir['waktu_siap'])) ? $orderTerakhir['waktu_siap'] : $orderTerakhir['created_at'];
                $waktuLalu = strtotime($waktuPatokan);
                $waktuSekarang = time();
                
                $selisihMenit = ($waktuSekarang - $waktuLalu) / 60;

                // Jika sudah lewat 30 menit, meja otomatis dikosongkan & sesi identitas dihapus
                if ($selisihMenit > 30 && $mejaData['status'] == 'terisi') {
                    $this->tableModel->update($mejaData['id'], [
                        'status'         => 'tersedia',
                        'device_session' => null
                    ]);
                    
                    // HAPUS SESI IDENTITAS PENGUNJUNG OTOMATIS
                    session()->remove(['tipe_pengguna', 'nomor_identitas', 'nama_pemesan', 'jurusan_instansi']);
                    
                    $mejaData['status'] = 'tersedia';
                }
            }

            // CEK GAGAL SCAN
            if ($mejaData['status'] == 'terisi' && $mejaData['device_session'] !== $currentSession) {
                return view('customer/meja_terpakai', ['nomor_meja' => $nomorMeja]);
            }
        }

        session()->set('nomor_meja', $nomorMeja);

        // JIKA IDENTITAS BELUM DIISI, LEMPAR KE HALAMAN FORM IDENTITAS DULU
        if (!session()->has('tipe_pengguna') || !session()->has('nama_pemesan')) {
            return view('customer/identitas', ['nomor_meja' => $nomorMeja]);
        }

        // JIKA SUDAH ISI IDENTITAS, LANJUT KE KATALOG MENU
        $daftarMenu = $this->menuModel
            ->select('menus.*, tenants.nama_tenant')
            ->join('tenants', 'tenants.id = menus.tenant_id')
            ->where('menus.status', 'tersedia')
            ->findAll();

        $data = [
            'nomor_meja'       => $nomorMeja,
            'nama_pemesan'     => session()->get('nama_pemesan'),
            'tipe_pengguna'    => session()->get('tipe_pengguna'),
            'jurusan_instansi' => session()->get('jurusan_instansi'),
            'daftar_menu'      => $daftarMenu
        ];

        return view('customer/index', $data);
    }

    // METHOD JSON: Menyediakan data menu aktif untuk fitur Live Auto-Sync customer
    public function getMenuJson()
    {
        $menus = $this->menuModel
            ->select('menus.*, tenants.nama_tenant')
            ->join('tenants', 'tenants.id = menus.tenant_id')
            ->where('menus.status', 'tersedia')
            ->findAll();

        return $this->response->setJSON($menus ?? []);
    }

    // METHOD AJAX: Cari data mahasiswa/dosen berdasarkan NIP/NPM lengkap atau belakangnya saja
    public function cariCivitas()
    {
        $keyword = $this->request->getGet('q');
        $tipe = $this->request->getGet('tipe'); // mahasiswa atau dosen

        $db = \Config\Database::connect();
        $result = $db->table('master_civitas')
                     ->where('tipe', $tipe)
                     ->like('nomor_induk', $keyword, 'both')
                     ->get()
                     ->getRowArray();

        if ($result) {
            // Mengambil nilai jurusan secara fleksibel dari berbagai kemungkinan nama kolom database
            $jurusanVal = $result['jurusan_instansi'] ?? $result['jurusan'] ?? $result['prodi'] ?? '';

            return $this->response->setJSON([
                'status' => 'found', 
                'result' => [
                    'nama'             => $result['nama'],
                    'jurusan_instansi' => $jurusanVal
                ]
            ]);
        }

        return $this->response->setJSON(['status' => 'not_found']);
    }

    // METHOD: Simpan identitas ke session lalu arahkan ke katalog
    public function simpanIdentitas()
    {
        $nomorMeja = $this->request->getPost('nomor_meja') ?? '01';

        session()->set([
            'tipe_pengguna'    => $this->request->getPost('tipe_pengguna'),
            'nomor_identitas'  => $this->request->getPost('nomor_identitas') ?? null,
            'nama_pemesan'     => $this->request->getPost('nama_pemesan'),
            'jurusan_instansi' => $this->request->getPost('jurusan_instansi'),
        ]);

        return redirect()->to('/customer?meja=' . $nomorMeja);
    }

    // METHOD: Ganti / Reset Identitas Pengunjung
    public function logoutIdentitas()
    {
        $nomorMeja = session()->get('nomor_meja') ?? '01';
        session()->remove(['tipe_pengguna', 'nomor_identitas', 'nama_pemesan', 'jurusan_instansi']);
        return redirect()->to('/customer?meja=' . $nomorMeja);
    }

    public function checkout()
    {
        $nomorMeja = session()->get('nomor_meja') ?? '01';
        $currentSession = session_id();
        $arrayMenuId = $this->request->getPost('menu_id');
        $arrayQty = $this->request->getPost('qty');
        $metodePembayaran = $this->request->getPost('metode_pembayaran') ?? 'qris';

        if (empty($arrayMenuId)) {
            return redirect()->back();
        }

        $mejaData = $this->tableModel->where('nomor_meja', $nomorMeja)->first();
        if ($mejaData && $mejaData['status'] == 'terisi' && $mejaData['device_session'] !== $currentSession) {
            return view('customer/meja_terpakai', ['nomor_meja' => $nomorMeja]);
        }

        $totalBayar = 0;
        $itemPesanan = [];

        foreach ($arrayMenuId as $index => $idMenu) {
            $jumlahBeli = (int) $arrayQty[$index];

            if ($jumlahBeli > 0) {
                $detailMenu = $this->menuModel->find($idMenu);
                if ($detailMenu) {
                    $subtotal = $detailMenu['harga'] * $jumlahBeli;
                    $totalBayar += $subtotal;

                    $itemPesanan[] = [
                        'tenant_id' => $detailMenu['tenant_id'],
                        'menu_id'   => $detailMenu['id'],
                        'qty'       => $jumlahBeli,
                        'harga'     => $detailMenu['harga']
                    ];
                }
            }
        }

        if (empty($itemPesanan)) {
            return redirect()->back();
        }

        $kodeTransaksi = 'TRX-' . date('YmdHis');

        // Jika metode QRIS status bayar awal 'pending', jika tunai 'lunas'
        $statusBayar = ($metodePembayaran == 'qris') ? 'pending' : 'lunas';

        // INSERT PESANAN BESERTA DATA IDENTITAS DARI SESSION
        $this->orderModel->insert([
            'kode_transaksi'   => $kodeTransaksi,
            'nomor_meja'       => $nomorMeja,
            'tipe_pengguna'    => session()->get('tipe_pengguna') ?? 'guest',
            'nomor_identitas'  => session()->get('nomor_identitas') ?? null,
            'nama_pemesan'     => session()->get('nama_pemesan') ?? 'Tamu',
            'jurusan_instansi' => session()->get('jurusan_instansi') ?? '-',
            'total_bayar'      => $totalBayar,
            'status_bayar'     => $statusBayar
        ]);

        $idOrderBaru = $this->orderModel->insertID();

        foreach ($itemPesanan as $item) {
            $this->orderItemModel->insert([
                'order_id'     => $idOrderBaru,
                'tenant_id'    => $item['tenant_id'],
                'menu_id'      => $item['menu_id'],
                'qty'          => $item['qty'],
                'harga_satuan' => $item['harga'],
                'status_dapur' => 'menunggu'
            ]);
        }

        // KUNCI MEJA SAAT CHECKOUT BERHASIL
        if ($mejaData) {
            $this->tableModel->update($mejaData['id'], [
                'status'         => 'terisi',
                'device_session' => $currentSession
            ]);
        }

        // Jika memilih QRIS, arahkan ke halaman pembayaran QRIS timer
        if ($metodePembayaran == 'qris') {
            return redirect()->to('/customer/qris/' . $idOrderBaru);
        }

        return redirect()->to('/customer/sukses/' . $idOrderBaru);
    }

    // HALAMAN SIMULASI PEMBAYARAN QRIS + TIMER
    public function qris($idOrder)
    {
        $dataOrder = $this->orderModel->find($idOrder);
        if (!$dataOrder) {
            return redirect()->to('/customer');
        }

        $data = ['order' => $dataOrder];
        return view('customer/qris_payment', $data);
    }

    // METHOD AJAX: Proses konfirmasi sukses pembayaran QRIS simulasi
    public function prosesBayarQris($idOrder)
    {
        $this->orderModel->update($idOrder, ['status_bayar' => 'lunas']);
        return $this->response->setJSON(['status' => 'success']);
    }

    public function sukses($idOrder)
    {
        $dataOrder = $this->orderModel->find($idOrder);
        
        if (!$dataOrder) {
            return redirect()->to('/customer');
        }

        $detailItems = $this->orderItemModel
            ->select('order_items.*, menus.nama_menu, tenants.nama_tenant')
            ->join('menus', 'menus.id = order_items.menu_id')
            ->join('tenants', 'tenants.id = order_items.tenant_id')
            ->where('order_id', $idOrder)
            ->findAll();

        $data = [
            'order' => $dataOrder,
            'items' => $detailItems
        ];

        return view('customer/sukses', $data);
    }

    public function getOrderStatusJson($idOrder)
    {
        $dataOrder = $this->orderModel->find($idOrder);
        
        if (!$dataOrder) {
            return $this->response->setJSON(['status' => 'not_found']);
        }

        $detailItems = $this->orderItemModel
            ->select('order_items.*, menus.nama_menu, tenants.nama_tenant')
            ->join('menus', 'menus.id = order_items.menu_id')
            ->join('tenants', 'tenants.id = order_items.tenant_id')
            ->where('order_id', $idOrder)
            ->findAll();

        return $this->response->setJSON([
            'order' => $dataOrder,
            'items' => $detailItems
        ]);
    }

    // Method tombol "Sudah Diterima" / Selesai
    public function terimaMakanan($idOrder)
    {
        try {
            $dataOrder = $this->orderModel->find($idOrder);

            if ($dataOrder) {
                try {
                    $this->orderModel->update($idOrder, [
                        'waktu_siap' => date('Y-m-d H:i:s')
                    ]);
                } catch (\Exception $e) {
                    // Abaikan jika kolom belum ada
                }

                // 1. KOSONGKAN MEJA
                $db = \Config\Database::connect();
                $db->table('tables')
                   ->where('nomor_meja', $dataOrder['nomor_meja'])
                   ->update([
                       'status'         => 'tersedia',
                       'device_session' => null
                   ]);

                // 2. HAPUS SESI IDENTITAS AGAR KEMBALI KE FORM INPUT NAMA
                session()->remove(['tipe_pengguna', 'nomor_identitas', 'nama_pemesan', 'jurusan_instansi']);
            }

            return $this->response->setJSON(['status' => 'success']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}