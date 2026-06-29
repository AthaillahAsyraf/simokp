<?php
namespace App\Http\Controllers\Mahasiswa;
use App\Http\Controllers\Controller;
use App\Models\Seminar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SeminarController extends Controller {
    public function index() {
        $mahasiswa   = Auth::user()->mahasiswa->load(['seminar.dosenPenguji', 'progressBabs', 'nilai']);
        $ruanganList = Seminar::daftarRuangan();
        $jadwalTerisi = Seminar::jadwalTerisi($mahasiswa->seminar?->id);
        return view('mahasiswa.seminar.index', compact('mahasiswa', 'ruanganList', 'jadwalTerisi'));
    }

    /**
     * Mahasiswa mengajukan jadwal seminar (tanggal/jam/ruangan). Dosen penguji
     * BELUM diisi di sini — itu ditentukan admin saat approve, supaya admin yang
     * berwenang menugaskan penguji, bukan mahasiswa yang memilih sendiri.
     * Status awal: menunggu_persetujuan, baru jadi 'terjadwal' setelah admin setuju.
     *
     * Ruangan & jam otomatis dicek bentrok terhadap pengajuan mahasiswa lain
     * (baik yang masih menunggu maupun yang sudah terjadwal) — jadi begitu satu
     * mahasiswa klaim slot, mahasiswa lain tidak bisa ambil slot yang sama lagi.
     */
    public function store(Request $request) {
        $mahasiswa = Auth::user()->mahasiswa->load(['progressBabs', 'seminar']);

        if (!$mahasiswa->allBabSelesai()) {
            return back()->with('error', 'Seminar baru bisa diajukan setelah semua BAB laporan disetujui dosen pembimbing.');
        }

        $seminarLama = $mahasiswa->seminar;
        if ($seminarLama && !$seminarLama->isDitolak()) {
            return back()->with('error', 'Anda sudah punya pengajuan/jadwal seminar yang aktif.');
        }

        $validator = Validator::make($request->all(), [
            'tanggal'     => 'required|date|after_or_equal:today',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'ruangan'     => 'required|string|max:100',
        ], [], ['jam_selesai' => 'jam selesai']);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'daftar')->withInput();
        }

        $bentrok = Seminar::cekBentrok(
            $request->tanggal, $request->jam_mulai, $request->jam_selesai,
            $request->ruangan, null, $mahasiswa->dosen_id,
            $seminarLama?->id
        );
        if ($bentrok) {
            return back()->withErrors([$bentrok], 'daftar')->withInput();
        }

        Seminar::updateOrCreate(
            ['mahasiswa_id' => $mahasiswa->id],
            [
                'tanggal'          => $request->tanggal,
                'jam_mulai'        => $request->jam_mulai,
                'jam_selesai'      => $request->jam_selesai,
                'ruangan'          => $request->ruangan,
                'dosen_penguji_id' => null,
                'status'           => 'menunggu_persetujuan',
                'diajukan_oleh'    => 'mahasiswa',
                'catatan'          => null,
            ]
        );

        return back()->with('success', 'Pengajuan jadwal seminar terkirim, menunggu persetujuan admin.');
    }
}