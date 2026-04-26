<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\ProgressBab;
use App\Models\Nilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

public function login(Request $request)
{
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
        return back()->withErrors(['email' => 'Email atau password salah.'])->withInput();
    }

    $request->session()->regenerate();

    $role = Auth::user()->role;

    return redirect()->route(match ($role) {
        'admin' => 'admin.dashboard',
        'dosen' => 'dosen.dashboard',
        'instansi' => 'instansi.dashboard',
        'mahasiswa' => 'mahasiswa.dashboard',
        default => 'login',
    });
}

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'nim'      => 'required|string|unique:mahasiswas,nim',
            'angkatan' => 'required|string',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'mahasiswa',
        ]);

        $mahasiswa = Mahasiswa::create([
            'user_id'  => $user->id,
            'nim'      => $request->nim,
            'nama'     => $request->name,
            'angkatan' => $request->angkatan,
            'status'   => 'proses',
        ]);

        // Buat progress BAB default
        $babs = ['BAB I', 'BAB II', 'BAB III', 'BAB IV', 'BAB V', 'LAPORAN LENGKAP'];
        foreach ($babs as $bab) {
            ProgressBab::create(['mahasiswa_id' => $mahasiswa->id, 'bab' => $bab, 'status' => 'belum']);
        }

        // Buat record nilai kosong
        Nilai::create(['mahasiswa_id' => $mahasiswa->id]);

        Auth::login($user);
        return redirect()->route('mahasiswa.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function redirectByRole(string $role): string
    {
        return match ($role) {
            'admin'     => route('admin.dashboard'),
            'dosen'     => route('dosen.dashboard'),
            'instansi'  => route('instansi.dashboard'),
            'mahasiswa' => route('mahasiswa.dashboard'),
            default     => '/',
        };
    }
}