<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Seminar, Mahasiswa};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SeminarController extends Controller
{
    public function index(Request $request)
    {
        $query = Seminar::with('mahasiswa.dosen');
        $query->whereNotIn('status', [Seminar::STATUS_MENUNGGU_ACC_DOSPEM, Seminar::STATUS_ACC_DOSPEM, Seminar::STATUS_ACC_DITOLAK]);
        if ($request->filled('status')) $query->where('status', $request->status);
        $seminars = $query->orderByRaw("status = 'menunggu_persetujuan' desc")->orderBy('tanggal')->get();
        $mahasiswas = Mahasiswa::with('dosen')->whereIn('status', ['seminar', 'selesai'])
            ->where(function ($q) { $q->whereDoesntHave('seminar')->orWhereHas('seminar', fn ($q2) => $q2->where('status', 'ditolak')); })
            ->get();
        return view('admin.seminar.index', ['seminars' => $seminars, 'mahasiswas' => $mahasiswas, 'ruanganList' => Seminar::daftarRuangan()]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) return back()->withErrors($validator, 'tambah')->withInput();
        $mahasiswa = Mahasiswa::findOrFail($request->mahasiswa_id);
        if (!$mahasiswa->dosen_id) return back()->with('db_error', 'Mahasiswa belum memiliki dosen pembimbing.')->withInput();
        $bentrok = $this->cekBentrok($request, $mahasiswa);
        if ($bentrok) return back()->with('db_error', $bentrok)->withInput();

        Seminar::updateOrCreate(['mahasiswa_id' => $mahasiswa->id], array_merge($this->jadwalData($request), [
            'dosen_penguji_id' => $mahasiswa->dosen_id,
            'status' => Seminar::STATUS_TERJADWAL, 'diajukan_oleh' => 'admin', 'catatan' => null,
        ]));
        $mahasiswa->update(['status' => 'seminar']);
        return redirect()->route('admin.seminar.index')->with('success', 'Jadwal seminar berhasil disimpan. Dosen pembimbing otomatis menjadi penguji.');
    }

    public function approve(Request $request, Seminar $seminar)
    {
        abort_unless($seminar->isPending(), 422, 'Pengajuan ini sudah diproses.');
        $validator = Validator::make($request->all(), $this->rules(false));
        if ($validator->fails()) return back()->withErrors($validator, 'approve')->withInput()->with('approve_id', $seminar->id);
        $mahasiswa = $seminar->mahasiswa;
        if (!$mahasiswa->dosen_id) return back()->with('db_error', 'Mahasiswa belum memiliki dosen pembimbing.')->withInput()->with('approve_id', $seminar->id);
        $bentrok = $this->cekBentrok($request, $mahasiswa, $seminar->id);
        if ($bentrok) return back()->with('db_error', $bentrok)->withInput()->with('approve_id', $seminar->id);

        $seminar->update(array_merge($this->jadwalData($request), ['dosen_penguji_id' => $mahasiswa->dosen_id, 'status' => Seminar::STATUS_TERJADWAL]));
        $mahasiswa->update(['status' => 'seminar']);
        return back()->with('success', 'Pengajuan seminar disetujui dan dijadwalkan.');
    }

    public function reject(Request $request, Seminar $seminar)
    {
        abort_unless($seminar->isPending(), 422, 'Pengajuan ini sudah diproses.');
        $request->validate(['catatan' => 'required|string|max:500'], ['catatan.required' => 'Alasan penolakan wajib diisi supaya mahasiswa tahu apa yang perlu diperbaiki.']);
        $seminar->update(['status' => Seminar::STATUS_DITOLAK, 'catatan' => $request->catatan]);
        return back()->with('success', 'Pengajuan seminar ditolak.');
    }

    public function update(Request $request, Seminar $seminar)
    {
        $validator = Validator::make($request->all(), array_merge($this->rules(false), ['status' => 'required|in:terjadwal,selesai', 'catatan' => 'nullable|string|max:500']));
        if ($validator->fails()) return back()->withErrors($validator, 'edit')->withInput()->with('edit_id', $seminar->id);
        $mahasiswa = $seminar->mahasiswa;
        if (!$mahasiswa->dosen_id) return back()->with('db_error', 'Mahasiswa belum memiliki dosen pembimbing.')->withInput()->with('edit_id', $seminar->id);
        $bentrok = $this->cekBentrok($request, $mahasiswa, $seminar->id);
        if ($bentrok) return back()->with('db_error', $bentrok)->withInput()->with('edit_id', $seminar->id);

        $seminar->update(array_merge($this->jadwalData($request), ['dosen_penguji_id' => $mahasiswa->dosen_id, 'status' => $request->status, 'catatan' => $request->catatan]));
        if ($request->status === Seminar::STATUS_SELESAI) {
            $mahasiswa->update(['status' => 'selesai', 'tanggal_selesai' => now()->toDateString()]);
        }
        return back()->with('success', 'Data seminar diperbarui.');
    }

    public function destroy(Seminar $seminar) { $seminar->delete(); return back()->with('success', 'Jadwal seminar dihapus.'); }

    private function rules(bool $butuhMahasiswa = true): array
    {
        return array_filter([
            'mahasiswa_id' => $butuhMahasiswa ? 'required|exists:mahasiswas,id' : null,
            'tanggal' => 'required|date', 'jam_mulai' => 'required', 'jam_selesai' => 'required|after:jam_mulai', 'ruangan' => 'required|string|max:100',
        ]);
    }

    private function jadwalData(Request $request): array { return $request->only(['tanggal', 'jam_mulai', 'jam_selesai', 'ruangan']); }
    private function cekBentrok(Request $request, Mahasiswa $mahasiswa, ?int $excludeId = null): ?string
    {
        return Seminar::cekBentrok($request->tanggal, $request->jam_mulai, $request->jam_selesai, $request->ruangan, $mahasiswa->dosen_id, $mahasiswa->dosen_id, $excludeId);
    }
}
