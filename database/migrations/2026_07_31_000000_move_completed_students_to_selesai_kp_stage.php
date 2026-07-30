<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('mahasiswas')
            ->where('status', 'selesai')
            ->update(['tahap' => 'selesai_kp']);
    }

    public function down(): void
    {
        DB::table('mahasiswas')
            ->where('tahap', 'selesai_kp')
            ->update(['tahap' => 'aktif_kp']);
    }
};
