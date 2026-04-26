<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instansi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class InstansiController extends Controller
{
    public function index()
    {
        $instansis = Instansi::with(['user', 'mahasiswas'])->latest()->get();
        return view('admin.instansi.index', compact('instansis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'  => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name'     => $request->nama,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'instansi',
        ]);

        Instansi::create([
            'user_id'        => $user->id,
            'nama'           => $request->nama,
            'bidang'         => $request->bidang,
            'alamat'         => $request->alamat,
            'kontak_person'  => $request->kontak_person,
            'no_hp'          => $request->no_hp,
        ]);

        return back()->with('success', 'Instansi berhasil ditambahkan.');
    }

    public function update(Request $request, Instansi $instansi)
    {
        $request->validate(['nama' => 'required']);
        $instansi->update($request->only(['nama', 'bidang', 'alamat', 'kontak_person', 'no_hp']));
        return back()->with('success', 'Data instansi diperbarui.');
    }

    public function destroy(Instansi $instansi)
    {
        $instansi->user->delete();
        return back()->with('success', 'Instansi berhasil dihapus.');
    }
}