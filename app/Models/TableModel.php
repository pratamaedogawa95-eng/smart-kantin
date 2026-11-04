<?php

namespace App\Models;

use CodeIgniter\Model;

class TableModel extends Model
{
    protected $table              = 'tables';
    protected $primaryKey         = 'id';
    protected $allowedFields      = ['nomor_meja', 'kapasitas', 'status', 'device_session'];
    protected $useTimestamps      = true;
}