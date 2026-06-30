<?php
namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $mahasiswa = Auth::user()->mahasiswa->load(['dosen', 'instansi']);
        return view('mahasiswa.profile.show', compact('mahasiswa'));
    }

    public function edit()
    {
        $mahasiswa = Auth::user()->mahasiswa->load(['dosen', 'instansi']);
        return view('mahasiswa.profile.edit', compact('mahasiswa'));
    }

    public function update(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;

        $request->validate([
            'no_hp'    => 'nullable|string|max:20',
            'bio'      => 'nullable|string|max:500',
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'foto_profil.image'    => 'File harus berupa gambar.',
            'foto_profil.mimes'    => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'foto_profil.max'      => 'Ukuran foto maksimal 2MB.',
            'bio.max'              => 'Bio maksimal 500 karakter.',
        ]);

        $data = $request->only(['no_hp', 'bio']);

        if ($request->hasFile('foto_profil')) {
            // Hapus foto lama jika ada
            if ($mahasiswa->foto_profil) {
                Storage::disk('public')->delete($mahasiswa->foto_profil);
            }
            // Simpan foto baru
            $path = $request->file('foto_profil')->store('foto-profil', 'public');
            $data['foto_profil'] = $path;
        }

        if ($request->boolean('hapus_foto') && $mahasiswa->foto_profil) {
            Storage::disk('public')->delete($mahasiswa->foto_profil);
            $data['foto_profil'] = null;
        }

        $mahasiswa->update($data);

        return redirect()->route('mahasiswa.profile.show')
            ->with('success', 'Profil berhasil diperbarui!');
    }
}