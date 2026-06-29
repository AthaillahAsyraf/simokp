<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Seminar, Mahasiswa, Dosen};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SeminarController extends Controller {

    public function index(Request $request) {
        $query = Seminar::with(['mahasiswa.dosen', 'dosenPenguji']);
        if ($request->filled('status')) $query->where('status', $request->status);
        $seminars = $query->orderByRaw("status = 'menunggu_persetujuan' desc")->orderBy('tanggal')->get();

        // Mahasiswa yang boleh dijadwalkan langsung oleh admin: sudah tahap seminar/selesai,
        // dan belum punya seminar aktif (atau seminar lamanya ditolak -> boleh diajukan ulang)
        $mahasiswas = Mahasiswa::whereIn('status', ['seminar', 'selesai'])
            ->where(function ($q) {
                $q->whereDoesntHave('seminar')
                  ->orWhereHas('seminar', fn ($q2) => $q2->where('status', 'ditolak'));
            })->get();

        $dosens = Dosen::orderBy('nama')->get();
        $ruanganList = Seminar::daftarRuangan();

        return view('admin.seminar.index', compact('seminars', 'mahasiswas', 'dosens', 'ruanganList'));
    }

    /** Admin bikin jadwal langsung (tidak lewat pengajuan) */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'mahasiswa_id'     => 'required|exists:mahasiswas,id',
            'tanggal'          => 'required|date',
            'jam_mulai'        => 'required',
            'jam_selesai'      => 'required|after:jam_mulai',
            'ruangan'          => 'required|string|max:100',
            'dosen_penguji_id' => 'required|exists:dosens,id',
        ], [], ['jam_selesai' => 'jam selesai']);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'tambah')->withInput();
        }

        $mahasiswa = Mahasiswa::find($request->mahasiswa_id);

        $bentrok = Seminar::cekBentrok(
            $request->tanggal, $request->jam_mulai, $request->jam_selesai,
            $request->ruangan, (int) $request->dosen_penguji_id, $mahasiswa->dosen_id
        );
        if ($bentrok) {
            return back()->with('db_error', $bentrok)->withInput();
        }

        Seminar::updateOrCreate(
            ['mahasiswa_id' => $request->mahasiswa_id],
            [
                'tanggal'          => $request->tanggal,
                'jam_mulai'        => $request->jam_mulai,
                'jam_selesai'      => $request->jam_selesai,
                'ruangan'          => $request->ruangan,
                'dosen_penguji_id' => $request->dosen_penguji_id,
                'status'           => 'terjadwal',
                'diajukan_oleh'    => 'admin',
                'catatan'          => null,
            ]
        );

        $mahasiswa->update(['status' => 'seminar']);

        return redirect()->route('admin.seminar.index')->with('success', 'Jadwal seminar berhasil disimpan.');
    }

    /**
     * Admin menyetujui pengajuan mahasiswa — sekaligus melengkapi dosen penguji
     * & (kalau perlu) menyesuaikan jam/ruangan, lalu dicek ulang bentroknya.
     */
    public function approve(Request $request, Seminar $seminar) {
        abort_unless($seminar->isPending(), 422, 'Pengajuan ini sudah diproses.');

        $validator = Validator::make($request->all(), [
            'tanggal'          => 'required|date',
            'jam_mulai'        => 'required',
            'jam_selesai'      => 'required|after:jam_mulai',
            'ruangan'          => 'required|string|max:100',
            'dosen_penguji_id' => 'required|exists:dosens,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'approve')->withInput()->with('approve_id', $seminar->id);
        }

        $bentrok = Seminar::cekBentrok(
            $request->tanggal, $request->jam_mulai, $request->jam_selesai,
            $request->ruangan, (int) $request->dosen_penguji_id, $seminar->mahasiswa->dosen_id,
            $seminar->id
        );
        if ($bentrok) {
            return back()->with('db_error', $bentrok)->withInput()->with('approve_id', $seminar->id);
        }

        $seminar->update([
            'tanggal'          => $request->tanggal,
            'jam_mulai'        => $request->jam_mulai,
            'jam_selesai'      => $request->jam_selesai,
            'ruangan'          => $request->ruangan,
            'dosen_penguji_id' => $request->dosen_penguji_id,
            'status'           => 'terjadwal',
        ]);
        $seminar->mahasiswa->update(['status' => 'seminar']);

        return back()->with('success', 'Pengajuan seminar disetujui & dijadwalkan.');
    }

    public function reject(Request $request, Seminar $seminar) {
        abort_unless($seminar->isPending(), 422, 'Pengajuan ini sudah diproses.');

        $request->validate(['catatan' => 'required|string|max:500'], [
            'catatan.required' => 'Alasan penolakan wajib diisi supaya mahasiswa tahu apa yang perlu diperbaiki.',
        ]);

        $seminar->update(['status' => 'ditolak', 'catatan' => $request->catatan]);

        return back()->with('success', 'Pengajuan seminar ditolak.');
    }

    /** Edit manual jadwal yang sudah terjadwal/selesai (bukan untuk approve pengajuan) */
    public function update(Request $request, Seminar $seminar) {
        $validator = Validator::make($request->all(), [
            'tanggal'          => 'required|date',
            'jam_mulai'        => 'required',
            'jam_selesai'      => 'required|after:jam_mulai',
            'ruangan'          => 'required|string|max:100',
            'dosen_penguji_id' => 'nullable|exists:dosens,id',
            'status'           => 'required|in:terjadwal,selesai',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'edit')->withInput()->with('edit_id', $seminar->id);
        }

        $bentrok = Seminar::cekBentrok(
            $request->tanggal, $request->jam_mulai, $request->jam_selesai,
            $request->ruangan, $request->dosen_penguji_id, $seminar->mahasiswa->dosen_id,
            $seminar->id
        );
        if ($bentrok) {
            return back()->with('db_error', $bentrok)->withInput()->with('edit_id', $seminar->id);
        }

        $seminar->update($request->only(['tanggal','jam_mulai','jam_selesai','ruangan','dosen_penguji_id','status','catatan']));

        if ($request->status === 'selesai') {
            $seminar->mahasiswa->update(['status' => 'selesai']);
        }

        return back()->with('success', 'Data seminar diperbarui.');
    }

    public function destroy(Seminar $seminar) {
        $seminar->delete();
        return back()->with('success', 'Jadwal seminar dihapus.');
    }
}