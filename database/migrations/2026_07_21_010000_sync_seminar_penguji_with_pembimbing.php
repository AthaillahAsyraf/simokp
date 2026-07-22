<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::table('seminars as s')->join('mahasiswas as m', 'm.id', '=', 's.mahasiswa_id')->update(['s.dosen_penguji_id' => DB::raw('m.dosen_id')]);
    }
    public function down(): void { }
};
