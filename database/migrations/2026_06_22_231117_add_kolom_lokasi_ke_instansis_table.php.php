<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan titik koordinat instansi + radius toleransi absen.
     * Tanpa ->after() supaya aman walau struktur kolom instansis berbeda.
     */
    public function up(): void
    {
        Schema::table('instansis', function (Blueprint $table) {
            if (!Schema::hasColumn('instansis', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('instansis', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('instansis', 'radius_absen')) {
                $table->unsignedInteger('radius_absen')->default(100)
                    ->comment('Radius toleransi absen dalam meter');
            }
        });
    }

    public function down(): void
    {
        Schema::table('instansis', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'radius_absen']);
        });
    }
};