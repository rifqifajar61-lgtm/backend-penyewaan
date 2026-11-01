<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyewaans', function (Blueprint $table) {
            $table->id('penyewaan_id');
            $table->foreignId('penyewaan_pelanggan_id')
                ->constrained('pelanggans', 'pelanggan_id')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->date('penyewaan_tglsewa');
            $table->date('penyewaan_tglkembali');
            $table->enum('penyewaan_sttspembayaran', ['Lunas', 'Belum Dibayar', 'DP'])
                ->default('Belum Dibayar');
            $table->enum('penyewaan_sttskembali', ['Sudah Kembali', 'Belum Kembali'])
                ->default('Belum Kembali');
            $table->integer('penyewaan_totalharga');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyewaans');
    }
};