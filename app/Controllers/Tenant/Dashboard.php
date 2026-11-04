<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Models\MenuModel;
use App\Models\OrderItemModel;

class Dashboard extends BaseController
{
    public function index()
    {
        // Validasi Sesi Login Tenant
        if (!session()->get('logged_in_tenant')) {
            return redirect()->to('/tenant/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $tenantId = session()->get('tenant_id');

        $menuModel = new MenuModel();
        $orderItemModel = new OrderItemModel();

        // 1. Ambil Menu HANYA milik tenant ini
        $menus = $menuModel->where('tenant_id', $tenantId)->findAll();

        // 2. Ambil Pesanan Masuk (Aktif: menunggu / diproses) HANYA milik tenant ini
        $orders = $orderItemModel
            ->select('order_items.*, orders.kode_transaksi, orders.nomor_meja, orders.nama_pemesan, menus.nama_menu')
            ->join('orders', 'orders.id = order_items.order_id')
            ->join('menus', 'menus.id = order_items.menu_id')
            ->where('order_items.tenant_id', $tenantId)
            ->whereIn('order_items.status_dapur', ['menunggu', 'diproses'])
            ->orderBy('order_items.id', 'DESC')
            ->findAll();

        $data = [
            'nama_tenant' => session()->get('nama_tenant'),
            'menus'       => $menus,
            'orders'      => $orders
        ];

        return view('tenant/dashboard', $data);
    }

    // Method untuk Polling JSON Dapur secara Real-time
    public function getOrdersJson()
    {
        $tenantId = session()->get('tenant_id');
        $orderItemModel = new OrderItemModel();

        $orders = $orderItemModel
            ->select('order_items.*, orders.kode_transaksi, orders.nomor_meja, orders.nama_pemesan, menus.nama_menu')
            ->join('orders', 'orders.id = order_items.order_id')
            ->join('menus', 'menus.id = order_items.menu_id')
            ->where('order_items.tenant_id', $tenantId)
            ->whereIn('order_items.status_dapur', ['menunggu', 'diproses'])
            ->orderBy('order_items.id', 'DESC')
            ->findAll();

        return $this->response->setJSON($orders);
    }

    // ==========================================
    // MANAJEMEN MENU PER TENANT
    // ==========================================

    public function storeMenu()
    {
        if (!session()->get('logged_in_tenant')) {
            return redirect()->to('/tenant/login');
        }

        $tenantId = session()->get('tenant_id');
        $menuModel = new MenuModel();

        // Handle upload foto jika ada
        $fileFoto = $this->request->getFile('foto');
        $namaFoto = null;

        if ($fileFoto && $fileFoto->isValid() && !$fileFoto->hasMoved()) {
            $namaFoto = $fileFoto->getRandomName();
            $fileFoto->move('uploads/menu', $namaFoto);
        }

        $menuModel->insert([
            'tenant_id' => $tenantId, // Otomatis terikat ke tenant yang login
            'nama_menu' => $this->request->getPost('nama_menu'),
            'kategori'  => $this->request->getPost('kategori'),
            'harga'     => $this->request->getPost('harga'),
            'status'    => $this->request->getPost('status'),
            'foto'      => $namaFoto
        ]);

        return redirect()->to(base_url('tenant/dashboard'))->with('pesan', 'Menu baru berhasil ditambahkan!');
    }

    public function toggleStatus($id)
    {
        $menuModel = new MenuModel();
        $menu = $menuModel->find($id);

        if ($menu && $menu['tenant_id'] == session()->get('tenant_id')) {
            $statusBaru = ($menu['status'] == 'tersedia') ? 'habis' : 'tersedia';
            $menuModel->update($id, ['status' => $statusBaru]);
        }

        return redirect()->to(base_url('tenant/dashboard'))->with('pesan', 'Status menu berhasil diubah!');
    }

    public function deleteMenu($id)
    {
        $menuModel = new MenuModel();
        $menu = $menuModel->find($id);

        if ($menu && $menu['tenant_id'] == session()->get('tenant_id')) {
            // Hapus file foto fisik jika ada
            if (!empty($menu['foto']) && file_exists('uploads/menu/' . $menu['foto'])) {
                unlink('uploads/menu/' . $menu['foto']);
            }
            $menuModel->delete($id);
        }

        return redirect()->to(base_url('tenant/dashboard'))->with('pesan', 'Menu berhasil dihapus!');
    }

    public function updateStatus()
    {
        $itemId = $this->request->getPost('item_id');
        $statusBaru = $this->request->getPost('status_dapur');

        $orderItemModel = new OrderItemModel();
        $item = $orderItemModel->find($itemId);

        // Validasi agar tenant hanya bisa mengubah pesanan miliknya sendiri
        if ($item && $item['tenant_id'] == session()->get('tenant_id')) {
            $orderItemModel->update($itemId, ['status_dapur' => $statusBaru]);
        }

        return redirect()->to(base_url('tenant/dashboard'))->with('pesan', 'Status dapur berhasil diperbarui!');
    }

    // Tampilkan Form Edit Menu
    public function editMenu($id)
    {
        if (!session()->get('logged_in_tenant')) {
            return redirect()->to('/tenant/login');
        }

        $tenantId = session()->get('tenant_id');
        $menuModel = new MenuModel();
        
        $menu = $menuModel->where(['id' => $id, 'tenant_id' => $tenantId])->first();

        if (!$menu) {
            return redirect()->to(base_url('tenant/dashboard'))->with('error', 'Menu tidak ditemukan atau bukan milik stand Anda.');
        }

        $data = [
            'menu' => $menu
        ];

        return view('tenant/edit_menu', $data);
    }

    // Proses Simpan Perubahan Menu
    public function updateMenu($id)
    {
        if (!session()->get('logged_in_tenant')) {
            return redirect()->to('/tenant/login');
        }

        $tenantId = session()->get('tenant_id');
        $menuModel = new MenuModel();
        
        $menuLama = $menuModel->where(['id' => $id, 'tenant_id' => $tenantId])->first();

        if (!$menuLama) {
            return redirect()->to(base_url('tenant/dashboard'))->with('error', 'Menu tidak ditemukan.');
        }

        $fileFoto = $this->request->getFile('foto');
        $namaFoto = $menuLama['foto']; // Default gunakan foto lama

        if ($fileFoto && $fileFoto->isValid() && !$fileFoto->hasMoved()) {
            $namaFoto = $fileFoto->getRandomName();
            $fileFoto->move('uploads/menu', $namaFoto);

            // Hapus foto lama jika diganti baru
            if (!empty($menuLama['foto']) && file_exists('uploads/menu/' . $menuLama['foto'])) {
                unlink('uploads/menu/' . $menuLama['foto']);
            }
        }

        $menuModel->update($id, [
            'nama_menu' => $this->request->getPost('nama_menu'),
            'kategori'  => $this->request->getPost('kategori'),
            'harga'     => $this->request->getPost('harga'),
            'status'    => $this->request->getPost('status'),
            'foto'      => $namaFoto
        ]);

        return redirect()->to(base_url('tenant/dashboard'))->with('pesan', 'Menu berhasil diperbarui!');
    }
}