<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggan_datas', function (Blueprint $table) {
            $table->id('pelanggan_data_id');
            $table->foreignId('pelanggan_data_pelanggan_id')
                ->constrained('pelanggans', 'pelanggan_id')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->enum('pelanggan_data_jenis', ['KTP', 'SIM']);
            $table->string('pelanggan_data_file', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggan_datas');
    }
};