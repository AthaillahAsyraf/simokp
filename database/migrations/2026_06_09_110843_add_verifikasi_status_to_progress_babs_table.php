<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::table('progress_babs', function (Blueprint $table) {
            $table->string('verifikasi_status')->nullable()->after('status');
        });

        // Perbaiki data lama — yang sudah 'selesai' langsung set 'approved'
        // supaya data existing tidak semua jadi ○
        DB::table('progress_babs')
            ->where('status', 'selesai')
            ->update(['verifikasi_status' => 'approved']);
    }

    public function down(): void {
        Schema::table('progress_babs', function (Blueprint $table) {
            $table->dropColumn('verifikasi_status');
        });
    }
};