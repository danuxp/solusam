<?php

namespace App\Models;

use CodeIgniter\Model;

class Sampah extends Model
{
    protected $table            = 'data_sampah';
    protected $allowedFields    = ['nama_sampah', 'harga_beli', 'harga_jual', 'satuan', 'client_id'];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
