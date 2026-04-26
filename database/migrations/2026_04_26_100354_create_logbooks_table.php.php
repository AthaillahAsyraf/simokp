<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('logbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->date('tanggal');
            $table->text('kegiatan');
            $table->string('jam_mulai', 10)->nullable();
            $table->string('jam_selesai', 10)->nullable();
            $table->enum('status_instansi', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->text('catatan_instansi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbooks');
    }
};