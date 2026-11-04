<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table            = 'orders';
    protected $primaryKey       = 'id';
    
    // PERBAIKAN: Tambahkan kolom identitas agar diizinkan masuk ke database
    protected $allowedFields    = [
        'kode_transaksi', 
        'nomor_meja', 
        'total_bayar', 
        'status_bayar', 
        'waktu_siap',
        'tipe_pengguna',
        'nomor_identitas',
        'nama_pemesan',
        'jurusan_instansi'
    ];
    
    protected $useTimestamps    = true;
}