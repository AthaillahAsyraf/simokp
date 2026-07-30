<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FormKesediaanPembimbingController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa->load(['dosen', 'formKesediaanPembimbing']);
        // Form dapat dilihat/dicetak sebagai arsip penetapan pembimbing oleh admin.
        abort_unless($mahasiswa->formKesediaanPembimbing, 403);

        return view('mahasiswa.form-kesediaan-pembimbing.index', compact('mahasiswa'));
    }

}
