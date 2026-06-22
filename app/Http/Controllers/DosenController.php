<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DosenController extends Controller
{
    public function index(Request $request)
    {
        $query = Dosen::with(['user', 'mahasiswas']);

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($q2) => $q2->where('nama', 'like', "%$q%")
                                        ->orWhere('nip',  'like', "%$q%"));
        }

        $dosens = $query->latest()->get();

        return view('admin.dosen.index', compact('dosens'));
    }

    public function store(Request $request)
    {
        $request->validateWithBag('tambah', [
            'nip'      => ['required', 'regex:/^[0-9]+$/', 'unique:dosens,nip'],
            'nama'     => ['required', 'string', 'max:255'],
            'no_hp'    => ['nullable', 'regex:/^[0-9]+$/'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'nip.required'      => 'NIP wajib diisi.',
            'nip.regex'         => 'NIP harus berupa angka.',
            'nip.unique'        => 'NIP sudah terdaftar.',
            'nama.required'     => 'Nama wajib diisi.',
            'no_hp.regex'       => 'No. HP harus berupa angka.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah digunakan oleh akun lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
        ]);

        // ✅ FIX BUG 1: User model sudah punya cast 'password' => 'hashed'
        //    → JANGAN pakai Hash::make() lagi, kalau tidak password di-hash 2x
        //    → Di beberapa kondisi ini bisa bikin User::create() gagal diam-diam
        try {
            DB::transaction(function () use ($request) {
                $user = User::create([
                    'name'     => $request->nama,
                    'email'    => $request->email,
                    'password' => $request->password,   // ← bukan Hash::make()
                    'role'     => 'dosen',
                ]);

                Dosen::create([
                    'user_id' => $user->id,
                    'nip'     => $request->nip,
                    'nama'    => $request->nama,
                    'no_hp'   => $request->no_hp,
                ]);
            });
        } catch (\Exception $e) {
            // ✅ FIX BUG 2: tangkap error DB agar tidak diam-diam gagal
            //    Pesan error ditampilkan di halaman agar bisa diketahui masalahnya
            return back()
                ->withInput()
                ->with('db_error', 'Gagal menyimpan: ' . $e->getMessage());
        }

        return redirect()
            ->route('admin.dosen.index')
            ->with('success', 'Dosen berhasil ditambahkan.');
    }

    public function show(Dosen $dosen)
    {
        $dosen->load(['user', 'mahasiswas.instansi', 'mahasiswas.progressBabs']);
        return view('admin.dosen.show', compact('dosen'));
    }

    public function update(Request $request, Dosen $dosen)
    {
        try {
            $request->validateWithBag('edit', [
                'nip'   => ['required', 'regex:/^[0-9]+$/', Rule::unique('dosens', 'nip')->ignore($dosen->id)],
                'nama'  => ['required', 'string', 'max:255'],
                'no_hp' => ['nullable', 'regex:/^[0-9]+$/'],
            ], [
                'nip.required' => 'NIP wajib diisi.',
                'nip.regex'    => 'NIP harus berupa angka.',
                'nip.unique'   => 'NIP sudah digunakan dosen lain.',
                'nama.required'=> 'Nama wajib diisi.',
                'no_hp.regex'  => 'No. HP harus berupa angka.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->validator, 'edit')
                ->withInput()
                ->with('edit_id', $dosen->id);
        }

        $dosen->update($request->only(['nip', 'nama', 'no_hp']));

        return back()->with('success', 'Data dosen berhasil diperbarui.');
    }

    public function destroy(Dosen $dosen)
    {
        DB::transaction(function () use ($dosen) {
            $userId = $dosen->user_id;
            $dosen->delete();
            if ($userId) {
                User::find($userId)?->delete();
            }
        });

        return back()->with('success', 'Dosen berhasil dihapus.');
    }
}