<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenyewaanDetail extends Model
{
    use HasFactory;

    protected $table = 'penyewaan_details';
    protected $primaryKey = 'penyewaan_detail_id';

    protected $fillable = [
        'penyewaan_detail_penyewaan_id',
        'penyewaan_detail_alat_id',
        'penyewaan_detail_jumlah',
        'penyewaan_detail_subharga',
    ];

    public function penyewaan()
    {
        return $this->belongsTo(Penyewaan::class, 'penyewaan_detail_penyewaan_id', 'penyewaan_id');
    }

    public function alat()
    {
        return $this->belongsTo(Alat::class, 'penyewaan_detail_alat_id', 'alat_id');
    }
}