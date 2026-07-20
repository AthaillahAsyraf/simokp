<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Memaksa user dengan flag wajib_ganti_password=true untuk mengganti
 * password terlebih dahulu sebelum bisa mengakses menu lain. Dipakai untuk
 * akun Dosen & Pembimbing Lapangan yang dibuat oleh pihak lain (admin/
 * mahasiswa) dan belum pernah menentukan password sendiri.
 */
class ForceGantiPassword
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user && $user->wajib_ganti_password) {
            // Izinkan tetap mengakses halaman ganti password itu sendiri & logout,
            // supaya tidak terjadi redirect loop.
            if (! $request->routeIs('ganti-password', 'ganti-password.post', 'logout')) {
                return redirect()->route('ganti-password')
                    ->with('force_ganti_password', 'Demi keamanan, silakan ganti password default Anda terlebih dahulu sebelum melanjutkan.');
            }
        }

        return $next($request);
    }
}