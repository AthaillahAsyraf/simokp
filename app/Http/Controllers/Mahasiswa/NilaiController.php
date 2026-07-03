<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NilaiController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa->load(['nilai', 'seminar', 'instansi', 'dosen']);
        return view('mahasiswa.nilai.index', compact('mahasiswa'));
    }

    /**
     * Mahasiswa mencetak nilainya sendiri — pakai view cetak yang SAMA PERSIS
     * dengan yang dosen pakai (lihat Dosen\NilaiController@cetak), supaya
     * formatnya konsisten siapa pun yang mencetak.
     */
    public function cetak()
    {
        $mahasiswa = Auth::user()->mahasiswa->load(['seminar', 'instansi', 'nilai', 'dosen']);
        abort_if(!$mahasiswa->nilai || $mahasiswa->nilai->nilai_seminar === null, 404, 'Nilai pembimbing belum diisi dosen.');

        return view('nilai.cetak', [
            'mahasiswa' => $mahasiswa,
            'dosen'     => $mahasiswa->dosen,
            'backUrl'   => route('mahasiswa.nilai.index'),
        ]);
    }

    /**
     * Cetak "FORM NILAI PEMBIMBING LAPANGAN" — view cetak SAMA PERSIS dengan
     * yang dipakai Instansi (lihat Instansi\NilaiController@cetak).
     */
    public function cetakLapangan()
    {
        $mahasiswa = Auth::user()->mahasiswa->load(['seminar', 'instansi', 'nilai']);
        abort_if(!$mahasiswa->nilai || $mahasiswa->nilai->nilai_lapangan === null, 404, 'Nilai lapangan belum diisi pembimbing lapangan.');

        return view('nilai.cetak-lapangan', [
            'mahasiswa' => $mahasiswa,
            'backUrl'   => route('mahasiswa.nilai.index'),
        ]);
    }
}