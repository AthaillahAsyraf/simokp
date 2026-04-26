<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('progress_babs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->enum('bab', ['BAB I', 'BAB II', 'BAB III', 'BAB IV', 'BAB V', 'LAPORAN LENGKAP']);
            $table->enum('status', ['belum', 'proses', 'selesai'])->default('belum');
            $table->date('tanggal_update')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_babs');
    }
};