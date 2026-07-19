<?php

namespace App\Http\Middleware;

use App\Models\Mahasiswa;
use Closure;
use Illuminate\Http\Request;

class CekTahapKp
{
    /**
     * Blokir akses fitur mahasiswa (absensi, laporan BAB, seminar, nilai, dll)
     * kalau tahap KP mahasiswa belum sampai $tahapMinimal.
     *
     * Contoh pakai di routes: ->middleware('tahap:aktif_kp')
     */
    public function handle(Request $request, Closure $next, string $tahapMinimal)
    {
        $mahasiswa = $request->user()?->mahasiswa;

        if (!$mahasiswa || !$mahasiswa->sudahMencapaiTahap($tahapMinimal)) {
            return redirect()
                ->route('mahasiswa.persyaratan.index')
                ->with('error', 'Selesaikan tahap sebelumnya dulu (lihat status di halaman Persyaratan KP) sebelum bisa mengakses fitur ini.');
        }

        return $next($request);
    }
}