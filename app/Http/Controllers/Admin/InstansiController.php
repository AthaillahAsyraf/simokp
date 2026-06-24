<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Instansi, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class InstansiController extends Controller
{
    public function index()
    {
        $instansis = Instansi::with(['user', 'mahasiswas'])->latest()->get();
        return view('admin.instansi.index', compact('instansis'));
    }

    public function show(Instansi $instansi)
    {
        $instansi->load(['mahasiswas.dosen', 'mahasiswas.progressBabs']);
        return view('admin.instansi.show', compact('instansi'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama'          => 'required|string|max:255',
            'bidang'        => 'nullable|string|max:255',
            'alamat'        => 'nullable|string',
            'kontak_person' => 'nullable|string|max:255',
            'no_hp'         => 'nullable|numeric',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|min:8',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
            'radius_absen'  => 'nullable|integer|min:10|max:5000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'tambah')->withInput();
        }

        try {
            DB::transaction(function () use ($request) {
                $user = User::create([
                    'name'     => $request->nama,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'role'     => 'instansi',
                ]);

                Instansi::create([
                    'user_id'       => $user->id,
                    'nama'          => $request->nama,
                    'bidang'        => $request->bidang,
                    'alamat'        => $request->alamat,
                    'kontak_person' => $request->kontak_person,
                    'no_hp'         => $request->no_hp,
                    'latitude'      => $request->latitude,
                    'longitude'     => $request->longitude,
                    'radius_absen'  => $request->radius_absen ?: 100,
                ]);
            });
        } catch (\Throwable $e) {
            return back()->with('db_error', 'Gagal menyimpan instansi: '.$e->getMessage())->withInput();
        }

        return redirect()->route('admin.instansi.index')->with('success', 'Instansi berhasil ditambahkan.');
    }

    public function update(Request $request, Instansi $instansi)
    {
        $validator = Validator::make($request->all(), [
            'nama'          => 'required|string|max:255',
            'bidang'        => 'nullable|string|max:255',
            'alamat'        => 'nullable|string',
            'kontak_person' => 'nullable|string|max:255',
            'no_hp'         => 'nullable|numeric',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
            'radius_absen'  => 'nullable|integer|min:10|max:5000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'edit')->withInput()->with('edit_id', $instansi->id);
        }

        try {
            $instansi->update($request->only([
                'nama', 'bidang', 'alamat', 'kontak_person', 'no_hp',
                'latitude', 'longitude', 'radius_absen',
            ]));
        } catch (\Throwable $e) {
            return back()->with('db_error', 'Gagal memperbarui instansi: '.$e->getMessage())
                ->withInput()->with('edit_id', $instansi->id);
        }

        return redirect()->route('admin.instansi.index')->with('success', 'Data instansi diperbarui.');
    }

    public function destroy(Instansi $instansi)
    {
        try {
            DB::transaction(function () use ($instansi) {
                $instansi->user?->delete(); // null-safe: instansi mungkin sudah tidak punya user
                $instansi->delete();
            });
        } catch (\Throwable $e) {
            return back()->with('db_error', 'Gagal menghapus instansi: '.$e->getMessage());
        }

        return redirect()->route('admin.instansi.index')->with('success', 'Instansi berhasil dihapus.');
    }
}