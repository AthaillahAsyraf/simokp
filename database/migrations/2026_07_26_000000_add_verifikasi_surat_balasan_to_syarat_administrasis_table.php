<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('syarat_administrasis', function (Blueprint $table) {
            $table->string('surat_balasan_status')->nullable()->after('surat_balasan_uploaded_at');
            $table->text('surat_balasan_catatan')->nullable()->after('surat_balasan_status');
            $table->timestamp('surat_balasan_diverifikasi_at')->nullable()->after('surat_balasan_catatan');
        });
    }
    public function down(): void {
        Schema::table('syarat_administrasis', function (Blueprint $table) {
            $table->dropColumn(['surat_balasan_status', 'surat_balasan_catatan', 'surat_balasan_diverifikasi_at']);
        });
    }
};
