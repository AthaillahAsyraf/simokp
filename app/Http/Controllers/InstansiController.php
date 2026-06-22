<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instansi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InstansiController extends Controller
{
    public function index(Request $request)
    {
        $query = Instansi::with(['user', 'mahasiswas']);

        // ✅ FIX: search ada di view tapi tidak dihandle di controller
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where('nama', 'like', "%$q%");
        }

        $instansis = $query->latest()->get();
        return view('admin.instansi.index', compact('instansis'));
    }

    public function store(Request $request)
    {
        // ✅ FIX: validateWithBag agar error tampil di modal yang benar
        $request->validateWithBag('tambah', [
            'nama'          => ['required', 'string', 'max:255'],
            'bidang'        => ['nullable', 'string', 'max:255'],
            'alamat'        => ['nullable', 'string'],
            'kontak_person' => ['nullable', 'string', 'max:255'],
            'no_hp'         => ['nullable', 'regex:/^[0-9]+$/'],
            'email'         => ['required', 'email', 'unique:users,email'],
            'password'      => ['required', 'string', 'min:8'],
        ], [
            'nama.required'     => 'Nama instansi wajib diisi.',
            'no_hp.regex'       => 'No. HP harus berupa angka.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah digunakan oleh akun lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
        ]);

        // ✅ FIX: hapus Hash::make() — User model sudah punya cast 'password'=>'hashed'
        // ✅ FIX: DB::transaction agar User & Instansi tersimpan bersama atau tidak sama sekali
        // ✅ FIX: try-catch agar error tidak diam-diam gagal
        try {
            DB::transaction(function () use ($request) {
                $user = User::create([
                    'name'     => $request->nama,
                    'email'    => $request->email,
                    'password' => $request->password,   // cast 'hashed' yang handle
                    'role'     => 'instansi',
                ]);

                Instansi::create([
                    'user_id'       => $user->id,
                    'nama'          => $request->nama,
                    'bidang'        => $request->bidang,
                    'alamat'        => $request->alamat,
                    'kontak_person' => $request->kontak_person,
                    'no_hp'         => $request->no_hp,
                ]);
            });
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('db_error', 'Gagal menyimpan: ' . $e->getMessage());
        }

        return redirect()
            ->route('admin.instansi.index')
            ->with('success', 'Instansi berhasil ditambahkan.');
    }

    public function show(Instansi $instansi)
    {
        $instansi->load(['user', 'mahasiswas']);
        return view('admin.instansi.show', compact('instansi'));
    }

    public function update(Request $request, Instansi $instansi)
    {
        // ✅ FIX: validateWithBag('edit') + try-catch + flash edit_id untuk auto-buka modal
        try {
            $request->validateWithBag('edit', [
                'nama'          => ['required', 'string', 'max:255'],
                'bidang'        => ['nullable', 'string', 'max:255'],
                'alamat'        => ['nullable', 'string'],
                'kontak_person' => ['nullable', 'string', 'max:255'],
                'no_hp'         => ['nullable', 'regex:/^[0-9]+$/'],
            ], [
                'nama.required' => 'Nama instansi wajib diisi.',
                'no_hp.regex'   => 'No. HP harus berupa angka.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->validator, 'edit')
                ->withInput()
                ->with('edit_id', $instansi->id);
        }

        $instansi->update($request->only([
            'nama', 'bidang', 'alamat', 'kontak_person', 'no_hp',
        ]));

        return back()->with('success', 'Data instansi berhasil diperbarui.');
    }

    public function destroy(Instansi $instansi)
    {
        // ✅ FIX: null check sebelum hapus user (asli langsung ->delete() tanpa check)
        DB::transaction(function () use ($instansi) {
            $userId = $instansi->user_id;
            $instansi->delete();
            if ($userId) {
                User::find($userId)?->delete();
            }
        });

        return back()->with('success', 'Instansi berhasil dihapus.');
    }
}