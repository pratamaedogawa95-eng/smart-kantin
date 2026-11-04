<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pesanan - Smart Kantin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js untuk polling status real-time -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-900 min-h-screen py-8 px-4 font-sans flex items-center justify-center" x-data="statusApp()">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-100 p-6 space-y-6">
        
        <!-- Status Header Banner -->
        <div class="text-center space-y-2">
            <div class="w-16 h-16 bg-rose-50 text-rose-600 rounded-full flex items-center justify-center mx-auto text-2xl shadow-inner animate-bounce">
                🍽️
            </div>
            <h1 class="text-lg font-black text-slate-900">Pesanan Berhasil Dibuat!</h1>
            <p class="text-xs text-slate-500">No. Transaksi: <b class="text-rose-600"><?= esc($order['kode_transaksi']) ?></b></p>
        </div>

        <!-- Detail Meja & Pemesan -->
        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 space-y-2 text-xs">
            <div class="flex justify-between">
                <span class="text-slate-400">Nomor Meja:</span>
                <span class="font-bold text-slate-800">Meja <?= esc($order['nomor_meja']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Nama Pemesan:</span>
                <span class="font-bold text-slate-800"><?= esc($order['nama_pemesan']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Status Pembayaran:</span>
                <span class="font-extrabold uppercase px-2 py-0.5 rounded text-[10px]"
                      :class="order.status_bayar === 'lunas' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                      x-text="order.status_bayar"></span>
            </div>
        </div>

        <!-- Alur Ikon Status Dapur (Step Tracker) -->
        <div class="space-y-3">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Status Dapur Tenant:</h3>
            
            <div class="space-y-2.5">
                <template x-for="item in items" :key="item.id">
                    <div class="bg-slate-50 border border-slate-100 p-3.5 rounded-2xl flex justify-between items-center">
                        <div>
                            <span class="text-[9px] font-extrabold bg-rose-100 text-rose-700 px-2 py-0.5 rounded-md" x-text="item.nama_tenant"></span>
                            <h4 class="font-bold text-xs text-slate-800 mt-1" x-text="item.nama_menu + ' (x' + item.qty + ')'"></h4>
                        </div>

                        <!-- Badge Ikon Status Dapur -->
                        <div>
                            <span class="px-2.5 py-1 rounded-xl text-[10px] font-extrabold flex items-center space-x-1"
                                  :class="{
                                      'bg-amber-100 text-amber-700': item.status_dapur === 'menunggu',
                                      'bg-blue-100 text-blue-700': item.status_dapur === 'diproses',
                                      'bg-emerald-100 text-emerald-700': item.status_dapur === 'siap'
                                  }">
                                <span x-text="item.status_dapur === 'menunggu' ? '⏳ Menunggu' : (item.status_dapur === 'diproses' ? '🔥 Dimasak' : '✅ Siap Saji')"></span>
                            </span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Tombol Aksi: Pesanan Sudah Diterima -->
        <div class="pt-2">
            <button @click="konfirmasiTerima()" 
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-2xl text-xs shadow-lg shadow-emerald-600/30 transition flex items-center justify-center space-x-2">
                <span>✓ Pesanan Sudah Diterima & Selesai</span>
            </button>
            <p class="text-[10px] text-slate-400 text-center mt-2">Klik tombol ini setelah makanan diantar ke meja Anda agar meja kembali kosong otomatis.</p>
        </div>

    </div>

    <!-- Script Polling Real-time Status via Alpine.js -->
    <script>
        function statusApp() {
            return {
                order: <?= json_encode($order) ?>,
                items: <?= json_encode($items) ?>,
                init() {
                    // Polling setiap 3 detik untuk cek update status dari dapur
                    setInterval(() => {
                        fetch('/customer/status-json/<?= $order['id'] ?>', {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status !== 'not_found') {
                                this.order = data.order;
                                this.items = data.items;
                            }
                        });
                    }, 3000);
                },
                konfirmasiTerima() {
                    if (confirm("Pastikan makanan sudah Anda terima. Selesaikan pesanan dan kosongkan meja?")) {
                        fetch('/customer/terimaMakanan/<?= $order['id'] ?>', {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                alert("Terima kasih! Pesanan selesai.");
                                window.location.href = '/customer?meja=<?= $order['nomor_meja'] ?>';
                            }
                        });
                    }
                }
            }
        }
    </script>
</body>
</html>