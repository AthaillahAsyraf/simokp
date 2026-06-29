<?php
namespace App\Http\Controllers\Dosen;
use App\Http\Controllers\Controller;
use App\Models\Seminar;
use Illuminate\Support\Facades\Auth;

class SeminarController extends Controller {
    public function index() {
        $dosen      = Auth::user()->dosen;
        $mhsIds     = $dosen->mahasiswas()->pluck('id');
        $seminars   = Seminar::with(['mahasiswa.nilai', 'dosenPenguji'])
                        ->whereIn('mahasiswa_id', $mhsIds)->orderBy('tanggal')->get();
        return view('dosen.seminar.index', compact('seminars'));
    }
}