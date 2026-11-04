<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu - Smart Kantin ITATS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4 font-sans">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-6 space-y-6 border border-slate-100">
        
        <!-- Header -->
        <div class="flex justify-between items-center border-b pb-3">
            <div>
                <span class="bg-sky-50 text-sky-600 text-[10px] font-extrabold px-2.5 py-1 rounded-full uppercase tracking-wider">Tenant Panel</span>
                <h1 class="text-base font-black text-slate-900 mt-1">Edit Data Menu</h1>
            </div>
            <a href="/tenant" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition">✕ Batal</a>
        </div>

        <!-- Form Edit -->
        <form action="/tenant/menu/update/<?= $menu['id'] ?>" method="post" enctype="multipart/form-data" class="space-y-3.5 text-xs">
            <?= csrf_field() ?>

            <!-- Nama Menu / Judul -->
            <div class="space-y-1">
                <label class="font-bold text-slate-700">Nama Menu / Judul</label>
                <input type="text" 
                       name="nama_menu" 
                       value="<?= esc($menu['nama_menu']) ?>" 
                       required 
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 font-semibold text-slate-800 focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
            </div>

            <!-- Kategori & Harga -->
            <div class="grid grid-cols-2 gap-2">
                <div class="space-y-1">
                    <label class="font-bold text-slate-700">Kategori</label>
                    <select name="kategori" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 font-semibold text-slate-800 focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                        <option value="Makanan" <?= ($menu['kategori'] == 'Makanan') ? 'selected' : '' ?>>Makanan</option>
                        <option value="Minuman" <?= ($menu['kategori'] == 'Minuman') ? 'selected' : '' ?>>Minuman</option>
                        <option value="Cemilan" <?= ($menu['kategori'] == 'Cemilan') ? 'selected' : '' ?>>Cemilan</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="font-bold text-slate-700">Harga (Rp)</label>
                    <input type="number" 
                           name="harga" 
                           value="<?= esc($menu['harga']) ?>" 
                           required 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 font-semibold text-slate-800 focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                </div>
            </div>

            <!-- Status -->
            <div class="space-y-1">
                <label class="font-bold text-slate-700">Status Ketersediaan</label>
                <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 font-semibold text-slate-800 focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                    <option value="tersedia" <?= ($menu['status'] == 'tersedia') ? 'selected' : '' ?>>Tersedia</option>
                    <option value="habis" <?= ($menu['status'] == 'habis') ? 'selected' : '' ?>>Habis</option>
                </select>
            </div>

            <!-- Upload / Ganti Foto -->
            <div class="space-y-1.5">
                <label class="font-bold text-slate-700">Foto Menu (Opsional)</label>
                
                <!-- Preview Foto Lama -->
                <?php if (!empty($menu['foto'])): ?>
                    <div class="mb-2 flex items-center space-x-3 bg-slate-50 p-2 rounded-xl border border-slate-200">
                        <img src="/uploads/menu/<?= esc($menu['foto']) ?>" alt="Foto Menu" class="w-10 h-10 object-cover rounded-lg">
                        <span class="text-[11px] text-slate-500 truncate">Foto saat ini aktif</span>
                    </div>
                <?php endif; ?>

                <input type="file" 
                       name="foto" 
                       accept="image/*" 
                       class="w-full text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-600 hover:file:bg-sky-100 transition">
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="w-full bg-sky-600 hover:bg-sky-700 text-white font-bold py-3 rounded-xl shadow-md shadow-sky-600/20 transition mt-2">
                Simpan Perubahan ➔
            </button>
        </form>

    </div>

</body>
</html>