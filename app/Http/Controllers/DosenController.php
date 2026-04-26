<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DosenController extends Controller
{
    public function index()
    {
        $dosens = Dosen::with(['user', 'mahasiswas'])->latest()->get();
        return view('admin.dosen.index', compact('dosens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip'    => 'required|unique:dosens,nip',
            'nama'   => 'required',
            'email'  => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name'     => $request->nama,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'dosen',
        ]);

        Dosen::create([
            'user_id' => $user->id,
            'nip'     => $request->nip,
            'nama'    => $request->nama,
            'bidang'  => $request->bidang,
            'no_hp'   => $request->no_hp,
        ]);

        return back()->with('success', 'Dosen berhasil ditambahkan.');
    }

    public function update(Request $request, Dosen $dosen)
    {
        $request->validate(['nama' => 'required']);
        $dosen->update($request->only(['nip', 'nama', 'bidang', 'no_hp']));
        return back()->with('success', 'Data dosen diperbarui.');
    }

    public function destroy(Dosen $dosen)
    {
        $dosen->user->delete();
        return back()->with('success', 'Dosen berhasil dihapus.');
    }
}