<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identitas Pengunjung - Smart Kantin ITATS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4 font-sans" x-data="identitasApp()">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl p-6 md:p-8 space-y-6 border border-slate-100">
        
        <!-- Header Judul -->
        <div class="text-center space-y-1">
            <span class="bg-sky-50 text-sky-600 text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">SMART KANTIN ITATS</span>
            <h1 class="text-xl font-black text-slate-900 mt-2">Identitas Pengunjung</h1>
            <p class="text-xs text-slate-500">Silakan isi data diri Anda untuk Meja <b class="text-sky-600 underline"><?= esc($nomor_meja) ?></b></p>
        </div>

        <!-- Form Input -->
        <form action="/customer/simpan-identitas" method="post" class="space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="nomor_meja" value="<?= esc($nomor_meja) ?>">

            <!-- Status Pengguna -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700">Status Pengguna</label>
                <select name="tipe_pengguna" id="tipe_pengguna" x-model="tipe" @change="cariDataCivitas()" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
                    <option value="mahasiswa">Mahasiswa / Siswa</option>
                    <option value="dosen">Dosen / Staf</option>
                    <option value="umum">Tamu / Umum</option>
                </select>
            </div>

            <!-- Nomor Induk (NPM/NIP) -->
            <div class="space-y-1.5">
                <div class="flex justify-between items-center">
                    <label class="text-xs font-bold text-slate-700">NPM / NIS / NIP (Opsional)</label>
                    <!-- Indikator Loading Auto-Search -->
                    <span x-show="loading" class="text-[10px] text-sky-600 font-bold animate-pulse">🔍 Mencari data...</span>
                    <span x-show="found" class="text-[10px] text-emerald-600 font-bold">✓ Data Ditemukan</span>
                </div>
                <input type="text" 
                       name="nomor_identitas" 
                       id="nomor_identitas" 
                       x-model="nomorInduk" 
                       @input.debounce.300ms="cariDataCivitas()" 
                       placeholder="Ketik nomor induk atau 3-4 digit terakhir..." 
                       class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3 text-xs text-slate-800 focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
            </div>

            <!-- Nama Lengkap (Otomatis Terisi) -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700">Nama Lengkap <span class="text-sky-600">*</span></label>
                <input type="text" 
                       name="nama_pemesan" 
                       id="nama_pemesan" 
                       x-model="nama" 
                       required 
                       placeholder="Masukkan nama Anda..." 
                       class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3 text-xs text-slate-800 focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
            </div>

            <!-- Jurusan / Instansi (Otomatis Terisi) -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700">Jurusan / Program Studi / Instansi</label>
                <input type="text" 
                       name="jurusan_instansi" 
                       id="jurusan_instansi" 
                       x-model="jurusan" 
                       placeholder="Cth: S1 Sistem Informasi" 
                       class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3 text-xs text-slate-800 focus:ring-2 focus:ring-sky-500 focus:outline-none transition">
            </div>

            <!-- Tombol Submit (Warna Biru Muda / Sky) -->
            <button type="submit" class="w-full bg-sky-600 hover:bg-sky-700 text-white font-bold py-3.5 rounded-2xl text-xs shadow-lg shadow-sky-600/30 transition flex items-center justify-center space-x-2">
                <span>Masuk ke Katalog Menu</span>
                <span>➔</span>
            </button>
        </form>

    </div>

    <!-- Script AJAX Live-Search Alpine.js -->
    <script>
        function identitasApp() {
            return {
                tipe: 'mahasiswa',
                nomorInduk: '',
                nama: '',
                jurusan: '',
                loading: false,
                found: false,
                cariDataCivitas() {
                    if (this.nomorInduk.trim().length < 2 || this.tipe === 'umum') {
                        this.found = false;
                        return;
                    }

                    this.loading = true;
                    this.found = false;

                    fetch(`/customer/cari-civitas?q=${encodeURIComponent(this.nomorInduk)}&tipe=${this.tipe}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.loading = false;
                        if (data.status === 'found') {
                            this.nama = data.result.nama || '';
                            this.jurusan = data.result.jurusan_instansi || data.result.jurusan || data.result.prodi || '';
                            this.found = true;
                        } else {
                            this.found = false;
                        }
                    })
                    .catch(() => {
                        this.loading = false;
                        this.found = false;
                    });
                }
            }
        }
    </script>

</body>
</html>