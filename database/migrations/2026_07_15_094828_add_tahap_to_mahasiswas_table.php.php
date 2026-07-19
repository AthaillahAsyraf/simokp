<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            // lengkapi_berkas -> menunggu_verifikasi -> revisi_berkas ->
            // menunggu_instansi -> aktif_kp (lihat Mahasiswa::TAHAP_*)
            $table->string('tahap')->default('lengkapi_berkas')->after('status');
        });

        // Backfill data lama supaya mahasiswa yang sudah berjalan (sudah
        // punya dosen & instansi) tidak ikut ter-gate ulang oleh fitur baru.
        DB::table('mahasiswas')
            ->whereNotNull('dosen_id')
            ->whereNotNull('instansi_id')
            ->update(['tahap' => 'aktif_kp']);
    }

    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropColumn('tahap');
        });
    }
};