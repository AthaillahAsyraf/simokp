<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->date('tanggal');

            // ── Absen masuk ────────────────────────────────
            $table->time('jam_masuk')->nullable();
            $table->decimal('lat_masuk', 10, 7)->nullable();
            $table->decimal('lng_masuk', 10, 7)->nullable();
            $table->unsignedInteger('akurasi_gps_masuk')->nullable()->comment('meter');
            $table->unsignedInteger('jarak_masuk')->nullable()->comment('meter dari instansi');
            $table->string('foto_masuk')->nullable();
            $table->enum('status_masuk', ['valid', 'diluar_radius'])->nullable();
            $table->string('ip_masuk', 45)->nullable();

            // ── Absen pulang ───────────────────────────────
            $table->time('jam_keluar')->nullable();
            $table->decimal('lat_keluar', 10, 7)->nullable();
            $table->decimal('lng_keluar', 10, 7)->nullable();
            $table->unsignedInteger('akurasi_gps_keluar')->nullable();
            $table->unsignedInteger('jarak_keluar')->nullable();
            $table->string('foto_keluar')->nullable();
            $table->enum('status_keluar', ['valid', 'diluar_radius'])->nullable();
            $table->string('ip_keluar', 45)->nullable();

            $table->text('catatan_dosen')->nullable();
            $table->timestamps();

            // Satu mahasiswa hanya boleh punya 1 baris absen per tanggal
            $table->unique(['mahasiswa_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};