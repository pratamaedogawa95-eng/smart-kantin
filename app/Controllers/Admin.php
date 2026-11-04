<?php

namespace App\Controllers;

use App\Models\TenantModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\TableModel;

class Admin extends BaseController
{
    private $tenantModel;
    private $orderModel;
    private $orderItemModel;
    private $tableModel;

    public function __construct()
    {
        $this->tenantModel = new TenantModel();
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->tableModel = new TableModel();
    }

    public function index()
    {
        $totalOmzet = $this->orderModel
            ->selectSum('total_bayar')
            ->where('status_bayar', 'lunas')
            ->first()['total_bayar'] ?? 0;

        $totalTransaksi = $this->orderModel
            ->where('status_bayar', 'lunas')
            ->countAllResults();

        $laporanTenant = $this->tenantModel
            ->select('tenants.id, tenants.nama_tenant, tenants.nomor_stand, SUM(order_items.qty * order_items.harga_satuan) as omzet_tenant, COUNT(DISTINCT order_items.order_id) as total_pesanan')
            ->join('order_items', 'order_items.tenant_id = tenants.id', 'left')
            ->join('orders', "orders.id = order_items.order_id AND orders.status_bayar = 'lunas'", 'left')
            ->groupBy(['tenants.id', 'tenants.nama_tenant', 'tenants.nomor_stand'])
            ->findAll();

        $data = [
            'total_omzet'     => $totalOmzet,
            'total_transaksi' => $totalTransaksi,
            'laporan_tenant'  => $laporanTenant,
            'tenants'         => $this->tenantModel->findAll(),
            'meja'            => $this->tableModel->findAll()
        ];

        return view('admin/dashboard', $data);
    }

    // Simpan Tenant Baru
    public function simpanTenant()
    {
        $this->tenantModel->insert([
            'nama_tenant'  => $this->request->getPost('nama_tenant'),
            'nama_pemilik' => $this->request->getPost('nama_pemilik'),
            'nomor_stand'  => $this->request->getPost('nomor_stand'),
            'status'       => 'aktif'
        ]);

        return redirect()->to('/admin')->with('pesan', 'Tenant baru berhasil ditambahkan!');
    }

    // Simpan Meja Baru
    public function simpanMeja()
    {
        $this->tableModel->insert([
            'nomor_meja' => $this->request->getPost('nomor_meja'),
            'kapasitas'  => $this->request->getPost('kapasitas'),
            'status'     => 'tersedia'
        ]);

        return redirect()->to('/admin')->with('pesan', 'Meja baru berhasil ditambahkan!');
    }

    // Hapus Meja
    public function hapusMeja($id)
    {
        $this->tableModel->delete($id);
        return redirect()->to('/admin')->with('pesan', 'Meja berhasil dihapus!');
    }

    // Method baru untuk Fetch API Statistik Omzet & Laporan Tenant Real-time
    public function getAdminStatsJson()
    {
        $totalOmzet = $this->orderModel
            ->selectSum('total_bayar')
            ->where('status_bayar', 'lunas')
            ->first()['total_bayar'] ?? 0;

        $totalTransaksi = $this->orderModel
            ->where('status_bayar', 'lunas')
            ->countAllResults();

        $laporanTenant = $this->tenantModel
            ->select('tenants.id, tenants.nama_tenant, tenants.nomor_stand, SUM(order_items.qty * order_items.harga_satuan) as omzet_tenant, COUNT(DISTINCT order_items.order_id) as total_pesanan')
            ->join('order_items', 'order_items.tenant_id = tenants.id', 'left')
            ->join('orders', "orders.id = order_items.order_id AND orders.status_bayar = 'lunas'", 'left')
            ->groupBy(['tenants.id', 'tenants.nama_tenant', 'tenants.nomor_stand'])
            ->findAll();

        return $this->response->setJSON([
            'total_omzet'     => (float) $totalOmzet,
            'total_transaksi' => (int) $totalTransaksi,
            'laporan_tenant'  => $laporanTenant
        ]);
    }
}