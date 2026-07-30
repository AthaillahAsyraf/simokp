<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('form_kesediaan_pembimbings')
            ->whereIn('status', ['diteruskan', 'disetujui'])
            ->update([
                'status' => 'diterbitkan',
                'diteruskan_at' => null,
                'disetujui_at' => null,
                'updated_at' => now(),
            ]);

        DB::table('mahasiswas')
            ->where('tahap', 'menunggu_kesediaan_pembimbing')
            ->update([
                'tahap' => 'aktif_kp',
                'tanggal_mulai' => DB::raw('COALESCE(tanggal_mulai, CURDATE())'),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Tahap persetujuan dosen sengaja tidak dipulihkan karena alur ini telah dihapus.
    }
};
