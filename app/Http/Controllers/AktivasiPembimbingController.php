<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AktivasiPembimbingController extends Controller
{
    public function show(string $token)
    {
        $user = $this->userDariToken($token);
        return view('auth.aktivasi-pembimbing', compact('token', 'user'));
    }

    public function store(Request $request, string $token)
    {
        $user = $this->userDariToken($token);
        if (! $user) return redirect()->route('login')->withErrors(['email' => 'Tautan aktivasi tidak valid atau sudah kedaluwarsa.']);

        $request->validate(['password' => 'required|min:8|confirmed'], [
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);
        $user->update(['password' => Hash::make($request->password), 'activation_token' => null, 'activation_expires_at' => null, 'wajib_ganti_password' => false]);
        return redirect()->route('login')->with('success', 'Akun berhasil diaktifkan. Silakan masuk menggunakan email dan password baru Anda.');
    }

    private function userDariToken(string $token): ?User
    {
        return User::where('role', 'pembimbing_lapangan')->where('activation_token', hash('sha256', $token))->where('activation_expires_at', '>', now())->first();
    }
}
