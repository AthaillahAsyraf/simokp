<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rincian "Lembar Penilaian Seminar Kerja Praktik — Dosen Pembimbing" (format
 * resmi jurusan) punya 6 aspek dengan bobot persentase berbeda, bukan satu
 * angka tunggal:
 *
 *   1. Seminar  a. Penguasaan materi/metode   20%
 *               b. Sikap ilmiah dan argumentasi 10%
 *               c. Teknik penyajian dan kebahasaan 10%
 *   2. Laporan  a. Originalitas                30%
 *               b. Relevansi dan keterpaduan   15%
 *               c. Penulisan (format dan bahasa) 15%
 *
 * `nilai_seminar` (kolom lama) tetap dipakai sebagai HASIL AKHIR terhitung
 * (weighted sum) — supaya nilai_akhir/hitungNilaiAkhir() di tempat lain tidak
 * perlu diubah. Kolom-kolom di bawah ini cuma menyimpan rincian per aspek.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            if (!Schema::hasColumn('nilais', 'seminar_penguasaan_materi')) {
                $table->decimal('seminar_penguasaan_materi', 5, 2)->nullable()->after('nilai_seminar');
            }
            if (!Schema::hasColumn('nilais', 'seminar_sikap_ilmiah')) {
                $table->decimal('seminar_sikap_ilmiah', 5, 2)->nullable()->after('seminar_penguasaan_materi');
            }
            if (!Schema::hasColumn('nilais', 'seminar_teknik_penyajian')) {
                $table->decimal('seminar_teknik_penyajian', 5, 2)->nullable()->after('seminar_sikap_ilmiah');
            }
            if (!Schema::hasColumn('nilais', 'seminar_originalitas')) {
                $table->decimal('seminar_originalitas', 5, 2)->nullable()->after('seminar_teknik_penyajian');
            }
            if (!Schema::hasColumn('nilais', 'seminar_relevansi')) {
                $table->decimal('seminar_relevansi', 5, 2)->nullable()->after('seminar_originalitas');
            }
            if (!Schema::hasColumn('nilais', 'seminar_penulisan')) {
                $table->decimal('seminar_penulisan', 5, 2)->nullable()->after('seminar_relevansi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            $table->dropColumn([
                'seminar_penguasaan_materi', 'seminar_sikap_ilmiah', 'seminar_teknik_penyajian',
                'seminar_originalitas', 'seminar_relevansi', 'seminar_penulisan',
            ]);
        });
    }
};