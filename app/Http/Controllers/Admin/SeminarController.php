<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Seminar, Mahasiswa};
use Illuminate\Http\Request;

class SeminarController extends Controller {
    public function index() {
        $seminars   = Seminar::with('mahasiswa')->orderBy('tanggal')->get();
        $mahasiswas = Mahasiswa::where('status','seminar')->orWhere('status','selesai')->get();
        return view('admin.seminar.index', compact('seminars','mahasiswas'));
    }
    public function store(Request $request) {
        $request->validate(['mahasiswa_id'=>'required|exists:mahasiswas,id','tanggal'=>'required|date','jam'=>'required','ruangan'=>'required']);
        Seminar::updateOrCreate(
            ['mahasiswa_id'=>$request->mahasiswa_id],
            $request->only(['tanggal','jam','ruangan','dosen_penguji'])
        );
        // Update status mahasiswa jadi seminar
        Mahasiswa::find($request->mahasiswa_id)->update(['status'=>'seminar']);
        return back()->with('success','Jadwal seminar berhasil disimpan.');
    }
    public function update(Request $request, Seminar $seminar) {
        $request->validate(['status'=>'required|in:terjadwal,selesai']);
        $seminar->update($request->only(['status','tanggal','jam','ruangan','dosen_penguji','catatan']));
        if ($request->status === 'selesai') {
            $seminar->mahasiswa->update(['status'=>'selesai']);
        }
        return back()->with('success','Data seminar diperbarui.');
    }
    public function destroy(Seminar $seminar) {
        $seminar->delete();
        return back()->with('success','Jadwal seminar dihapus.');
    }
}