<?php

namespace App\Http\Controllers\Dosen;

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
        $dosen      = Auth::user()->dosen;
        $mahasiswas = Mahasiswa::with(['nilai', 'seminar', 'instansi'])
                        ->where('dosen_id', $dosen->id)->get();

        return view('dosen.nilai.index', compact('mahasiswas'));
    }

    /**
     * "Nilai Pembimbing" versi lama (form 1 angka via method update() di sini)
     * sudah DIHAPUS — digabung ke rubrik seminar (updateSeminar di bawah),
     * karena substansinya sama-sama penilaian dari dosen pembimbing dan cuma
     * bikin dosen input dua kali untuk hal yang sama.
     *
     * FIX: sebelumnya nulis ke kolom 'nilai' & status 'hadir' di tabel seminars,
     * padahal kolom itu tidak ada (nilai_seminar disimpan di tabel `nilais`).
     * Sekarang seminar cuma ditandai 'selesai' setelah dapat nilai.
     *
     * Diisi 6 aspek terpisah sesuai "Lembar Penilaian Seminar Kerja Praktik —
     * Dosen Pembimbing" (bukan satu angka nilai_seminar langsung), lalu
     * nilai_seminar dihitung otomatis dari bobot masing-masing aspek.
     */
    public function updateSeminar(Request $request, Mahasiswa $mahasiswa)
    {
        $dosen = Auth::user()->dosen;
        abort_if($mahasiswa->dosen_id !== $dosen->id, 403);

        $validator = Validator::make($request->all(), [
            'seminar_penguasaan_materi' => 'required|numeric|min:0|max:100',
            'seminar_sikap_ilmiah'      => 'required|numeric|min:0|max:100',
            'seminar_teknik_penyajian'  => 'required|numeric|min:0|max:100',
            'seminar_originalitas'      => 'required|numeric|min:0|max:100',
            'seminar_relevansi'         => 'required|numeric|min:0|max:100',
            'seminar_penulisan'         => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'seminar')->withInput();
        }

        $seminar = $mahasiswa->seminar;
        abort_if(!$seminar, 404, 'Jadwal seminar belum ada.');

        if (!$seminar->isTerjadwal() && !$seminar->isSelesai()) {
            return back()->with('error', 'Seminar mahasiswa ini belum dijadwalkan/disetujui admin.');
        }

        $seminar->update(['status' => 'selesai']);
        $mahasiswa->update(['status' => 'selesai', 'tanggal_selesai' => now()->toDateString()]);

        $nilai = Nilai::firstOrCreate(['mahasiswa_id' => $mahasiswa->id]);
        $nilai->fill($request->only([
            'seminar_penguasaan_materi', 'seminar_sikap_ilmiah', 'seminar_teknik_penyajian',
            'seminar_originalitas', 'seminar_relevansi', 'seminar_penulisan',
        ]));
        $nilai->nilai_seminar = $nilai->hitungNilaiSeminar();
        $nilai->nilai_akhir   = $nilai->hitungNilaiAkhir();
        $nilai->save();

        return back()->with('success', 'Nilai pembimbing berhasil disimpan.');
    }

    /**
     * Cetak "Lembar Penilaian Seminar Kerja Praktik — Dosen Pembimbing" untuk
     * satu mahasiswa. Judul KP/PKL diambil otomatis dari seminars.judul_kp
     * (diisi mahasiswa saat mengajukan jadwal seminar), jadi dosen tidak perlu
     * isi ulang. View cetak ini SAMA PERSIS dengan yang dipakai mahasiswa
     * mencetak nilainya sendiri (lihat Mahasiswa\NilaiController@cetak).
     */
    public function cetak(Mahasiswa $mahasiswa)
    {
        $dosen = Auth::user()->dosen;
        abort_if($mahasiswa->dosen_id !== $dosen->id, 403);

        $mahasiswa->load(['seminar', 'instansi', 'nilai']);
        abort_if(!$mahasiswa->nilai || $mahasiswa->nilai->nilai_seminar === null, 404, 'Nilai pembimbing belum diisi.');

        return view('nilai.cetak', [
            'mahasiswa' => $mahasiswa,
            'dosen'     => $dosen,
            'backUrl'   => route('dosen.nilai.index'),
        ]);
    }
}
