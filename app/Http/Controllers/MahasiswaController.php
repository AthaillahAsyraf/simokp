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

class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = Mahasiswa::with(['dosen', 'instansi', 'progressBabs']);

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($q2) => $q2->where('nama', 'like', "%$q%")->orWhere('nim', 'like', "%$q%"));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $mahasiswas = $query->latest()->paginate(10)->withQueryString();
        $dosens     = Dosen::all();
        $instansis  = Instansi::all();

        return view('admin.mahasiswa.index', compact('mahasiswas', 'dosens', 'instansis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nim'      => 'required|unique:mahasiswas,nim',
            'nama'     => 'required',
            'angkatan' => 'required',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name'     => $request->nama,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'mahasiswa',
        ]);

        $mahasiswa = Mahasiswa::create([
            'user_id'        => $user->id,
            'nim'            => $request->nim,
            'nama'           => $request->nama,
            'angkatan'       => $request->angkatan,
            'no_hp'          => $request->no_hp,
            'dosen_id'       => $request->dosen_id,
            'instansi_id'    => $request->instansi_id,
            'tanggal_mulai'  => $request->tanggal_mulai,
            'status'         => 'proses',
        ]);

        $babs = ['BAB I', 'BAB II', 'BAB III', 'BAB IV', 'BAB V', 'LAPORAN LENGKAP'];
        foreach ($babs as $bab) {
            ProgressBab::create(['mahasiswa_id' => $mahasiswa->id, 'bab' => $bab, 'status' => 'belum']);
        }
        Nilai::create(['mahasiswa_id' => $mahasiswa->id]);

        return back()->with('success', 'Mahasiswa berhasil ditambahkan.');
    }

    public function show(Mahasiswa $mahasiswa)
    {
        $mahasiswa->load(['dosen', 'instansi', 'progressBabs', 'logbooks', 'seminar', 'surats', 'nilai']);
        return view('admin.mahasiswa.show', compact('mahasiswa'));
    }

    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $request->validate([
            'nama'     => 'required',
            'angkatan' => 'required',
            'status'   => 'required|in:proses,seminar,selesai',
        ]);

        $mahasiswa->update($request->only([
            'nama', 'angkatan', 'no_hp', 'dosen_id',
            'instansi_id', 'tanggal_mulai', 'tanggal_selesai', 'status'
        ]));

        return back()->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    public function destroy(Mahasiswa $mahasiswa)
    {
        $mahasiswa->user->delete(); // cascade ke mahasiswa
        return back()->with('success', 'Mahasiswa berhasil dihapus.');
    }
}