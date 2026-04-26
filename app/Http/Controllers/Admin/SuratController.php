<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use Illuminate\Http\Request;

class SuratController extends Controller
{
    public function index()
    {
        $surats = Surat::with('mahasiswa')->latest()->get();
        return view('admin.surat.index', compact('surats'));
    }

    public function approve(Surat $surat)
    {
        $surat->update(['status' => 'disetujui']);
        return back()->with('success', 'Surat disetujui.');
    }

    public function reject(Request $request, Surat $surat)
    {
        $surat->update(['status' => 'ditolak', 'catatan_admin' => $request->catatan_admin]);
        return back()->with('success', 'Surat ditolak.');
    }
}