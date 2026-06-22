<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Instansi;
use App\Models\ProgressBab;
use App\Models\Nilai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;   // ← BUG 3: ini hilang di versi asli

class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = Mahasiswa::with(['dosen', 'instansi', 'progressBabs']);

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($q2) => $q2->where('nama', 'like', "%$q%")
                                        ->orWhere('nim', 'like', "%$q%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ✅ FIX BUG 1: filter dosen_id & instansi_id ada di VIEW tapi tidak di controller
        //    → dropdown filter tidak berfungsi sama sekali
        if ($request->filled('dosen_id')) {
            $query->where('dosen_id', $request->dosen_id);
        }
        if ($request->filled('instansi_id')) {
            $query->where('instansi_id', $request->instansi_id);
        }

        $mahasiswas = $query->latest()->paginate(10)->withQueryString();
        $dosens     = Dosen::all();
        $instansis  = Instansi::all();

        return view('admin.mahasiswa.index', compact('mahasiswas', 'dosens', 'instansis'));
    }

    public function store(Request $request)
    {
        // ✅ FIX BUG 2: validasi terlalu lemah → error tidak kelihatan karena modal menutup
        //    Inilah penyebab utama "tidak nambah ke halaman":
        //    data gagal disimpan (validasi gagal) tapi user tidak tahu karena modal sudah tutup
        $request->validate([
            'nim'           => ['required', 'regex:/^[0-9]+$/', 'unique:mahasiswas,nim'],
            'nama'          => ['required', 'string', 'max:255'],
            'angkatan'      => ['required', 'digits:4'],
            'no_hp'         => ['nullable', 'regex:/^[0-9]+$/'],
            'email'         => ['required', 'email', 'unique:users,email'],
            'password'      => ['required', 'string', 'min:8'],     // asli min:6, harusnya min:8
            'dosen_id'      => ['nullable', 'exists:dosens,id'],
            'instansi_id'   => ['nullable', 'exists:instansis,id'],
            'tanggal_mulai' => ['nullable', 'date'],
        ], [
            'nim.required'      => 'NIM wajib diisi.',
            'nim.regex'         => 'NIM harus berupa angka.',
            'nim.unique'        => 'NIM sudah terdaftar.',
            'nama.required'     => 'Nama wajib diisi.',
            'angkatan.required' => 'Angkatan wajib diisi.',
            'angkatan.digits'   => 'Angkatan harus tepat 4 angka (contoh: 2021).',
            'no_hp.regex'       => 'No. HP harus berupa angka.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah digunakan oleh akun lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
        ]);

        // ✅ FIX BUG 3: tidak ada DB::transaction → jika ProgressBab/Nilai gagal dibuat,
        //    User & Mahasiswa sudah terlanjur tersimpan (data korup/inkonsisten)
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->nama,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'mahasiswa',
            ]);

            $mahasiswa = Mahasiswa::create([
                'user_id'       => $user->id,
                'nim'           => $request->nim,
                'nama'          => $request->nama,
                'angkatan'      => $request->angkatan,
                'no_hp'         => $request->no_hp,
                'dosen_id'      => $request->dosen_id,
                'instansi_id'   => $request->instansi_id,
                'tanggal_mulai' => $request->tanggal_mulai,
                'status'        => 'proses',
            ]);

            $babs = ['BAB I', 'BAB II', 'BAB III', 'BAB IV', 'BAB V', 'LAPORAN LENGKAP'];
            foreach ($babs as $bab) {
                ProgressBab::create([
                    'mahasiswa_id' => $mahasiswa->id,
                    'bab'          => $bab,
                    'status'       => 'belum',
                ]);
            }

            Nilai::create(['mahasiswa_id' => $mahasiswa->id]);
        });

        // ✅ FIX BUG 4: asli pakai back() yang bisa bermasalah jika ada query string aktif
        //    Pakai redirect ke route eksplisit agar selalu kembali ke halaman pertama list
        return redirect()
            ->route('admin.mahasiswa.index')
            ->with('success', 'Mahasiswa berhasil ditambahkan.');
    }

    public function show(Mahasiswa $mahasiswa)
    {
        $mahasiswa->load(['dosen', 'instansi', 'progressBabs', 'logbooks', 'seminar', 'surats', 'nilai']);
        return view('admin.mahasiswa.show', compact('mahasiswa'));
    }

    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $request->validate([
            'nama'     => ['required', 'string', 'max:255'],
            'angkatan' => ['required', 'digits:4'],
            'status'   => ['required', 'in:proses,seminar,selesai'],
        ], [
            'nama.required'     => 'Nama wajib diisi.',
            'angkatan.required' => 'Angkatan wajib diisi.',
            'angkatan.digits'   => 'Angkatan harus tepat 4 angka.',
            'status.in'         => 'Status tidak valid.',
        ]);

        $mahasiswa->update($request->only([
            'nama', 'angkatan', 'no_hp', 'dosen_id',
            'instansi_id', 'tanggal_mulai', 'tanggal_selesai', 'status',
        ]));

        return back()->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    public function destroy(Mahasiswa $mahasiswa)
    {
        // ✅ FIX BUG 5: asli $mahasiswa->user->delete() langsung tanpa null check
        //    → jika kolom user_id null / user sudah terhapus, akan crash dengan error:
        //    "Call to a member function delete() on null"
        DB::transaction(function () use ($mahasiswa) {
            $userId = $mahasiswa->user_id;

            $mahasiswa->delete();            // hapus mahasiswa dulu (lepas FK)

            if ($userId) {
                User::find($userId)?->delete(); // lalu hapus user-nya
            }
        });

        return back()->with('success', 'Mahasiswa berhasil dihapus.');
    }
}