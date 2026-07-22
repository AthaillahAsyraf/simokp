<?php
namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Seminar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeminarController extends Controller {
    public function index() {
        $ids = Auth::user()->dosen->mahasiswas()->pluck('id');
        $seminars = Seminar::with('mahasiswa')->whereIn('mahasiswa_id', $ids)->orderByRaw("status = 'menunggu_acc_dospem' desc")->latest()->get();
        return view('dosen.seminar.index', compact('seminars'));
    }
    public function verifikasiAcc(Request $request, Seminar $seminar) {
        abort_if($seminar->mahasiswa->dosen_id !== Auth::user()->dosen->id, 403);
        abort_unless($seminar->status === 'menunggu_acc_dospem', 422);
        $request->validate(['keputusan' => 'required|in:setujui,tolak', 'catatan' => 'nullable|string|max:500']);
        if ($request->keputusan === 'tolak' && !$request->filled('catatan')) return back()->with('error', 'Catatan wajib diisi saat menolak ACC.');
        $setujui = $request->keputusan === 'setujui';
        $seminar->update([
            'status' => $setujui ? Seminar::STATUS_MENUNGGU : Seminar::STATUS_ACC_DITOLAK,
            'catatan' => $request->catatan,
            'dosen_penguji_id' => $seminar->mahasiswa->dosen_id,
        ]);
        return back()->with('success', $setujui ? 'ACC seminar disetujui dan pengajuan diteruskan ke admin.' : 'Permintaan ACC ditolak dengan catatan.');
    }
}
