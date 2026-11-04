<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderItemModel extends Model
{
    protected $table            = 'order_items';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['order_id', 'tenant_id', 'menu_id', 'qty', 'harga_satuan', 'status_dapur'];
    protected $useTimestamps   = false;
}