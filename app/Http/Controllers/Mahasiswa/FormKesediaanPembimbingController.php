<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\FormKesediaanPembimbing;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\Auth;

class FormKesediaanPembimbingController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa->load(['dosen', 'formKesediaanPembimbing']);
        // Form tetap bisa dilihat/dicetak sebagai arsip setelah dosen menyetujui
        // dan mahasiswa berpindah ke tahap Aktif KP.
        abort_unless($mahasiswa->formKesediaanPembimbing, 403);

        return view('mahasiswa.form-kesediaan-pembimbing.index', compact('mahasiswa'));
    }

    public function teruskan()
    {
        $mahasiswa = Auth::user()->mahasiswa->load('formKesediaanPembimbing');
        $form = $mahasiswa->formKesediaanPembimbing;
        abort_unless($mahasiswa->tahap === Mahasiswa::TAHAP_MENUNGGU_KESEDIAAN_PEMBIMBING && $form, 403);

        if ($form->status !== FormKesediaanPembimbing::STATUS_DITERBITKAN) {
            return back()->with('error', 'Form kesediaan sudah diteruskan ke dosen pembimbing.');
        }

        $form->update(['status' => FormKesediaanPembimbing::STATUS_DITERUSKAN, 'diteruskan_at' => now()]);

        return back()->with('success', 'Form kesediaan berhasil diteruskan. Menunggu persetujuan dosen pembimbing.');
    }
}
