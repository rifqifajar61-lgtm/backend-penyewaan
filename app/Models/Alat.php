<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alat extends Model
{
    use HasFactory;

    protected $table = 'alats';
    protected $primaryKey = 'alat_id';

    protected $fillable = [
        'alat_kategori_id',
        'alat_nama',
        'alat_deskripsi',
        'alat_gambar',
        'alat_hargaperhari',
        'alat_stok',
    ];

    // Accessor supaya frontend bisa langsung pakai $alat->alat_gambar_url
    // sebagai URL foto yang bisa diakses browser, tanpa perlu tahu detail
    // path storage-nya. Otomatis muncul di response JSON karena masuk $appends.
    protected $appends = ['alat_gambar_url'];

    public function getAlatGambarUrlAttribute(): ?string
    {
        if (!$this->alat_gambar) {
            return null;
        }
        return asset('storage/' . $this->alat_gambar);
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'alat_kategori_id', 'kategori_id');
    }

    public function penyewaanDetail()
    {
        return $this->hasMany(PenyewaanDetail::class, 'penyewaan_detail_alat_id', 'alat_id');
    }
}