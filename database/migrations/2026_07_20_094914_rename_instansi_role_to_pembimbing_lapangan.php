<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) users.role: ubah ENUM dulu (tambah nilai baru), migrasikan data, baru buang nilai lama
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','dosen','instansi','mahasiswa','pembimbing_lapangan') DEFAULT 'mahasiswa'");
        DB::table('users')->where('role', 'instansi')->update(['role' => 'pembimbing_lapangan']);
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','dosen','mahasiswa','pembimbing_lapangan') DEFAULT 'mahasiswa'");

        // 2) surats.pengirim_role / penerima_role (kolom string bebas, aman langsung update)
        if (Schema::hasTable('surats')) {
            DB::table('surats')->where('pengirim_role', 'instansi')->update(['pengirim_role' => 'pembimbing_lapangan']);
            DB::table('surats')->where('penerima_role', 'instansi')->update(['penerima_role' => 'pembimbing_lapangan']);
        }

        // 3) chat_pesan.pengirim_role (ENUM)
        if (Schema::hasTable('chat_pesan')) {
            DB::statement("ALTER TABLE chat_pesan MODIFY pengirim_role ENUM('instansi','dosen','pembimbing_lapangan')");
            DB::table('chat_pesan')->where('pengirim_role', 'instansi')->update(['pengirim_role' => 'pembimbing_lapangan']);
            DB::statement("ALTER TABLE chat_pesan MODIFY pengirim_role ENUM('dosen','pembimbing_lapangan')");
        }
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','dosen','mahasiswa','pembimbing_lapangan','instansi') DEFAULT 'mahasiswa'");
        DB::table('users')->where('role', 'pembimbing_lapangan')->update(['role' => 'instansi']);
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','dosen','instansi','mahasiswa') DEFAULT 'mahasiswa'");

        if (Schema::hasTable('surats')) {
            DB::table('surats')->where('pengirim_role', 'pembimbing_lapangan')->update(['pengirim_role' => 'instansi']);
            DB::table('surats')->where('penerima_role', 'pembimbing_lapangan')->update(['penerima_role' => 'instansi']);
        }

        if (Schema::hasTable('chat_pesan')) {
            DB::statement("ALTER TABLE chat_pesan MODIFY pengirim_role ENUM('instansi','dosen','pembimbing_lapangan')");
            DB::table('chat_pesan')->where('pengirim_role', 'pembimbing_lapangan')->update(['pengirim_role' => 'instansi']);
            DB::statement("ALTER TABLE chat_pesan MODIFY pengirim_role ENUM('instansi','dosen')");
        }
    }
};