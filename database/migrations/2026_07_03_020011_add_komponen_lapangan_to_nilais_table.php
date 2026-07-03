<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "FORM NILAI PEMBIMBING LAPANGAN" (format resmi instansi) punya 8 komponen
 * dengan bobot SAMA RATA (beda dengan rubrik seminar yang berbeda-beda
 * persentase) — rata-rata sederhana dari 8 angka:
 *
 *   A. Kedisiplinan   1. Jumlah Kehadiran
 *                     2. Taat Tata Tertib
 *   B. Kerjasama      1. Dengan Anggota Kelompok
 *                     2. Dengan Kelompok Lain
 *                     3. Pembimbing
 *   C. Prestasi kerja 1. Inovasi
 *                     2. Kemampuan Menyelesaikan Tugas
 *                     3. Keseriusan
 *
 * `nilai_lapangan` (kolom lama) tetap dipakai sebagai HASIL AKHIR terhitung
 * (rata-rata 8 komponen) — supaya hitungNilaiAkhir() di tempat lain tidak
 * perlu diubah. Kolom-kolom di bawah ini cuma menyimpan rincian per komponen.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            if (!Schema::hasColumn('nilais', 'lapangan_kehadiran')) {
                $table->decimal('lapangan_kehadiran', 5, 2)->nullable()->after('nilai_lapangan');
            }
            if (!Schema::hasColumn('nilais', 'lapangan_tata_tertib')) {
                $table->decimal('lapangan_tata_tertib', 5, 2)->nullable()->after('lapangan_kehadiran');
            }
            if (!Schema::hasColumn('nilais', 'lapangan_kerjasama_anggota')) {
                $table->decimal('lapangan_kerjasama_anggota', 5, 2)->nullable()->after('lapangan_tata_tertib');
            }
            if (!Schema::hasColumn('nilais', 'lapangan_kerjasama_kelompok_lain')) {
                $table->decimal('lapangan_kerjasama_kelompok_lain', 5, 2)->nullable()->after('lapangan_kerjasama_anggota');
            }
            if (!Schema::hasColumn('nilais', 'lapangan_kerjasama_pembimbing')) {
                $table->decimal('lapangan_kerjasama_pembimbing', 5, 2)->nullable()->after('lapangan_kerjasama_kelompok_lain');
            }
            if (!Schema::hasColumn('nilais', 'lapangan_inovasi')) {
                $table->decimal('lapangan_inovasi', 5, 2)->nullable()->after('lapangan_kerjasama_pembimbing');
            }
            if (!Schema::hasColumn('nilais', 'lapangan_tugas')) {
                $table->decimal('lapangan_tugas', 5, 2)->nullable()->after('lapangan_inovasi');
            }
            if (!Schema::hasColumn('nilais', 'lapangan_keseriusan')) {
                $table->decimal('lapangan_keseriusan', 5, 2)->nullable()->after('lapangan_tugas');
            }
        });
    }

    public function down(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            $table->dropColumn([
                'lapangan_kehadiran', 'lapangan_tata_tertib',
                'lapangan_kerjasama_anggota', 'lapangan_kerjasama_kelompok_lain', 'lapangan_kerjasama_pembimbing',
                'lapangan_inovasi', 'lapangan_tugas', 'lapangan_keseriusan',
            ]);
        });
    }
};