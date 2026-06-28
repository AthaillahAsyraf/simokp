<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aktor instansi cuma punya 1 kontak_person (penanggung jawab instansi).
 * Ini beda sama pembimbing lapangan, yang ngawasin mahasiswa langsung
 * sehari-hari dan bisa beda-beda orangnya walau instansinya sama.
 * Makanya kolom ini nempel di mahasiswa, bukan di instansi.
 */
return new class extends Migration {
    public function up(): void {
        Schema::table('mahasiswas', function (Blueprint $table) {
            if (!Schema::hasColumn('mahasiswas', 'pembimbing_lapangan_nama')) {
                $table->string('pembimbing_lapangan_nama')->nullable()->after('instansi_id');
            }
            if (!Schema::hasColumn('mahasiswas', 'pembimbing_lapangan_jabatan')) {
                $table->string('pembimbing_lapangan_jabatan')->nullable()->after('pembimbing_lapangan_nama');
            }
            if (!Schema::hasColumn('mahasiswas', 'pembimbing_lapangan_no_hp')) {
                $table->string('pembimbing_lapangan_no_hp')->nullable()->after('pembimbing_lapangan_jabatan');
            }
        });
    }

    public function down(): void {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropColumn(['pembimbing_lapangan_nama', 'pembimbing_lapangan_jabatan', 'pembimbing_lapangan_no_hp']);
        });
    }
};