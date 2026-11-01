<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggans';

    protected $primaryKey = 'pelanggan_id';

    protected $fillable = [
        'pelanggan_nama',
        'pelanggan_alamat',
        'pelanggan_notelp',
        'pelanggan_email',
        'pelanggan_password',
    ];

    protected $hidden = [
        'pelanggan_password',
    ];

    public function data()
    {
        return $this->hasMany(
            PelangganData::class,
            'pelanggan_data_pelanggan_id',
            'pelanggan_id'
        );
    }

    public function penyewaan()
    {
        return $this->hasMany(
            Penyewaan::class,
            'penyewaan_pelanggan_id',
            'pelanggan_id'
        );
    }
}