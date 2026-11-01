<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyewaan_details', function (Blueprint $table) {
            $table->id('penyewaan_detail_id');
            $table->foreignId('penyewaan_detail_penyewaan_id')
                ->constrained('penyewaans', 'penyewaan_id')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('penyewaan_detail_alat_id')
                ->constrained('alats', 'alat_id')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->integer('penyewaan_detail_jumlah');
            $table->integer('penyewaan_detail_subharga');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyewaan_details');
    }
};