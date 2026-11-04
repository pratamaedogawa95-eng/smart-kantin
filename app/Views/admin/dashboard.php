<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Smart Kantin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Tambahkan Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 p-4 md:p-6 font-sans" 
      x-data="adminDashboard()" 
      x-init="init()">

    <div class="max-w-6xl mx-auto space-y-6">
        
        <div class="flex justify-between items-center border-b pb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Panel Super Admin</h1>
                <p class="text-xs text-gray-500">Rekapitulasi Keuangan, Tenant & Kelola QR Meja</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-[10px] bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-semibold animate-pulse">
                    ● Live Auto-Sync
                </span>
                <span class="bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full">Sistem Aktif</span>
            </div>
        </div>

        <?php if (session()->getFlashdata('pesan')) : ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded text-xs font-bold">
                <?= session()->getFlashdata('pesan') ?>
            </div>
        <?php endif; ?>

        <!-- Summary Cards (Omzet & Total Transaksi Reaktif) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
                <p class="text-xs font-bold text-gray-400 uppercase">Total Uang Masuk (Kantin)</p>
                <p class="text-2xl font-extrabold text-blue-600 mt-1" 
                   x-text="'Rp ' + Number(totalOmzet).toLocaleString('id-ID')">
                </p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
                <p class="text-xs font-bold text-gray-400 uppercase">Total Transaksi Selesai</p>
                <p class="text-2xl font-extrabold text-gray-800 mt-1" 
                   x-text="totalTransaksi + ' Transaksi'">
                </p>
            </div>
        </div>

        <!-- Tabel Split Revenue Per Tenant (Reaktif) -->
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-3">
            <h2 class="text-base font-bold text-gray-800">Laporan Hak Pencairan Dana Per Stand (Split Revenue)</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b text-gray-600">
                            <th class="p-2.5">Stand / Tenant</th>
                            <th class="p-2.5">Nomor Stand</th>
                            <th class="p-2.5">Jumlah Pesanan</th>
                            <th class="p-2.5">Hak Pendapatan (Omzet)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <template x-for="row in laporanTenant" :key="row.id">
                            <tr class="hover:bg-gray-50">
                                <td class="p-2.5 font-bold text-gray-800" x-text="row.nama_tenant"></td>
                                <td class="p-2.5 text-gray-500" x-text="row.nomor_stand"></td>
                                <td class="p-2.5" x-text="(row.total_pesanan || 0) + ' kali'"></td>
                                <td class="p-2.5 font-bold text-green-600" x-text="'Rp ' + Number(row.omzet_tenant || 0).toLocaleString('id-ID')"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section Kelola Meja & Generator QR Code -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Form Tambah Meja (1 Kolom) -->
            <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-3">
                <h2 class="text-base font-bold text-gray-800">Tambah Meja Kantin</h2>
                <form action="/admin/simpanMeja" method="post" class="space-y-3 text-xs">
                    <?= csrf_field() ?>
                    <div>
                        <label class="font-semibold text-gray-600">Nomor Meja</label>
                        <input type="text" name="nomor_meja" placeholder="Misal: 04" required class="w-full border rounded p-2 mt-1">
                    </div>
                    <div>
                        <label class="font-semibold text-gray-600">Kapasitas Kursi</label>
                        <input type="number" name="kapasitas" value="4" required class="w-full border rounded p-2 mt-1">
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded hover:bg-blue-700">
                        + Tambah Meja
                    </button>
                </form>
            </div>

            <!-- Daftar Meja & Generator QR Code (2 Kolom) -->
            <div class="md:col-span-2 bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-3">
                <h2 class="text-base font-bold text-gray-800">Daftar Meja & QR Code Siap Cetak</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <?php foreach ($meja as $m) : ?>
                        <?php $urlOrder = base_url('customer?meja=' . $m['nomor_meja']); ?>
                        <div class="border rounded-lg p-3 text-center bg-gray-50 flex flex-col items-center justify-between space-y-2">
                            <div>
                                <span class="font-extrabold text-sm text-gray-800">Meja <?= $m['nomor_meja'] ?></span>
                                <p class="text-[10px] text-gray-500">(Kapasitas: <?= $m['kapasitas'] ?> Orang)</p>
                            </div>

                            <!-- Auto Generate QR Code via API gratis QuickChart -->
                            <img src="https://quickchart.io/qr?text=<?= urlencode($urlOrder) ?>&size=120" 
                                 alt="QR Meja <?= $m['nomor_meja'] ?>" 
                                 class="w-24 h-24 border p-1 bg-white rounded">

                            <div class="flex items-center space-x-1 w-full pt-1">
                                <a href="<?= $urlOrder ?>" target="_blank" class="text-[10px] bg-green-600 text-white font-bold py-1 px-2 rounded w-full text-center">
                                    Tes Scan
                                </a>
                                <a href="/admin/hapusMeja/<?= $m['id'] ?>" onclick="return confirm('Hapus meja ini?')" class="text-[10px] bg-red-100 text-red-600 font-bold py-1 px-2 rounded">
                                    ✕
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- Form Tambah Stand/Tenant -->
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-3">
            <h2 class="text-base font-bold text-gray-800">Tambah Stand / Tenant Baru</h2>
            <form action="/admin/simpanTenant" method="post" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <?= csrf_field() ?>
                <input type="text" name="nama_tenant" placeholder="Nama Stand/Kios" required class="border text-xs rounded p-2">
                <input type="text" name="nama_pemilik" placeholder="Nama Pemilik" required class="border text-xs rounded p-2">
                <input type="text" name="nomor_stand" placeholder="No. Stand (misal: S-04)" required class="border text-xs rounded p-2">
                <button type="submit" class="bg-blue-600 text-white text-xs font-bold py-2 rounded hover:bg-blue-700">
                    Simpan Tenant
                </button>
            </form>
        </div>

    </div>

<!-- Script Alpine.js Polling Fetch API Dashboard Admin -->
<script>
    function adminDashboard() {
        return {
            totalOmzet: <?= json_encode($total_omzet) ?>,
            totalTransaksi: <?= json_encode($total_transaksi) ?>,
            laporanTenant: <?= json_encode($laporan_tenant) ?>,
            init() {
                // Polling data statistik admin setiap 3 detik di background tanpa kedip
                setInterval(() => {
                    this.fetchStats();
                }, 3000);
            },
            fetchStats() {
                fetch('/admin/stats-json')
                    .then(response => response.json())
                    .then(data => {
                        this.totalOmzet = data.total_omzet;
                        this.totalTransaksi = data.total_transaksi;
                        this.laporanTenant = data.laporan_tenant;
                    })
                    .catch(error => console.error('Gagal memperbarui dashboard admin:', error));
            }
        }
    }
</script>
</body>
</html>