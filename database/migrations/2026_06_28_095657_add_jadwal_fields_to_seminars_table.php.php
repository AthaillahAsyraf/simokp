<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Tambahan buat fitur jadwal seminar yang bisa deteksi bentrok (ruangan/dosen)
 * dan pengajuan dari mahasiswa.
 *
 * Kolom lama (`jam`, `dosen_penguji` string) TETAP DIBIARKAN ada demi keamanan
 * data lama, tapi sudah tidak dipakai lagi oleh kode baru. Kode baru pakai
 * jam_mulai/jam_selesai (perlu dua titik waktu buat cek overlap) dan
 * dosen_penguji_id (FK, biar bisa dicek bentrok per-dosen, bukan cocok-cocokan teks).
 *
 * Kolom status diubah dari enum ke string biasa (validasi pindah ke level
 * aplikasi) supaya gampang nambah status baru: menunggu_persetujuan, ditolak.
 */
return new class extends Migration {
    public function up(): void {
        Schema::table('seminars', function (Blueprint $table) {
            if (!Schema::hasColumn('seminars', 'jam_mulai')) {
                $table->time('jam_mulai')->nullable()->after('tanggal');
            }
            if (!Schema::hasColumn('seminars', 'jam_selesai')) {
                $table->time('jam_selesai')->nullable()->after('jam_mulai');
            }
            if (!Schema::hasColumn('seminars', 'dosen_penguji_id')) {
                $table->foreignId('dosen_penguji_id')->nullable()->after('dosen_penguji')
                    ->constrained('dosens')->nullOnDelete();
            }
            if (!Schema::hasColumn('seminars', 'diajukan_oleh')) {
                $table->string('diajukan_oleh')->default('admin')->after('status'); // admin | mahasiswa
            }
        });

        // Backfill jam_mulai dari kolom jam lama yang sudah ada datanya
        DB::table('seminars')->whereNotNull('jam')->whereNull('jam_mulai')->update(['jam_mulai' => DB::raw('jam')]);

        // Kolom `jam` lama NOT NULL tanpa default — kode baru sudah tidak mengisinya lagi
        // (pakai jam_mulai/jam_selesai), jadi harus dilonggarkan jadi nullable supaya insert baru tidak gagal.
        Schema::table('seminars', function (Blueprint $table) {
            $table->time('jam')->nullable()->change();
        });

        // Lepas constraint enum lama -> string biasa, supaya menampung status baru
        Schema::table('seminars', function (Blueprint $table) {
            $table->string('status')->default('terjadwal')->change();
        });
    }

    public function down(): void {
        Schema::table('seminars', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dosen_penguji_id');
            $table->dropColumn(['jam_mulai', 'jam_selesai', 'diajukan_oleh']);
        });
    }
};