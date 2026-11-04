<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuModel extends Model
{
    protected $table            = 'menus';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['tenant_id', 'nama_menu', 'kategori', 'harga', 'foto', 'status'];
    protected $useTimestamps   = true;
}