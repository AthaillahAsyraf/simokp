<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\FormKesediaanPembimbing;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\Auth;

class FormKesediaanPembimbingController extends Controller
{
    public function index()
    {
        $dosen = Auth::user()->dosen;
        $forms = FormKesediaanPembimbing::with('mahasiswa.instansi')
            ->where('dosen_id', $dosen->id)->latest('diteruskan_at')->get();

        return view('dosen.form-kesediaan-pembimbing.index', compact('forms'));
    }

    public function show(FormKesediaanPembimbing $form)
    {
        abort_unless($form->dosen_id === Auth::user()->dosen->id, 403);
        $form->load(['mahasiswa', 'dosen']);

        return view('dosen.form-kesediaan-pembimbing.show', compact('form'));
    }

    public function setujui(FormKesediaanPembimbing $form)
    {
        abort_unless($form->dosen_id === Auth::user()->dosen->id, 403);

        if ($form->status !== FormKesediaanPembimbing::STATUS_DITERUSKAN) {
            return back()->with('error', 'Form ini belum diteruskan mahasiswa atau sudah disetujui.');
        }

        $form->update(['status' => FormKesediaanPembimbing::STATUS_DISETUJUI, 'disetujui_at' => now()]);
        $form->mahasiswa->update([
            'tahap'          => Mahasiswa::TAHAP_AKTIF_KP,
            'tanggal_mulai'  => now()->toDateString(),
            'tanggal_selesai'=> null,
        ]);

        return back()->with('success', 'Kesediaan membimbing disetujui. Mahasiswa kini dapat mengirim proposal rencana kerja.');
    }
}
