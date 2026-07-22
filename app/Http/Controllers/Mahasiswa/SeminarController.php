<?php
namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Seminar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeminarController extends Controller {
    public function index() {
        $mahasiswa = Auth::user()->mahasiswa->load(['seminar', 'bimbingans', 'dosen']);
        return view('mahasiswa.seminar.index', ['mahasiswa' => $mahasiswa, 'ruanganList' => Seminar::daftarRuangan(), 'jadwalTerisi' => Seminar::jadwalTerisi($mahasiswa->seminar?->id)]);
    }
    public function mintaAcc(Request $request) {
        $mahasiswa = Auth::user()->mahasiswa;
        $data = $request->validate([
            'judul_kp' => 'required|string|max:255',
            'tanggal' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'ruangan' => 'required|string|max:100',
        ]);
        if (!$mahasiswa->bimbingans()->where('jenis', 'laporan')->exists()) return back()->with('error', 'Unggah laporan final terlebih dahulu.');
        $lama = $mahasiswa->seminar;
        if ($lama && $lama->status !== 'acc_ditolak_dospem') return back()->with('error', 'Permintaan seminar masih aktif.');
        $bentrok = Seminar::cekBentrok($data['tanggal'], $data['jam_mulai'], $data['jam_selesai'], $data['ruangan'], $mahasiswa->dosen_id, $mahasiswa->dosen_id, $lama?->id);
        if ($bentrok) return back()->withErrors([$bentrok])->withInput();

        Seminar::updateOrCreate(['mahasiswa_id' => $mahasiswa->id], array_merge($data, [
            'dosen_penguji_id' => $mahasiswa->dosen_id,
            'status' => Seminar::STATUS_MENUNGGU_ACC_DOSPEM,
            'diajukan_oleh' => 'mahasiswa',
            'catatan' => null,
        ]));
        return back()->with('success', 'Permintaan ACC dan pilihan jadwal dikirim ke dosen pembimbing. Slot tersebut kini dikunci sementara.');
    }
    public function store(Request $request) {
        $mahasiswa = Auth::user()->mahasiswa; $seminar = $mahasiswa->seminar;
        if (!$seminar || $seminar->status !== 'acc_dospem') return back()->with('error', 'Menunggu ACC dosen pembimbing.');
        $data = $request->validate(['tanggal' => 'required|date|after_or_equal:today', 'jam_mulai' => 'required', 'jam_selesai' => 'required|after:jam_mulai', 'ruangan' => 'required|string|max:100']);
        $bentrok = Seminar::cekBentrok($data['tanggal'], $data['jam_mulai'], $data['jam_selesai'], $data['ruangan'], $mahasiswa->dosen_id, $mahasiswa->dosen_id, $seminar->id);
        if ($bentrok) return back()->withErrors([$bentrok], 'daftar')->withInput();
        $seminar->update(array_merge($data, ['dosen_penguji_id' => $mahasiswa->dosen_id, 'status' => Seminar::STATUS_MENUNGGU, 'catatan' => null]));
        return back()->with('success', 'Jadwal dikirim ke admin.');
    }
}
