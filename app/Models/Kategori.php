<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategoris';
    protected $primaryKey = 'kategori_id';

    protected $fillable = [
        'kategori_nama',
    ];

    public function alat()
    {
        return $this->hasMany(Alat::class, 'alat_kategori_id', 'kategori_id');
    }
}