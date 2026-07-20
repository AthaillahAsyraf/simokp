<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Konsep "radius toleransi absen" dihapus dari sistem — absen GPS mahasiswa
 * kini selalu dicatat sebagai "valid" (jarak & koordinat tetap disimpan di
 * tabel absensis untuk transparansi/audit, tapi tidak lagi menggagalkan
 * absen apapun jaraknya).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('instansis', 'radius_absen')) {
            Schema::table('instansis', function (Blueprint $table) {
                $table->dropColumn('radius_absen');
            });
        }
    }

    public function down(): void
    {
        Schema::table('instansis', function (Blueprint $table) {
            $table->integer('radius_absen')->default(100)->after('longitude');
        });
    }
};