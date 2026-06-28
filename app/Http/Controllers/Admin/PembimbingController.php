<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Dosen, Mahasiswa};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembimbingController extends Controller
{
    /**
     * Satu halaman, dua tab:
     * - Dosen Pembimbing  (dosen akademik dari kampus)
     * - Pembimbing Lapangan (penanggung jawab mahasiswa di sisi instansi)
     */
    public function index(Request $request)
    {
        $dosenQuery = Dosen::with(['user', 'mahasiswas']);
        if ($request->filled('search_dosen')) {
            $q = $request->search_dosen;
            $dosenQuery->where(fn ($q2) => $q2->where('nama', 'like', "%$q%")->orWhere('nip', 'like', "%$q%"));
        }
        $dosens = $dosenQuery->latest()->get();

        $lapanganQuery = Mahasiswa::with('instansi')->whereNotNull('instansi_id');
        if ($request->filled('search_lapangan')) {
            $q = $request->search_lapangan;
            $lapanganQuery->where(fn ($q2) => $q2->where('nama', 'like', "%$q%")->orWhere('nim', 'like', "%$q%"));
        }
        $mahasiswas = $lapanganQuery->latest()->get();

        $tab = $request->get('tab', 'dosen');

        return view('admin.pembimbing.index', compact('dosens', 'mahasiswas', 'tab'));
    }

    /**
     * Update data Pembimbing Lapangan (nama, jabatan, no_hp) untuk satu mahasiswa.
     * Dipisah dari Admin\MahasiswaController@update biar tidak nyenggol form CRUD
     * mahasiswa yang sudah jalan, dan error bag-nya independen.
     */
    public function updateLapangan(Request $request, Mahasiswa $mahasiswa)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'pembimbing_lapangan_nama'    => 'nullable|string|max:255',
            'pembimbing_lapangan_jabatan' => 'nullable|string|max:255',
            'pembimbing_lapangan_no_hp'   => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'lapangan')->withInput()->with('edit_lapangan_id', $mahasiswa->id);
        }

        try {
            $mahasiswa->update($request->only([
                'pembimbing_lapangan_nama',
                'pembimbing_lapangan_jabatan',
                'pembimbing_lapangan_no_hp',
            ]));
        } catch (\Throwable $e) {
            return back()->with('db_error', 'Gagal menyimpan data pembimbing lapangan: '.$e->getMessage())
                ->withInput()->with('edit_lapangan_id', $mahasiswa->id);
        }

        return redirect()->route('admin.pembimbing.index', ['tab' => 'lapangan'])
            ->with('success', "Pembimbing lapangan untuk {$mahasiswa->nama} berhasil diperbarui.");
    }
}