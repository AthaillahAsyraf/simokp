<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Yang menilai performa kerja mahasiswa sehari-hari di lapangan itu Pembimbing
 * Lapangan, bukan "Instansi" sebagai institusi — jadi nama kolom diperjelas
 * sampai level kode, bukan cuma label di tampilan.
 *
 * Pakai pola tambah-kolom-baru -> copy data -> hapus kolom lama, supaya aman
 * lintas database (MySQL & SQLite) tanpa pakai renameColumn().
 */
return new class extends Migration {
    public function up(): void {
        Schema::table('nilais', function (Blueprint $table) {
            if (!Schema::hasColumn('nilais', 'nilai_lapangan')) {
                $table->decimal('nilai_lapangan', 5, 2)->nullable()->after('mahasiswa_id');
            }
            if (!Schema::hasColumn('nilais', 'catatan_lapangan')) {
                $table->text('catatan_lapangan')->nullable()->after('nilai_lapangan');
            }
        });

        DB::table('nilais')->update([
            'nilai_lapangan'   => DB::raw('nilai_instansi'),
            'catatan_lapangan' => DB::raw('catatan_instansi'),
        ]);

        Schema::table('nilais', function (Blueprint $table) {
            $table->dropColumn(['nilai_instansi', 'catatan_instansi']);
        });
    }

    public function down(): void {
        Schema::table('nilais', function (Blueprint $table) {
            $table->decimal('nilai_instansi', 5, 2)->nullable()->after('mahasiswa_id');
            $table->text('catatan_instansi')->nullable()->after('nilai_instansi');
        });

        DB::table('nilais')->update([
            'nilai_instansi'   => DB::raw('nilai_lapangan'),
            'catatan_instansi' => DB::raw('catatan_lapangan'),
        ]);

        Schema::table('nilais', function (Blueprint $table) {
            $table->dropColumn(['nilai_lapangan', 'catatan_lapangan']);
        });
    }
};