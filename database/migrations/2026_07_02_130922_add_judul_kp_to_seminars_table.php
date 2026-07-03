<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Judul KP/PKL ditaruh di tabel `seminars`, bukan `mahasiswas`, karena yang
 * berwenang mengisinya adalah mahasiswa sendiri saat mengajukan jadwal
 * seminar (bukan admin/dosen di tempat lain). Nempel ke pengajuan seminar
 * juga pas secara alur: judul baru "final" begitu mahasiswa siap seminar,
 * dan dari sinilah judul otomatis ikut ke lembar penilaian dosen pembimbing.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('seminars', function (Blueprint $table) {
            if (!Schema::hasColumn('seminars', 'judul_kp')) {
                $table->string('judul_kp')->nullable()->after('mahasiswa_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('seminars', function (Blueprint $table) {
            $table->dropColumn('judul_kp');
        });
    }
};