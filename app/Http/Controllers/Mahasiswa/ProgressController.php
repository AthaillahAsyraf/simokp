<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProgressController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa->load('bimbingans');
        return view('mahasiswa.progress.index', compact('mahasiswa'));
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'keterangan' => 'required|string|max:1000',
        ]);
        if ($validator->fails()) return back()->withErrors($validator, 'upload')->withInput();

        $mahasiswa = Auth::user()->mahasiswa;
        try {
            $uploaded = $request->file('file');
            $path = $uploaded->store('bimbingan/'.$mahasiswa->id, 'public');
            Bimbingan::create([
                'mahasiswa_id' => $mahasiswa->id,
                'jenis' => Bimbingan::JENIS_LAPORAN,
                'keterangan' => $request->keterangan,
                'file' => $path,
                'file_asli' => $uploaded->getClientOriginalName(),
            ]);
        } catch (\Throwable $e) {
            return back()->with('db_error', 'Gagal mengupload file bimbingan. Silakan coba lagi.');
        }

        return back()->with('success', 'File bimbingan berhasil dikirim, menunggu tanggapan dosen pembimbing.');
    }

    public function mintaAccSeminar()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $sudahAda = $mahasiswa->bimbingans()->where('jenis', Bimbingan::JENIS_ACC_SEMINAR)
            ->whereIn('status', [Bimbingan::STATUS_MENUNGGU, Bimbingan::STATUS_DISETUJUI])->exists();
        if ($sudahAda) return back()->with('error', 'Permintaan ACC seminar Anda sudah dikirim atau sudah disetujui.');
        if (!$mahasiswa->bimbingans()->where('jenis', Bimbingan::JENIS_LAPORAN)->exists()) {
            return back()->with('error', 'Unggah laporan final terlebih dahulu sebelum meminta ACC seminar.');
        }

        Bimbingan::create([
            'mahasiswa_id' => $mahasiswa->id,
            'jenis' => Bimbingan::JENIS_ACC_SEMINAR,
            'keterangan' => 'Mahasiswa menyatakan laporan telah mencapai BAB V/final dan meminta ACC untuk mendaftar seminar.',
        ]);
        return back()->with('success', 'Permintaan ACC seminar telah dikirim ke dosen pembimbing.');
    }
}
