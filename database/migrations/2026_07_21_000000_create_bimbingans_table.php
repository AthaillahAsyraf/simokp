<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bimbingans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained()->cascadeOnDelete();
            $table->enum('jenis', ['laporan', 'acc_seminar'])->default('laporan');
            $table->text('keterangan');
            $table->string('file')->nullable();
            $table->string('file_asli')->nullable();
            $table->enum('status', ['menunggu', 'revisi', 'disetujui'])->default('menunggu');
            $table->text('catatan_dosen')->nullable();
            $table->timestamp('ditinjau_at')->nullable();
            $table->timestamps();
            $table->index(['mahasiswa_id', 'jenis', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('bimbingans'); }
};
