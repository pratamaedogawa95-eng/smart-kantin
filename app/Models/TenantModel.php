<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantModel extends Model
{
    protected $table            = 'tenants';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['nama_tenant', 'nama_pemilik', 'nomor_stand', 'status'];
    protected $useTimestamps   = true;
}