<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Menu - Smart Kantin Meja <?= esc($nomor_meja) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js untuk keranjang reaktif & filter -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 pb-28 font-sans" x-data="katalogApp()">

    <!-- Header & Info Meja -->
    <div class="bg-slate-900 text-white p-4 sticky top-0 z-20 shadow-md flex justify-between items-center">
        <div>
            <h1 class="text-sm font-bold tracking-wide">Smart Kantin ITATS</h1>
            <p class="text-[11px] text-slate-300">
                Meja: <span class="font-bold text-white underline"><?= esc($nomor_meja) ?></span> | 
                Pemesan: <span class="font-bold text-white"><?= esc($nama_pemesan ?? 'Tamu') ?></span>
            </p>
        </div>
        <a href="/customer/logout-identitas" onclick="return confirm('Ganti identitas pemesan?')" class="bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-semibold px-2.5 py-1.5 rounded-lg border border-slate-700 transition">
            Ganti Identitas
        </a>
    </div>

    <!-- Search Bar & Kategori Filter Horizontal -->
    <div class="bg-white border-b sticky top-[57px] z-10 px-4 py-3 space-y-2.5 shadow-sm">
        <!-- Input Search -->
        <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">🔍</span>
            <input type="text" x-model="searchQuery" placeholder="Cari makanan atau minuman favoritmu..." class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
        </div>

        <!-- Pilihan Tab Kategori -->
        <div class="flex space-x-2 overflow-x-auto whitespace-nowrap scrollbar-none pb-1">
            <button @click="activeTab = 'Semua'" :class="activeTab === 'Semua' ? 'bg-sky-600 text-white font-bold' : 'bg-gray-100 text-gray-700 font-semibold'" class="text-xs px-4 py-1.5 rounded-full transition shadow-sm">Semua Menu</button>
            <button @click="activeTab = 'Makanan'" :class="activeTab === 'Makanan' ? 'bg-sky-600 text-white font-bold' : 'bg-gray-100 text-gray-700 font-semibold'" class="text-xs px-4 py-1.5 rounded-full transition shadow-sm">🍱 Makanan</button>
            <button @click="activeTab = 'Minuman'" :class="activeTab === 'Minuman' ? 'bg-sky-600 text-white font-bold' : 'bg-gray-100 text-gray-700 font-semibold'" class="text-xs px-4 py-1.5 rounded-full transition shadow-sm">🥤 Minuman</button>
            <button @click="activeTab = 'Cemilan'" :class="activeTab === 'Cemilan' ? 'bg-sky-600 text-white font-bold' : 'bg-gray-100 text-gray-700 font-semibold'" class="text-xs px-4 py-1.5 rounded-full transition shadow-sm">🍟 Cemilan</button>
        </div>
    </div>

    <!-- Form Checkout Utama -->
    <form action="/customer/checkout" method="post" class="p-4 max-w-lg mx-auto space-y-6">
        <?= csrf_field() ?>

        <!-- Looping Berdasarkan Kategori yang Tersedia dari Server -->
        <template x-for="kategoriName in getActiveCategories()" :key="kategoriName">
            <div class="space-y-3" x-show="hasVisibleMenus(kategoriName)">
                <!-- Judul Kategori -->
                <div class="flex items-center space-x-2 border-l-4 border-sky-600 pl-2.5">
                    <h2 class="font-black text-slate-800 text-sm uppercase tracking-wider" x-text="kategoriName"></h2>
                </div>

                <!-- Grid Card Menu per Kategori -->
                <div class="grid grid-cols-2 gap-3.5">
                    <template x-for="menu in filterMenusByCategory(kategoriName)" :key="menu.id">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col justify-between transition hover:shadow-md"
                             x-show="matchesSearch(menu.nama_menu)">
                            <div>
                                <!-- Gambar Menu -->
                                <div class="relative h-32 bg-gray-100">
                                    <template x-if="menu.foto">
                                        <img :src="'/uploads/menu/' + menu.foto" :alt="menu.nama_menu" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!menu.foto">
                                        <div class="w-full h-full flex items-center justify-center text-gray-300 text-xs font-medium bg-gray-50">
                                            🍽️ No Image
                                        </div>
                                    </template>
                                    <!-- Badge Tenant / Stand -->
                                    <span class="absolute top-2 left-2 bg-black/60 backdrop-blur-md text-white text-[9px] font-bold px-2 py-0.5 rounded-md" x-text="menu.nama_tenant"></span>
                                </div>

                                <!-- Detail Nama & Harga -->
                                <div class="p-3 space-y-1">
                                    <h3 class="font-bold text-gray-800 text-xs line-clamp-2 leading-snug" x-text="menu.nama_menu"></h3>
                                    <p class="text-xs font-extrabold text-sky-600" x-text="'Rp ' + Number(menu.harga).toLocaleString('id-ID')"></p>
                                </div>
                            </div>

                            <!-- Tombol Aksi / Qty Control di Card -->
                            <div class="p-3 pt-0">
                                <template x-if="getQty(menu.id) === 0">
                                    <button type="button" 
                                            @click="tambah(menu.id, menu.harga)" 
                                            class="w-full bg-sky-50 hover:bg-sky-100 text-sky-600 font-bold py-1.5 rounded-xl text-xs border border-sky-200 transition shadow-sm">
                                        + Add
                                    </button>
                                </template>

                                <template x-if="getQty(menu.id) > 0">
                                    <div class="flex items-center justify-between bg-sky-50 border border-sky-200 rounded-xl p-1">
                                        <button type="button" 
                                                @click="kurang(menu.id)" 
                                                class="w-7 h-7 bg-white rounded-lg font-bold text-sky-600 hover:bg-sky-100 shadow-sm flex items-center justify-center text-xs">
                                            -
                                        </button>
                                        <span class="font-extrabold text-xs text-sky-700" x-text="getQty(menu.id)"></span>
                                        <button type="button" 
                                                @click="tambah(menu.id, menu.harga)" 
                                                class="w-7 h-7 bg-sky-600 text-white rounded-lg font-bold hover:bg-sky-700 shadow-sm flex items-center justify-center text-xs">
                                            +
                                        </button>
                                    </div>
                                </template>

                                <!-- Hidden Input untuk dikirim ke Controller -->
                                <input type="hidden" name="menu_id[]" :value="menu.id">
                                <input type="hidden" name="qty[]" :value="getQty(menu.id)">
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <!-- Floating Bottom Bar: Total & Tombol Checkout -->
        <div class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md p-4 border-t shadow-2xl z-30" x-show="totalItem > 0" x-transition>
            <div class="max-w-lg mx-auto flex justify-between items-center bg-sky-600 text-white px-4 py-3 rounded-2xl shadow-lg">
                <div>
                    <p class="text-[10px] uppercase font-semibold text-sky-100 tracking-wider">Total (<span x-text="totalItem"></span> Item)</p>
                    <p class="text-base font-extrabold" x-text="'Rp ' + totalBayar.toLocaleString('id-ID')">Rp 0</p>
                </div>

                <button type="button" 
                        @click="bukaModalBayar()" 
                        class="bg-white text-sky-600 hover:bg-sky-50 font-extrabold px-6 py-2.5 rounded-xl text-xs transition shadow">
                    CHECK OUT (<span x-text="totalItem"></span>) ➔
                </button>
            </div>
        </div>

        <!-- MODAL PILIHAN METODE PEMBAYARAN -->
        <div x-show="modalOpen" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm" style="display: none;">
            <div class="bg-white max-w-sm w-full p-6 rounded-3xl shadow-2xl space-y-4" @click.away="modalOpen = false">
                <div class="border-b pb-3">
                    <h3 class="font-bold text-gray-900 text-base">Pilih Metode Pembayaran</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Total Tagihan: <b class="text-sky-600" x-text="'Rp ' + totalBayar.toLocaleString('id-ID')"></b></p>
                </div>

                <div class="space-y-2.5">
                    <label class="flex items-center space-x-3 border-2 border-gray-100 p-3.5 rounded-2xl cursor-pointer hover:border-sky-500 hover:bg-sky-50/30 transition">
                        <input type="radio" name="metode_pembayaran" value="qris" checked class="w-4 h-4 text-sky-600 focus:ring-sky-500">
                        <div>
                            <p class="font-bold text-xs text-gray-800">QRIS (Scan & Bayar)</p>
                            <p class="text-[10px] text-gray-400">GoPay, OVO, Dana, BCA, Mandiri, dll.</p>
                        </div>
                    </label>

                    <label class="flex items-center space-x-3 border-2 border-gray-100 p-3.5 rounded-2xl cursor-pointer hover:border-sky-500 hover:bg-sky-50/30 transition">
                        <input type="radio" name="metode_pembayaran" value="tunai" class="w-4 h-4 text-sky-600 focus:ring-sky-500">
                        <div>
                            <p class="font-bold text-xs text-gray-800">Bayar Tunai / Kasir Stan</p>
                            <p class="text-[10px] text-gray-400">Bayar langsung di konter penjual.</p>
                        </div>
                    </label>
                </div>

                <div class="flex space-x-2 pt-3">
                    <button type="button" @click="modalOpen = false" class="w-1/2 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-3 rounded-xl text-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="w-1/2 bg-sky-600 hover:bg-sky-700 text-white font-bold py-3 rounded-xl text-xs shadow-lg shadow-sky-600/30 transition">
                        Konfirmasi Pesanan
                    </button>
                </div>
            </div>
        </div>

    </form>

    <!-- Logic Alpine.js dengan Auto-Sync Real-time (3 Detik) -->
    <script>
        function katalogApp() {
            return {
                menus: <?= json_encode($daftar_menu ?? []) ?>,
                cart: {},
                totalBayar: 0,
                totalItem: 0,
                modalOpen: false,
                searchQuery: '',
                activeTab: 'Semua',

                // Jalankan polling otomatis setiap 3 detik untuk sinkronisasi status stok menu
                init() {
                    setInterval(() => {
                        this.fetchMenuRealtime();
                    }, 3000);
                },

                fetchMenuRealtime() {
                    fetch('/customer/menu-json')
                        .then(response => response.json())
                        .then(data => {
                            this.menus = data;

                            // Jika ada menu di keranjang yang tiba-tiba habis diubah tenant, otomatis hapus dari cart
                            for (let id in this.cart) {
                                let masihAda = this.menus.some(m => m.id == id);
                                if (!masihAda) {
                                    delete this.cart[id];
                                }
                            }
                            this.hitungTotal();
                        })
                        .catch(error => {
                            // Abaikan error jaringan kecil agar konsol bersih
                        });
                },

                tambah(id, harga) {
                    if (!this.cart[id]) {
                        this.cart[id] = { qty: 0, harga: Number(harga) };
                    }
                    this.cart[id].qty++;
                    this.hitungTotal();
                },

                kurang(id) {
                    if (this.cart[id] && this.cart[id].qty > 0) {
                        this.cart[id].qty--;
                        if (this.cart[id].qty === 0) {
                            delete this.cart[id];
                        }
                    }
                    this.hitungTotal();
                },

                getQty(id) {
                    return this.cart[id] ? this.cart[id].qty : 0;
                },

                hitungTotal() {
                    let total = 0;
                    let count = 0;
                    for (let id in this.cart) {
                        total += this.cart[id].qty * this.cart[id].harga;
                        count += this.cart[id].qty;
                    }
                    this.totalBayar = total;
                    this.totalItem = count;
                },

                bukaModalBayar() {
                    if (this.totalBayar > 0) {
                        this.modalOpen = true;
                    }
                },

                getActiveCategories() {
                    let cats = [...new Set(this.menus.map(m => m.kategori || 'Makanan'))];
                    return cats;
                },

                filterMenusByCategory(kategori) {
                    return this.menus.filter(m => (m.kategori || 'Makanan') === kategori);
                },

                hasVisibleMenus(kategori) {
                    let list = this.filterMenusByCategory(kategori);
                    let matchTab = (this.activeTab === 'Semua' || this.activeTab === kategori);
                    let hasSearch = list.some(m => m.nama_menu.toLowerCase().includes(this.searchQuery.toLowerCase()));
                    return matchTab && hasSearch;
                },

                matchesSearch(namaMenu) {
                    return namaMenu.toLowerCase().includes(this.searchQuery.toLowerCase());
                }
            }
        }
    </script>
</body>
</html>