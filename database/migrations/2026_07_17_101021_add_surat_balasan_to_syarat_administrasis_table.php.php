<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('syarat_administrasis', function (Blueprint $table) {
            // Surat balasan dari instansi (surat permohonan dibuat di SAIDATA,
            // di luar SIMOKP — di sini mahasiswa cukup upload surat balasannya).
            $table->string('file_surat_balasan')->nullable()->after('file_transkrip_asli');
            $table->string('file_surat_balasan_asli')->nullable()->after('file_surat_balasan');
            $table->timestamp('surat_balasan_uploaded_at')->nullable()->after('file_surat_balasan_asli');
        });
    }

    public function down(): void
    {
        Schema::table('syarat_administrasis', function (Blueprint $table) {
            $table->dropColumn(['file_surat_balasan', 'file_surat_balasan_asli', 'surat_balasan_uploaded_at']);
        });
    }
};