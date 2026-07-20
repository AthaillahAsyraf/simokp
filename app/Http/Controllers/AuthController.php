<?php
namespace App\Http\Controllers;
use App\Models\{User, Mahasiswa, ProgressBab};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash};

class AuthController extends Controller {

    public function showLogin()   { return view('auth.login'); }
    public function showRegister(){ return view('auth.register'); }

    public function login(Request $request) {
        $request->validate(['email'=>'required|email','password'=>'required']);
        if (!Auth::attempt($request->only('email','password'), $request->boolean('remember'))) {
            return back()->withErrors(['email'=>'Email atau password salah.'])->withInput();
        }
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