<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $dosen      = Auth::user()->dosen;
        $mahasiswas = $dosen->mahasiswas()->orderBy('nama')->get();

        $query = Absensi::with(['mahasiswa'])
            ->whereHas('mahasiswa', fn($q) => $q->where('dosen_id', $dosen->id))
            ->orderByDesc('tanggal')
            ->orderByDesc('updated_at');

        // Filter mahasiswa tertentu
        if ($request->filled('mahasiswa_id')) {
            // Pastikan mahasiswa ini adalah bimbingan dosen yang sedang login
            $allowed = $mahasiswas->pluck('id')->contains($request->mahasiswa_id);
            if ($allowed) {
                $query->where('mahasiswa_id', $request->mahasiswa_id);
            }
        }

        // Filter tanggal
        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal', '<=', $request->tanggal_sampai);
        }

        // Filter status
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

        $absensis = $query->paginate(20)->withQueryString();

        return view('dosen.absensi.index', compact('dosen', 'mahasiswas', 'absensis'));
    }

    public function show(Mahasiswa $mahasiswa, Request $request)
    {
        $dosen = Auth::user()->dosen;

        // Pastikan mahasiswa ini adalah bimbingan dosen yang login
        abort_if($mahasiswa->dosen_id !== $dosen->id, 403, 'Mahasiswa ini bukan bimbingan Anda.');

        $mahasiswa->load(['instansi']);

        $query = Absensi::where('mahasiswa_id', $mahasiswa->id)->orderByDesc('tanggal');

        if ($request->filled('bulan')) {
            $query->whereYear('tanggal', substr($request->bulan, 0, 4))
                  ->whereMonth('tanggal', substr($request->bulan, 5, 2));
        }

        $absensis = $query->paginate(30)->withQueryString();

        $stats = [
            'total_hadir'   => Absensi::where('mahasiswa_id', $mahasiswa->id)->whereNotNull('jam_masuk')->count(),
            'total_lengkap' => Absensi::where('mahasiswa_id', $mahasiswa->id)->whereNotNull('jam_masuk')->whereNotNull('jam_keluar')->count(),
            'diluar_radius' => Absensi::where('mahasiswa_id', $mahasiswa->id)
                                      ->where(fn($q) => $q->where('status_masuk','diluar_radius')->orWhere('status_keluar','diluar_radius'))
                                      ->count(),
        ];

        return view('dosen.absensi.show', compact('mahasiswa', 'absensis', 'stats', 'dosen'));
    }

    /**
     * Dosen bisa memberi catatan koreksi pada absensi mahasiswanya
     */
    public function updateCatatan(Request $request, Absensi $absensi)
    {
        $dosen = Auth::user()->dosen;
        abort_if($absensi->mahasiswa->dosen_id !== $dosen->id, 403);

        $request->validate(['catatan_dosen' => 'nullable|string|max:1000']);
        $absensi->update(['catatan_dosen' => $request->catatan_dosen]);

        return back()->with('success', 'Catatan absensi berhasil disimpan.');
    }
}