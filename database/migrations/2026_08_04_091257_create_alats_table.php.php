<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alats', function (Blueprint $table) {
            $table->id('alat_id');
            $table->foreignId('alat_kategori_id')
                ->constrained('kategoris', 'kategori_id')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->string('alat_nama', 150);
            $table->string('alat_deskripsi', 255);
           $table->string('alat_gambar')->nullable();
            $table->integer('alat_hargaperhari');
            $table->integer('alat_stok');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alats');
    }
};