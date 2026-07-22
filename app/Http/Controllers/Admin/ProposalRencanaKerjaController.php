<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProposalRencanaKerja;

class ProposalRencanaKerjaController extends Controller
{
    public function index()
    {
        $proposals = ProposalRencanaKerja::with(['mahasiswa.dosen', 'mahasiswa.instansi'])
            ->latest('uploaded_at')->get();

        return view('admin.proposal-rencana-kerja.index', compact('proposals'));
    }
}
