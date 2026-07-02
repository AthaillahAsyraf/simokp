<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            // Diisi saat absen MASUK — rencana kegiatan hari ini
            $table->text('rencana')->nullable()->after('catatan_dosen');

            // Diisi saat absen PULANG — realisasi kegiatan yang sudah dikerjakan
            $table->text('realisasi')->nullable()->after('rencana');
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropColumn(['rencana', 'realisasi']);
        });
    }
};
