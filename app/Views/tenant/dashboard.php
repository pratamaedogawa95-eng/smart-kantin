<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Tenant & Layar Dapur - <?= esc($nama_tenant) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js untuk reaktifitas tab kategori & live search -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-100 p-4 font-sans" x-data="tenantApp()">

    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header Panel Tenant -->
        <div class="bg-sky-600 text-white p-4 rounded-2xl shadow-md flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <span class="bg-white text-sky-600 font-extrabold px-3 py-1 rounded-xl text-xs uppercase tracking-wider shadow-sm">Tenant Panel</span>
                <h1 class="font-black text-base md:text-lg"><?= esc($nama_tenant) ?></h1>
            </div>
            <a href="/tenant/logout" onclick="return confirm('Yakin ingin keluar dari dashboard stand?')" class="bg-rose-500 hover:bg-rose-600 text-white text-xs font-bold px-3.5 py-2 rounded-xl transition shadow">
                Logout ➔
            </a>
        </div>

        <?php if (session()->getFlashdata('pesan')) : ?>
            <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-2xl text-xs font-bold shadow-sm">
                <?= session()->getFlashdata('pesan') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="bg-rose-100 border border-rose-400 text-rose-700 px-4 py-3 rounded-2xl text-xs font-bold shadow-sm">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <!-- Layout Grid 2 Kolom: Monitor Dapur (Kiri) & Kelola Menu (Kanan) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Kiri: Monitor Dapur Pesanan Masuk (2 Kolom) -->
            <div class="lg:col-span-2 space-y-4" x-data="dapurManager()" x-init="init()">
                <div class="flex justify-between items-center">
                    <h2 class="text-base font-black text-slate-800">Monitor Dapur Masak</h2>
                    <span class="text-[10px] bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full font-bold animate-pulse shadow-sm">
                        ● Live Auto-Sync (Real-time)
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="order in orders" :key="order.id">
                        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-center border-b pb-2 mb-2">
                                    <span class="text-base font-black text-sky-600" x-text="'Meja ' + order.nomor_meja"></span>
                                    <span class="text-[11px] text-slate-400 font-semibold" x-text="order.kode_transaksi"></span>
                                </div>
                                <p class="font-bold text-slate-800 text-sm" x-text="order.nama_menu"></p>
                                <p class="text-xs text-slate-500 mt-0.5">Jumlah: <span class="font-extrabold text-slate-800" x-text="(order.qty ?? order.jumlah) + ' porsi'"></span></p>
                            </div>

                            <div class="mt-4 pt-3 border-t flex justify-between items-center">
                                <span class="text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-lg"
                                    :class="order.status_dapur == 'menunggu' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800'"
                                    x-text="order.status_dapur">
                                </span>

                                <form action="/tenant/updateStatus" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="item_id" x-bind:value="order.id">
                                    
                                    <template x-if="order.status_dapur == 'menunggu'">
                                        <div>
                                            <input type="hidden" name="status_dapur" value="diproses">
                                            <button type="submit" class="bg-sky-600 text-white text-xs font-bold px-3 py-1.5 rounded-xl hover:bg-sky-700 shadow-sm transition">
                                                Mulai Masak
                                            </button>
                                        </div>
                                    </template>

                                    <template x-if="order.status_dapur != 'menunggu'">
                                        <div>
                                            <input type="hidden" name="status_dapur" value="siap">
                                            <button type="submit" class="bg-emerald-600 text-white text-xs font-bold px-3 py-1.5 rounded-xl hover:bg-emerald-700 shadow-sm transition">
                                                Sajikan / Siap
                                            </button>
                                        </div>
                                    </template>
                                </form>
                            </div>
                        </div>
                    </template>

                    <template x-if="orders.length === 0">
                        <div class="col-span-2 bg-white p-8 text-center text-slate-400 rounded-2xl border border-slate-200 text-xs font-medium shadow-sm">
                            🍽️ Tidak ada pesanan aktif saat ini.
                        </div>
                    </template>
                </div>
            </div>

            <!-- Kanan: Kelola Katalog Menu Stand (1 Kolom) -->
            <div class="space-y-4">
                <h2 class="text-base font-black text-slate-800">Katalog Menu Stand</h2>

                <!-- Form Tambah Menu -->
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 space-y-3">
                    <h3 class="text-xs font-black text-slate-700 border-b pb-2">➕ Tambah Menu Baru</h3>
                    
                    <form action="/tenant/storeMenu" method="post" enctype="multipart/form-data" class="space-y-3 text-xs">
                        <?= csrf_field() ?>
                        <div>
                            <label class="font-bold text-slate-600">Nama Menu</label>
                            <input type="text" name="nama_menu" required placeholder="Cth: Ayam Geprek Spesial" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 mt-1 font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="font-bold text-slate-600">Kategori</label>
                                <select name="kategori" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 mt-1 font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                                    <option value="Makanan">Makanan</option>
                                    <option value="Minuman">Minuman</option>
                                    <option value="Cemilan">Cemilan</option>
                                </select>
                            </div>
                            <div>
                                <label class="font-bold text-slate-600">Harga (Rp)</label>
                                <input type="number" name="harga" required placeholder="12000" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 mt-1 font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="font-bold text-slate-600">Status Awal</label>
                                <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 mt-1 font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                                    <option value="tersedia">Tersedia</option>
                                    <option value="habis">Habis</option>
                                </select>
                            </div>
                            <div>
                                <label class="font-bold text-slate-600">Foto (Opsional)</label>
                                <input type="file" name="foto" accept="image/*" class="w-full mt-1 text-[10px] text-slate-500">
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-sky-600 text-white font-bold py-2.5 rounded-xl hover:bg-sky-700 shadow-md shadow-sky-600/20 transition">
                            Simpan Menu Baru
                        </button>
                    </form>
                </div>

                <!-- Atur Stok, Status, Kolom Kategori Terpisah & Live Search -->
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 space-y-3">
                    <div class="flex justify-between items-center border-b pb-2">
                        <h3 class="text-xs font-black text-slate-700">📋 Atur Stok & Status Menu</h3>
                    </div>

                    <!-- Input Search & Filter Kategori / Tag -->
                    <div class="space-y-2">
                        <input type="text" x-model="searchQuery" placeholder="🔍 Cari nama menu..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                        
                        <!-- Pilihan Tab Tag / Kategori -->
                        <div class="flex space-x-1 overflow-x-auto pb-1 text-[11px]">
                            <button @click="activeTab = 'Semua'" :class="activeTab === 'Semua' ? 'bg-sky-600 text-white font-bold' : 'bg-slate-100 text-slate-600 font-semibold'" class="px-3 py-1 rounded-lg transition whitespace-nowrap">Semua</button>
                            <button @click="activeTab = 'Makanan'" :class="activeTab === 'Makanan' ? 'bg-sky-600 text-white font-bold' : 'bg-slate-100 text-slate-600 font-semibold'" class="px-3 py-1 rounded-lg transition whitespace-nowrap">🍱 Makanan</button>
                            <button @click="activeTab = 'Minuman'" :class="activeTab === 'Minuman' ? 'bg-sky-600 text-white font-bold' : 'bg-slate-100 text-slate-600 font-semibold'" class="px-3 py-1 rounded-lg transition whitespace-nowrap">🥤 Minuman</button>
                            <button @click="activeTab = 'Cemilan'" :class="activeTab === 'Cemilan' ? 'bg-sky-600 text-white font-bold' : 'bg-slate-100 text-slate-600 font-semibold'" class="px-3 py-1 rounded-lg transition whitespace-nowrap">🍟 Cemilan</button>
                        </div>
                    </div>

                    <!-- Daftar Menu Dinamis berdasarkan Pencarian & Tab -->
                    <div class="space-y-2.5 max-h-80 overflow-y-auto pr-1">
                        <?php if (empty($menus)) : ?>
                            <p class="text-xs text-slate-400 text-center py-4">Belum ada menu terdaftar.</p>
                        <?php else : ?>
                            <?php foreach ($menus as $m) : ?>
                                <div class="menu-item flex justify-between items-center border-b border-slate-100 pb-2.5 pt-1 text-xs"
                                     data-nama="<?= strtolower(esc($m['nama_menu'])) ?>"
                                     data-kategori="<?= esc($m['kategori'] ?? 'Makanan') ?>"
                                     x-show="matchesSearch('<?= strtolower(esc($m['nama_menu'])) ?>', '<?= esc($m['kategori'] ?? 'Makanan') ?>')">
                                    
                                    <div>
                                        <p class="font-black text-slate-800"><?= esc($m['nama_menu']) ?></p>
                                        <div class="flex items-center space-x-2 mt-0.5">
                                            <span class="text-[10px] font-bold text-sky-600 bg-sky-50 px-2 py-0.5 rounded-md border border-sky-100"><?= esc($m['kategori'] ?? 'Makanan') ?></span>
                                            <span class="text-slate-500 font-semibold">Rp <?= number_format($m['harga'], 0, ',', '.') ?></span>
                                        </div>
                                    </div>

                                    <!-- Tombol Aksi: Edit (Logo Pensil di sebelah kiri status), Status, & Hapus -->
                                    <div class="flex items-center space-x-1.5">
                                        <!-- Tombol Edit (Logo Pensil) -->
                                        <a href="/tenant/menu/edit/<?= $m['id'] ?>" class="text-sky-600 hover:text-sky-800 font-extrabold px-2 py-1 bg-sky-50 hover:bg-sky-100 rounded-lg text-[10px] transition flex items-center shadow-sm" title="Edit Menu">
                                            ✏️ Edit
                                        </a>

                                        <!-- Tombol Toggle Status (Tersedia / Habis) -->
                                        <a href="/tenant/toggleStatusMenu/<?= $m['id'] ?>" 
                                           class="px-2.5 py-1 rounded-lg font-bold uppercase text-[10px] transition shadow-sm 
                                           <?= $m['status'] == 'tersedia' ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-rose-100 text-rose-700 hover:bg-rose-200' ?>">
                                            <?= esc($m['status']) ?>
                                        </a>

                                        <!-- Tombol Hapus -->
                                        <a href="/tenant/deleteMenu/<?= $m['id'] ?>" 
                                           onclick="return confirm('Yakin ingin menghapus menu ini?')" 
                                           class="text-rose-400 hover:text-rose-600 font-extrabold px-1.5 py-1 text-xs transition" title="Hapus">
                                            ✕
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </div>

    </div>

<!-- Script Alpine.js untuk Live Search, Filter Kategori & Monitor Dapur Real-time -->
<script>
    function tenantApp() {
        return {
            searchQuery: '',
            activeTab: 'Semua',
            matchesSearch(nama, kategori) {
                let matchQuery = nama.includes(this.searchQuery.toLowerCase());
                let matchTab = (this.activeTab === 'Semua' || kategori.toLowerCase() === this.activeTab.toLowerCase());
                return matchQuery && matchTab;
            }
        }
    }

    function dapurManager() {
        return {
            orders: <?= json_encode($orders) ?>,
            init() {
                setInterval(() => {
                    this.fetchOrders();
                }, 3000);
            },
            fetchOrders() {
                fetch('<?= base_url('tenant/get-orders-json') ?>')
                    .then(response => response.json())
                    .then(data => {
                        this.orders = data;
                    })
                    .catch(error => console.error('Gagal menyinkronkan data dapur:', error));
            }
        }
    }
</script>
</body>
</html>