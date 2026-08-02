<?php
namespace App\Http\Controllers;
use App\Models\{User, Mahasiswa, ProgressBab};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, Password};

class AuthController extends Controller {

    public function showLogin()   { return view('auth.login'); }
    public function showRegister(){ return view('auth.register'); }

    public function login(Request $request) {
        $request->validate([
            'login'    => 'required|string|max:255',
            'password' => 'required',
        ]);

        $login = trim($request->input('login'));
        $user = User::query()
            ->where('email', $login)
            ->orWhere(function ($query) use ($login) {
                $query->where('role', 'mahasiswa')
                    ->whereHas('mahasiswa', fn ($mahasiswa) => $mahasiswa->where('nim', $login));
            })
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['login' => 'Email/NIM atau password salah.'])->withInput();
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        return redirect($this->redirectByRole(Auth::user()->role));
    }

    public function register(Request $request) {
        $request->validate([
            'name'     => 'required|string|max:255',
            'nim'      => 'required|string|unique:mahasiswas,nim',
            'angkatan' => 'required|string',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);
        $user = User::create([
            'name'=>$request->name,'email'=>$request->email,
            'password'=>Hash::make($request->password),'role'=>'mahasiswa',
        ]);
        $mhs = Mahasiswa::create([
            'user_id'=>$user->id,'nim'=>$request->nim,
            'nama'=>$request->name,'angkatan'=>$request->angkatan,'status'=>'proses',
            'tahap'=>Mahasiswa::TAHAP_LENGKAPI_BERKAS,
        ]);
        foreach (['BAB I','BAB II','BAB III','BAB IV','BAB V'] as $bab) {
            ProgressBab::create(['mahasiswa_id'=>$mhs->id,'bab'=>$bab,'status'=>'belum']);
        }
        Auth::login($user);
        return redirect()->route('mahasiswa.dashboard');
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function showForgotPassword() { return view('auth.forgot-password'); }

    public function sendResetLink(Request $request) {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)
            ->whereIn('role', ['mahasiswa', 'dosen', 'pembimbing_lapangan'])->first();

        // Respons sama untuk semua email agar keberadaan akun tidak dapat ditebak.
        if ($user) Password::sendResetLink(['email' => $user->email]);

        return back()->with('success', 'Jika email terdaftar, tautan untuk mengatur ulang password telah dikirim.');
    }

    public function showResetPassword(Request $request, string $token) {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function resetPassword(Request $request) {
        $request->validate([
            'token' => 'required', 'email' => 'required|email', 'password' => 'required|min:8|confirmed',
        ], [
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $allowedUser = User::where('email', $request->email)
            ->whereIn('role', ['mahasiswa', 'dosen', 'pembimbing_lapangan'])->exists();
        if (! $allowedUser) return back()->withErrors(['email' => 'Tautan reset password tidak valid atau sudah kedaluwarsa.']);

        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => \Illuminate\Support\Str::random(60),
                'wajib_ganti_password' => false,
            ])->save();
        });

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Password berhasil diatur ulang. Silakan masuk menggunakan password baru Anda.');
        }
        return back()->withErrors(['email' => __($status)]);
    }

    // ── Ganti Password ──────────────────────────────────────────────────────
    public function showGantiPassword() {
        // Admin tidak perlu ganti password lewat fitur ini
        abort_if(Auth::user()->role === 'admin', 403);
        return view('auth.ganti-password');
    }

    public function gantiPassword(Request $request) {
        abort_if(Auth::user()->role === 'admin', 403);

        $request->validate([
            'password_lama'     => 'required',
            'password_baru'     => 'required|min:8|confirmed',
        ], [
            'password_baru.min'       => 'Password baru minimal 8 karakter.',
            'password_baru.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password_lama, $user->password)) {
            return back()->withErrors(['password_lama' => 'Password lama tidak sesuai.']);
        }

        if (Hash::check($request->password_baru, $user->password)) {
            return back()->withErrors(['password_baru' => 'Password baru tidak boleh sama dengan password lama.']);
        }

        $user->update([
            'password'             => Hash::make($request->password_baru),
            'wajib_ganti_password' => false,
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }

    public function redirectByRole(string $role): string {
        return match($role) {
            'admin'               => route('admin.dashboard'),
            'dosen'               => route('dosen.dashboard'),
            'pembimbing_lapangan' => route('instansi.dashboard'),
            default               => route('mahasiswa.dashboard'),
        };
    }
}
