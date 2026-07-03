<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Nilai Pembimbing" versi lama (satu angka 0-100, diisi lewat modal
 * terpisah) dihapus karena substansinya sudah tercakup di rubrik seminar
 * (kolom nilai_seminar + 6 kolom seminar_*), yang sekarang DITAMPILKAN
 * sebagai "Nilai Pembimbing" di UI. Menyimpan dua-duanya cuma bikin bingung
 * mana yang jadi acuan nilai_akhir.
 *
 * PERHATIAN: migrasi ini menghapus data nilai_pembimbing & catatan_pembimbing
 * yang sudah pernah diisi dosen lewat form lama. Kalau ada data lama yang
 * masih perlu diarsipkan, backup tabel `nilais` dulu sebelum migrate.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            if (Schema::hasColumn('nilais', 'nilai_pembimbing')) {
                $table->dropColumn('nilai_pembimbing');
            }
            if (Schema::hasColumn('nilais', 'catatan_pembimbing')) {
                $table->dropColumn('catatan_pembimbing');
            }
        });
    }

    public function down(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            $table->decimal('nilai_pembimbing', 5, 2)->nullable()->after('nilai_lapangan');
            $table->text('catatan_pembimbing')->nullable()->after('nilai_pembimbing');
        });
    }
};