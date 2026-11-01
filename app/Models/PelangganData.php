<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PelangganData extends Model
{
    use HasFactory;

    protected $table = 'pelanggan_datas';
    protected $primaryKey = 'pelanggan_data_id';

    protected $fillable = [
        'pelanggan_data_pelanggan_id',
        'pelanggan_data_jenis',
        'pelanggan_data_file',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_data_pelanggan_id', 'pelanggan_id');
    }
}