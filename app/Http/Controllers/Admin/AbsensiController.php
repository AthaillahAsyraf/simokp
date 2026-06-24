<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Instansi;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $query = Absensi::with(['mahasiswa.dosen', 'mahasiswa.instansi'])
            ->orderByDesc('tanggal')
            ->orderByDesc('updated_at');

        // Filter berdasarkan mahasiswa
        if ($request->filled('mahasiswa_id')) {
            $query->where('mahasiswa_id', $request->mahasiswa_id);
        }

        // Filter berdasarkan dosen pembimbing
        if ($request->filled('dosen_id')) {
            $query->whereHas('mahasiswa', fn($q) => $q->where('dosen_id', $request->dosen_id));
        }

        // Filter berdasarkan instansi
        if ($request->filled('instansi_id')) {
            $query->whereHas('mahasiswa', fn($q) => $q->where('instansi_id', $request->instansi_id));
        }

        // Filter berdasarkan tanggal
        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal', '<=', $request->tanggal_sampai);
        }

        // Filter berdasarkan status (diluar_radius)
        if ($request->filled('status')) {
            if ($request->status === 'diluar_radius') {
                $query->where(fn($q) =>
                    $q->where('status_masuk', 'diluar_radius')
                      ->orWhere('status_keluar', 'diluar_radius')
                );
            } elseif ($request->status === 'valid') {
                $query->where('status_masuk', 'valid');
            } elseif ($request->status === 'belum_keluar') {
                $query->whereNotNull('jam_masuk')->whereNull('jam_keluar');
            }
        }

        $absensis  = $query->paginate(20)->withQueryString();
        $mahasiswas = Mahasiswa::orderBy('nama')->get();
        $dosens     = Dosen::orderBy('nama')->get();
        $instansis  = Instansi::orderBy('nama')->get();

        return view('admin.absensi.index', compact(
            'absensis', 'mahasiswas', 'dosens', 'instansis'
        ));
    }

    public function show(Mahasiswa $mahasiswa, Request $request)
    {
        $mahasiswa->load(['dosen', 'instansi']);

        $query = Absensi::where('mahasiswa_id', $mahasiswa->id)
            ->orderByDesc('tanggal');

        if ($request->filled('bulan')) {
            $query->whereYear('tanggal', substr($request->bulan, 0, 4))
                  ->whereMonth('tanggal', substr($request->bulan, 5, 2));
        }

        $absensis = $query->paginate(30)->withQueryString();

        // Statistik ringkas
        $stats = [
            'total_hadir'       => Absensi::where('mahasiswa_id', $mahasiswa->id)->whereNotNull('jam_masuk')->count(),
            'total_lengkap'     => Absensi::where('mahasiswa_id', $mahasiswa->id)->whereNotNull('jam_masuk')->whereNotNull('jam_keluar')->count(),
            'diluar_radius'     => Absensi::where('mahasiswa_id', $mahasiswa->id)
                                          ->where(fn($q) => $q->where('status_masuk','diluar_radius')->orWhere('status_keluar','diluar_radius'))
                                          ->count(),
        ];

        return view('admin.absensi.show', compact('mahasiswa', 'absensis', 'stats'));
    }

    /**
     * Admin bisa memberi catatan / koreksi pada absensi
     */
    public function updateCatatan(Request $request, Absensi $absensi)
    {
        $request->validate(['catatan_dosen' => 'nullable|string|max:1000']);
        $absensi->update(['catatan_dosen' => $request->catatan_dosen]);
        return back()->with('success', 'Catatan absensi berhasil diperbarui.');
    }
}