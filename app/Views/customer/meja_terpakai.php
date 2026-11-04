<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gagal Scan - Meja Terpakai</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4 font-sans">
    <div class="max-w-sm w-full bg-white p-6 rounded-xl shadow-md text-center space-y-4">
        <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">
            🚫
        </div>
        <h1 class="text-lg font-bold text-gray-800">Gagal Scan! Meja <?= $nomor_meja ?> Sedang Terisi</h1>
        <p class="text-xs text-gray-500 leading-relaxed">
            Maaf, QR Code meja ini gagal diakses karena sedang digunakan oleh perangkat/pelanggan lain. Silakan pilih meja kosong yang lain.
        </p>
        <div class="pt-2">
            <a href="javascript:location.reload()" class="block w-full bg-blue-600 text-white font-bold text-xs py-2.5 rounded-lg hover:bg-blue-700 transition">
                Coba Scan Ulang
            </a>
        </div>
    </div>
</body>
</html>