<?php

namespace App\Http\Controllers\Instansi;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Nilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NilaiController extends Controller
{
    public function index()
    {
        $instansi   = Auth::user()->instansi;
        $mahasiswas = Mahasiswa::with(['nilai', 'dosen'])
                        ->where('instansi_id', $instansi->id)->get();

        return view('instansi.nilai.index', compact('mahasiswas'));
    }

    /**
     * Nilai ini secara substansi adalah penilaian dari Pembimbing Lapangan
     * (bukan "instansi" sebagai institusi). Karena pembimbing lapangan belum
     * punya akun login sendiri, nilai diinput lewat akun Instansi — tapi wajib
     * sudah ada nama pembimbing lapangan yang tercatat di profil mahasiswa,
     * supaya nilai ini selalu bisa dipertanggungjawabkan atas nama seseorang
     * yang jelas, bukan cuma "diisi instansi".
     */
    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $instansi = Auth::user()->instansi;
        abort_if($mahasiswa->instansi_id !== $instansi->id, 403);

        if (!$mahasiswa->pembimbing_lapangan_nama) {
            return back()->with('error', "Nama Pembimbing Lapangan untuk {$mahasiswa->nama} belum diisi admin. Hubungi admin untuk melengkapi data ini sebelum memberi nilai.");
        }

        $validator = Validator::make($request->all(), [
            'lapangan_kehadiran'               => 'required|numeric|min:0|max:100',
            'lapangan_tata_tertib'              => 'required|numeric|min:0|max:100',
            'lapangan_kerjasama_anggota'        => 'required|numeric|min:0|max:100',
            'lapangan_kerjasama_kelompok_lain'  => 'required|numeric|min:0|max:100',
            'lapangan_kerjasama_pembimbing'     => 'required|numeric|min:0|max:100',
            'lapangan_inovasi'                  => 'required|numeric|min:0|max:100',
            'lapangan_tugas'                    => 'required|numeric|min:0|max:100',
            'lapangan_keseriusan'               => 'required|numeric|min:0|max:100',
            'catatan_lapangan'                  => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'instansi')->withInput();
        }

        $nilai = Nilai::firstOrCreate(['mahasiswa_id' => $mahasiswa->id]);
        $nilai->fill($request->only(Nilai::KOMPONEN_LAPANGAN));
        $nilai->catatan_lapangan = $request->catatan_lapangan;
        $nilai->nilai_lapangan   = $nilai->hitungNilaiLapangan();
        $nilai->nilai_akhir      = $nilai->hitungNilaiAkhir();
        $nilai->save();

        return back()->with('success', 'Nilai pembimbing lapangan berhasil disimpan.');
    }

    /**
     * Cetak "FORM NILAI PEMBIMBING LAPANGAN" (format resmi instansi) untuk
     * satu mahasiswa. View cetak ini SAMA PERSIS dengan yang dipakai
     * mahasiswa mencetak nilai lapangannya sendiri (lihat
     * Mahasiswa\NilaiController@cetakLapangan).
     */
    public function cetak(Mahasiswa $mahasiswa)
    {
        $instansi = Auth::user()->instansi;
        abort_if($mahasiswa->instansi_id !== $instansi->id, 403);

        $mahasiswa->load(['seminar', 'instansi', 'nilai']);
        abort_if(!$mahasiswa->nilai || $mahasiswa->nilai->nilai_lapangan === null, 404, 'Nilai lapangan belum diisi.');

        return view('nilai.cetak-lapangan', [
            'mahasiswa' => $mahasiswa,
            'backUrl'   => route('instansi.nilai.index'),
        ]);
    }
}