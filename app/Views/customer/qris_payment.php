<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran QRIS - Smart Kantin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4 font-sans" x-data="qrisTimer()">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        
        <!-- Header Banner -->
        <div class="bg-red-600 text-white p-4 text-center">
            <h1 class="font-bold text-base tracking-wide">SMART KANTIN PAYMENT</h1>
            <p class="text-xs opacity-90 mt-0.5">Standar QR Code Pembayaran Nasional (QRIS)</p>
        </div>

        <!-- Body Konten -->
        <div class="p-6 space-y-5 text-center">
            
            <!-- Timer Hitung Mundur -->
            <div class="bg-amber-50 border border-amber-200 text-amber-800 p-3 rounded-xl flex items-center justify-between text-xs font-semibold">
                <span>Sisa Waktu Pembayaran:</span>
                <span class="text-sm font-bold bg-amber-100 px-3 py-1 rounded-lg text-red-600" x-text="formatTime">05:00</span>
            </div>

            <!-- Detail Tagihan -->
            <div>
                <p class="text-xs text-gray-400">Total Tagihan (Meja <?= esc($order['nomor_meja']) ?>)</p>
                <p class="text-2xl font-black text-gray-800 mt-1">Rp <?= number_format($order['total_bayar'], 0, ',', '.') ?></p>
                <p class="text-[11px] text-gray-400 mt-0.5">No. Transaksi: <?= esc($order['kode_transaksi']) ?></p>
            </div>

            <!-- Kotak QR Code QRIS -->
            <div class="bg-gray-50 border-2 border-dashed border-gray-300 p-4 rounded-2xl inline-block shadow-inner">
                <div class="bg-white p-2 rounded-xl shadow-sm inline-block">
                    <!-- Generator QR Code Dinamis -->
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=00020101021226580016ID.CO.SMARTKANTIN011893600914<?= $order['kode_transaksi'] ?>5204581253033605802ID5910SmartKantin6006Surabaya6304" 
                         alt="QRIS Code" class="w-48 h-48 mx-auto">
                </div>
                <div class="mt-2 flex justify-center items-center space-x-1">
                    <span class="text-[10px] font-extrabold tracking-widest text-red-600 bg-red-100 px-2 py-0.5 rounded">QRIS</span>
                    <span class="text-[10px] font-bold text-gray-600">DINAMIS</span>
                </div>
            </div>

            <p class="text-xs text-gray-500">
                Scan QR menggunakan m-Banking (BCA, Mandiri, BRI, BNI) atau E-Wallet (GoPay, OVO, DANA, ShopeePay).
            </p>

            <!-- Tombol Aksi Simulasi -->
            <div class="space-y-2 pt-2">
                <button @click="simpanPembayaran()" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl text-sm shadow transition">
                    ✓ Simulasi Bayar Berhasil
                </button>
                <a href="/customer?meja=<?= $order['nomor_meja'] ?>" class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-2.5 rounded-xl text-xs transition">
                    Batalkan / Kembali
                </a>
            </div>

        </div>

        <!-- Footer Modal -->
        <div class="bg-gray-50 px-6 py-3 border-t text-center text-[10px] text-gray-400">
            Didukung oleh Sistem Smart Kantin Multi-Tenant © 2026
        </div>

    </div>

    <!-- Script Alpine.js untuk Hitung Mundur (Timer 5 Menit) -->
    <script>
        function qrisTimer() {
            return {
                timer: 300, // 300 detik = 5 menit
                interval: null,
                init() {
                    this.interval = setInterval(() => {
                        if (this.timer > 0) {
                            this.timer--;
                        } else {
                            clearInterval(this.interval);
                            alert("Waktu pembayaran QRIS telah habis!");
                            window.location.href = '/customer?meja=<?= $order['nomor_meja'] ?>';
                        }
                    }, 1000);
                },
                get formatTime() {
                    let minutes = Math.floor(this.timer / 60);
                    let seconds = this.timer % 60;
                    return String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
                },
                simpanPembayaran() {
                    fetch('/customer/proses-bayar-qris/<?= $order['id'] ?>', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            window.location.href = '/customer/sukses/<?= $order['id'] ?>';
                        }
                    });
                }
            }
        }
    </script>
</body>
</html>