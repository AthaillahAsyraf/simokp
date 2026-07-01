<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('mahasiswas', function (Blueprint $table) {
            if (!Schema::hasColumn('mahasiswas', 'foto_profil')) {
                $table->string('foto_profil')->nullable()->after('no_hp');
            }
            if (!Schema::hasColumn('mahasiswas', 'bio')) {
                $table->text('bio')->nullable()->after('foto_profil');
            }
            if (!Schema::hasColumn('mahasiswas', 'pembimbing_lapangan_nama')) {
                $table->string('pembimbing_lapangan_nama')->nullable()->after('bio');
            }
            if (!Schema::hasColumn('mahasiswas', 'pembimbing_lapangan_jabatan')) {
                $table->string('pembimbing_lapangan_jabatan')->nullable()->after('pembimbing_lapangan_nama');
            }
            if (!Schema::hasColumn('mahasiswas', 'pembimbing_lapangan_no_hp')) {
                $table->string('pembimbing_lapangan_no_hp')->nullable()->after('pembimbing_lapangan_jabatan');
            }
        });
    }

    public function down(): void {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropColumn(['foto_profil', 'bio', 'pembimbing_lapangan_nama', 'pembimbing_lapangan_jabatan', 'pembimbing_lapangan_no_hp']);
        });
    }
};