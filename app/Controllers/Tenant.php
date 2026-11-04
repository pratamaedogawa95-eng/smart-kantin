<?php

namespace App\Controllers;

use App\Models\TenantModel;
use App\Models\MenuModel;
use App\Models\OrderItemModel;

class Tenant extends BaseController
{
    private $tenantModel;
    private $menuModel;
    private $orderItemModel;

    public function __construct()
    {
        $this->tenantModel = new TenantModel();
        $this->menuModel = new MenuModel();
        $this->orderItemModel = new OrderItemModel();
    }

    // 1. Tampilkan Monitor Dapur & Kelola Menu
    public function index()
    {
        $tenantId = session()->get('tenant_id') ?? 1;

        $daftarPesanan = $this->orderItemModel
            ->select('order_items.*, orders.nomor_meja, orders.kode_transaksi, menus.nama_menu')
            ->join('orders', 'orders.id = order_items.order_id')
            ->join('menus', 'menus.id = order_items.menu_id')
            ->where('order_items.tenant_id', $tenantId)
            ->whereIn('order_items.status_dapur', ['menunggu', 'diproses'])
            ->orderBy('order_items.id', 'ASC')
            ->findAll();

        $daftarMenu = $this->menuModel
            ->where('tenant_id', $tenantId)
            ->findAll();

        $data = [
            'tenant_id'   => $tenantId,
            'all_tenants' => $this->tenantModel->where('status', 'aktif')->findAll(),
            'orders'      => $daftarPesanan,
            'menus'       => $daftarMenu,
            'nama_tenant' => $this->tenantModel->find($tenantId)['nama_tenant'] ?? 'Tenant'
        ];

        return view('tenant/dapur', $data);
    }

    // 2. Tambah Menu Baru
    public function storeMenu()
    {
        $tenantId = session()->get('tenant_id') ?? 1;
        $file = $this->request->getFile('foto');

        $fileName = null;
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fileName = $file->getRandomName();
            $file->move('uploads/menu', $fileName);
        }

        $this->menuModel->insert([
            'tenant_id' => $tenantId,
            'nama_menu' => $this->request->getPost('nama_menu'),
            'kategori'  => $this->request->getPost('kategori'),
            'harga'     => $this->request->getPost('harga'),
            'status'    => $this->request->getPost('status'),
            'foto'      => $fileName
        ]);

        return redirect()->to(base_url('tenant'))->with('pesan', 'Menu berhasil ditambahkan!');
    }

    // 3. Tampilkan Form Edit Menu
    public function editMenu($id)
    {
        $tenantId = session()->get('tenant_id') ?? 1;
        $menu = $this->menuModel->where(['id' => $id, 'tenant_id' => $tenantId])->first();

        if (!$menu) {
            return redirect()->to(base_url('tenant'))->with('pesan', 'Menu tidak ditemukan.');
        }

        $data = [
            'menu' => $menu
        ];

        return view('tenant/edit_menu', $data);
    }

    // 4. Proses Update Menu
    public function updateMenu($id)
    {
        $tenantId = session()->get('tenant_id') ?? 1;
        $menuLama = $this->menuModel->where(['id' => $id, 'tenant_id' => $tenantId])->first();

        if (!$menuLama) {
            return redirect()->to(base_url('tenant'))->with('pesan', 'Menu tidak ditemukan.');
        }

        $file = $this->request->getFile('foto');
        $fileName = $menuLama['foto']; // Default pakai foto lama

        // Cek apakah ada file foto baru yang diunggah
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fileName = $file->getRandomName();
            $file->move('uploads/menu', $fileName);

            // Hapus foto lama jika ada di folder
            if (!empty($menuLama['foto']) && file_exists('uploads/menu/' . $menuLama['foto'])) {
                unlink('uploads/menu/' . $menuLama['foto']);
            }
        }

        // Update data ke database
        $this->menuModel->update($id, [
            'nama_menu' => $this->request->getPost('nama_menu'),
            'kategori'  => $this->request->getPost('kategori'),
            'harga'     => $this->request->getPost('harga'),
            'status'    => $this->request->getPost('status'),
            'foto'      => $fileName
        ]);

        return redirect()->to(base_url('tenant'))->with('pesan', 'Menu berhasil diperbarui!');
    }

    // 5. Toggle Status Ketersediaan (Tersedia <-> Habis)
    public function toggleStatusMenu($id)
    {
        $menu = $this->menuModel->find($id);
        if ($menu) {
            $statusBaru = ($menu['status'] == 'tersedia') ? 'habis' : 'tersedia';
            $this->menuModel->update($id, ['status' => $statusBaru]);
        }

        return redirect()->to(base_url('tenant'));
    }

    // 6. Hapus Menu
    public function deleteMenu($id)
    {
        $menu = $this->menuModel->find($id);
        if ($menu) {
            if ($menu['foto'] && file_exists('uploads/menu/' . $menu['foto'])) {
                unlink('uploads/menu/' . $menu['foto']);
            }
            $this->menuModel->delete($id);
        }

        return redirect()->to(base_url('tenant'))->with('pesan', 'Menu berhasil dihapus!');
    }

    // 7. Update Status Dapur
    public function updateStatus()
    {
        $itemId = $this->request->getPost('item_id');
        $statusBaru = $this->request->getPost('status_dapur');

        $this->orderItemModel->update($itemId, [
            'status_dapur' => $statusBaru
        ]);

        return redirect()->to(base_url('tenant'));
    }

    // 8. Switch Tenant Mode
    public function switchTenant($id)
    {
        session()->set('tenant_id', $id);
        return redirect()->to(base_url('tenant'));
    }

    // 9. Ambil Data Pesanan dalam Format JSON untuk Fetch API (Real-time Auto Sync)
    public function getOrdersJson()
    {
        $tenantId = session()->get('tenant_id') ?? 1;

        $daftarPesanan = $this->orderItemModel
            ->select('order_items.*, orders.nomor_meja, orders.kode_transaksi, menus.nama_menu')
            ->join('orders', 'orders.id = order_items.order_id')
            ->join('menus', 'menus.id = order_items.menu_id')
            ->where('order_items.tenant_id', $tenantId)
            ->whereIn('order_items.status_dapur', ['menunggu', 'diproses'])
            ->orderBy('order_items.id', 'ASC')
            ->findAll();

        return $this->response->setJSON($daftarPesanan);
    }

    // 10. Halaman Login Tenant
    public function login()
    {
        return view('tenant/login');
    }

    // 11. Proses Autentikasi Login Tenant
    public function prosesLogin()
    {
        $username = $this->request->getPost('username');
        $tenant = $this->tenantModel->where('nama_tenant', $username)->first();

        if ($tenant) {
            session()->set([
                'tenant_id'   => $tenant['id'],
                'nama_tenant' => $tenant['nama_tenant'],
                'logged_in'   => true
            ]);
            return redirect()->to(base_url('tenant'))->with('pesan', 'Berhasil masuk ke panel tenant!');
        }

        return redirect()->back()->with('error', 'Tenant tidak ditemukan atau salah!');
    }

    // 12. Logout Tenant
    public function logout()
    {
        session()->remove(['tenant_id', 'nama_tenant', 'logged_in']);
        return redirect()->to(base_url('tenant/login'))->with('pesan', 'Berhasil keluar.');
    }
}