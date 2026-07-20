<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migrasi 2026_07_20_094914_rename_instansi_role_to_pembimbing_lapangan
 * seharusnya HANYA mengganti users.role ('instansi' -> 'pembimbing_lapangan'),
 * karena itu satu-satunya yang menandai role LOGIN.
 *
 * Tapi migrasi tsb juga ikut mengganti tag di surats.pengirim_role/penerima_role
 * dan chat_pesan.pengirim_role, padahal tag itu menandai pihak INSTANSI
 * (perusahaan, tabel `instansis`) sebagai lawan bicara surat/chat — bukan role
 * login. Semua controller (Admin/Dosen/Instansi/Mahasiswa SuratController,
 * Instansi & Dosen ChatController, model Surat & ChatPesan) masih konsisten
 * menulis/membaca 'instansi', jadi migrasi ini membalikkan bagian tsb saja.
 *
 * users.role TIDAK disentuh di sini — tetap 'pembimbing_lapangan'.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) surats.pengirim_role / penerima_role — kolom string bebas
        if (Schema::hasTable('surats')) {
            DB::table('surats')->where('pengirim_role', 'pembimbing_lapangan')->update(['pengirim_role' => 'instansi']);
            DB::table('surats')->where('penerima_role', 'pembimbing_lapangan')->update(['penerima_role' => 'instansi']);
        }

        // 2) chat_pesan.pengirim_role — ENUM, harus dilebarkan dulu sebelum data dipindah
        if (Schema::hasTable('chat_pesan')) {
            DB::statement("ALTER TABLE chat_pesan MODIFY pengirim_role ENUM('instansi','dosen','pembimbing_lapangan')");
            DB::table('chat_pesan')->where('pengirim_role', 'pembimbing_lapangan')->update(['pengirim_role' => 'instansi']);
            DB::statement("ALTER TABLE chat_pesan MODIFY pengirim_role ENUM('instansi','dosen')");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('chat_pesan')) {
            DB::statement("ALTER TABLE chat_pesan MODIFY pengirim_role ENUM('instansi','dosen','pembimbing_lapangan')");
            DB::table('chat_pesan')->where('pengirim_role', 'instansi')->update(['pengirim_role' => 'pembimbing_lapangan']);
            DB::statement("ALTER TABLE chat_pesan MODIFY pengirim_role ENUM('dosen','pembimbing_lapangan')");
        }

        if (Schema::hasTable('surats')) {
            DB::table('surats')->where('pengirim_role', 'instansi')->update(['pengirim_role' => 'pembimbing_lapangan']);
            DB::table('surats')->where('penerima_role', 'instansi')->update(['penerima_role' => 'pembimbing_lapangan']);
        }
    }
};