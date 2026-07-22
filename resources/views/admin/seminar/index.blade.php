@extends('layouts.app')
@section('title','Jadwal Seminar')

@push('styles')
<style>
.badge-menunggu_persetujuan{background:var(--amber-100);color:var(--amber-600)}
.badge-ditolak{background:var(--red-100);color:var(--red-600)}
.pending-row{background:var(--amber-50)}
.err-box{background:var(--red-50);border:1px solid var(--red-100);border-radius:6px;padding:10px 14px;margin-bottom:14px}
.err-box strong{color:var(--red-600);font-size:.85rem}
.err-box ul{margin:4px 0 0 18px;color:var(--red-600);font-size:.82rem}
.info-box{background:var(--blue-50);border:1px solid var(--blue-100);border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:var(--blue-700)}
.filter-row{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center}
</style>
@endpush

@section('content')
@php $jumlahMenunggu = $seminars->where('status','menunggu_persetujuan')->count(); @endphp

<datalist id="daftarRuangan">
  @foreach($ruanganList as $r)<option value="{{ $r }}">@endforeach
</datalist>

<div class="page-header page-header-row">
  <div>
    <h1>Jadwal Seminar KP</h1>
    <p>Kelola jadwal seminar mahasiswa — sistem otomatis cek bentrok ruangan & dosen</p>
  </div>
  <button class="btn btn-primary" onclick="openModal('modalTambah')">+ Tambah Jadwal Langsung</button>
</div>

@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
@if(session('db_error'))<div class="err-box">❌ {{ session('db_error') }}</div>@endif

@if($jumlahMenunggu > 0)
  <div class="info-box">🕓 Ada <strong>{{ $jumlahMenunggu }}</strong> pengajuan seminar dari mahasiswa yang menunggu persetujuan kamu.</div>
@endif

<form method="GET" class="filter-row">
  <select name="status" class="form-control" style="width:auto;min-width:180px" onchange="this.form.submit()">
    <option value="">Semua Status</option>
    @foreach(['menunggu_persetujuan'=>'Menunggu Persetujuan','terjadwal'=>'Terjadwal','selesai'=>'Selesai','ditolak'=>'Ditolak'] as $val=>$lbl)
      <option value="{{ $val }}" {{ request('status')==$val?'selected':'' }}>{{ $lbl }}</option>
    @endforeach
  </select>
  @if(request('status'))<a href="{{ route('admin.seminar.index') }}" class="btn btn-outline btn-sm">Reset</a>@endif
</form>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Mahasiswa</th><th>Tanggal</th><th>Jam</th><th>Ruangan</th><th>Dosen Penguji</th><th>Diajukan</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        @forelse($seminars as $s)
        <tr class="{{ $s->isPending() ? 'pending-row' : '' }}">
          <td>
            <strong>{{ $s->mahasiswa?->nama }}</strong><br>
            <code>{{ $s->mahasiswa?->nim }}</code>
          </td>
          <td style="font-size:12px;font-family:monospace">{{ \Carbon\Carbon::parse($s->tanggal)->format('d M Y') }}</td>
          <td class="text-sm text-muted">
            {{ $s->jam_mulai ? \Carbon\Carbon::parse($s->jam_mulai)->format('H:i') : '-' }}{{ $s->jam_selesai ? ' - '.\Carbon\Carbon::parse($s->jam_selesai)->format('H:i') : '' }} WIB
          </td>
          <td class="text-sm">{{ $s->ruangan }}</td>
          <td class="text-sm text-muted">{{ $s->dosenPenguji?->nama ?? '–' }}</td>
          <td class="text-sm text-muted">{{ $s->diajukan_oleh === 'mahasiswa' ? '🎓 Mahasiswa' : '🛡️ Admin' }}</td>
          <td><span class="badge badge-{{ $s->status }}">{{ ucwords(str_replace('_',' ',$s->status)) }}</span></td>
          <td>
            <div style="display:flex;gap:4px;flex-wrap:wrap">
              @if($s->isPending())
                <button class="btn btn-success btn-xs" onclick="openApprove({{ $s->id }}, {{ json_encode($s->mahasiswa?->nama) }}, {{ json_encode($s->tanggal) }}, {{ json_encode($s->jam_mulai) }}, {{ json_encode($s->jam_selesai) }}, {{ json_encode($s->ruangan) }})">✅ Setujui</button>
                <button class="btn btn-danger btn-xs" onclick="openReject({{ $s->id }}, {{ json_encode($s->mahasiswa?->nama) }})">✖️ Tolak</button>
              @else
                <button class="btn btn-outline btn-xs" onclick="openEdit({{ $s->id }}, {{ json_encode($s->tanggal) }}, {{ json_encode($s->jam_mulai) }}, {{ json_encode($s->jam_selesai) }}, {{ json_encode($s->ruangan) }}, {{ json_encode($s->dosen_penguji_id) }}, {{ json_encode($s->status) }}, {{ json_encode($s->catatan) }})">Edit</button>
              @endif
              <form method="POST" action="{{ route('admin.seminar.destroy',$s) }}" onsubmit="return confirm('Hapus jadwal ini?')" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
          <tr><td colspan="8" style="text-align:center;padding:28px;color:var(--gray-400)">Belum ada jadwal seminar.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- MODAL TAMBAH LANGSUNG --}}
<div class="modal-bg" id="modalTambah">
  <div class="modal-box">
    <div class="modal-title">🎤 Tambah Jadwal Seminar Langsung</div>
    @if($errors->tambah->any())
    <div class="err-box"><strong>⚠️ Terdapat kesalahan:</strong>
      <ul>@foreach($errors->tambah->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif
    <form method="POST" action="{{ route('admin.seminar.store') }}">
      @csrf
      <div class="form-group">
        <label class="form-label">Mahasiswa *</label>
        <select name="mahasiswa_id" class="form-control" required>
          <option value="">-- Pilih Mahasiswa --</option>
          @foreach($mahasiswas as $m)
            <option value="{{ $m->id }}" {{ old('mahasiswa_id')==$m->id?'selected':'' }}>{{ $m->nama }} ({{ $m->nim }})</option>
          @endforeach
        </select>
        @if($mahasiswas->isEmpty())<p class="form-hint">Tidak ada mahasiswa yang siap dijadwalkan (harus laporan 100% & belum ada jadwal aktif).</p>@endif
      </div>
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Tanggal *</label><input type="date" name="tanggal" class="form-control" value="{{ old('tanggal') }}" required></div>
        <div class="form-group"><label class="form-label">Jam Mulai *</label><input type="time" name="jam_mulai" class="form-control" value="{{ old('jam_mulai') }}" required></div>
        <div class="form-group"><label class="form-label">Jam Selesai *</label><input type="time" name="jam_selesai" class="form-control" value="{{ old('jam_selesai') }}" required></div>
      </div>
      <div class="form-group"><label class="form-label">Ruangan *</label><input type="text" name="ruangan" class="form-control" list="daftarRuangan" placeholder="Pilih atau ketik ruangan baru" value="{{ old('ruangan') }}" required></div>
      <div class="alert alert-info">Dosen pembimbing mahasiswa akan otomatis ditetapkan sebagai dosen penguji.</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalTambah')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL APPROVE --}}
<div class="modal-bg" id="modalApprove">
  <div class="modal-box">
    <div class="modal-title" id="apTitle">✅ Setujui Pengajuan Seminar</div>
    @if($errors->approve->any())
    <div class="err-box"><strong>⚠️ Terdapat kesalahan / bentrok:</strong>
      <ul>@foreach($errors->approve->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif
    <form method="POST" id="approveForm">
      @csrf
      <p class="form-hint" style="margin-bottom:14px">Mahasiswa mengajukan jadwal di bawah ini. Sesuaikan kalau perlu, lalu tentukan dosen pengujinya.</p>
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Tanggal *</label><input type="date" name="tanggal" id="apTgl" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Jam Mulai *</label><input type="time" name="jam_mulai" id="apJamMulai" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Jam Selesai *</label><input type="time" name="jam_selesai" id="apJamSelesai" class="form-control" required></div>
      </div>
      <div class="form-group"><label class="form-label">Ruangan *</label><input type="text" name="ruangan" id="apRuangan" class="form-control" list="daftarRuangan" placeholder="Pilih atau ketik ruangan baru" required></div>
      <div class="alert alert-info">Dosen pembimbing otomatis menjadi dosen penguji.</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalApprove')">Batal</button>
        <button type="submit" class="btn btn-success">Setujui & Jadwalkan</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL REJECT --}}
<div class="modal-bg" id="modalReject">
  <div class="modal-box">
    <div class="modal-title" id="rjTitle">✖️ Tolak Pengajuan Seminar</div>
    <form method="POST" id="rejectForm">
      @csrf
      <div class="form-group">
        <label class="form-label">Alasan Penolakan *</label>
        <textarea name="catatan" class="form-control" rows="3" placeholder="cth: Ruangan tidak tersedia, ajukan ulang dengan jadwal lain." required></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalReject')">Batal</button>
        <button type="submit" class="btn btn-danger">Tolak Pengajuan</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal-bg" id="modalEdit">
  <div class="modal-box">
    <div class="modal-title">✏️ Edit Seminar</div>
    @if($errors->edit->any())
    <div class="err-box"><strong>⚠️ Terdapat kesalahan / bentrok:</strong>
      <ul>@foreach($errors->edit->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif
    <form method="POST" id="editForm">
      @csrf @method('PUT')
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Tanggal</label><input type="date" name="tanggal" id="eTgl" class="form-control"></div>
        <div class="form-group"><label class="form-label">Jam Mulai</label><input type="time" name="jam_mulai" id="eJamMulai" class="form-control"></div>
        <div class="form-group"><label class="form-label">Jam Selesai</label><input type="time" name="jam_selesai" id="eJamSelesai" class="form-control"></div>
      </div>
      <div class="form-group"><label class="form-label">Ruangan</label><input type="text" name="ruangan" id="eRuang" class="form-control" list="daftarRuangan" placeholder="Pilih atau ketik ruangan baru"></div>
      <div class="alert alert-info">Dosen pembimbing tetap otomatis menjadi dosen penguji.</div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select name="status" id="eStatus" class="form-control">
          <option value="terjadwal">Terjadwal</option>
          <option value="selesai">Selesai</option>
        </select>
      </div>
      <div class="form-group"><label class="form-label">Catatan</label><textarea name="catatan" id="eCatatan" class="form-control" rows="2"></textarea></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalEdit')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function openApprove(id, nama, tgl, jamMulai, jamSelesai, ruangan) {
  document.getElementById('approveForm').action = `{{ url('admin/seminar') }}/${id}/approve`;
  document.getElementById('apTitle').textContent = '✅ Setujui Pengajuan — ' + nama;
  document.getElementById('apTgl').value        = tgl || '';
  document.getElementById('apJamMulai').value    = jamMulai || '';
  document.getElementById('apJamSelesai').value  = jamSelesai || '';
  document.getElementById('apRuangan').value     = ruangan || '';
  openModal('modalApprove');
}

function openReject(id, nama) {
  document.getElementById('rejectForm').action = `{{ url('admin/seminar') }}/${id}/reject`;
  document.getElementById('rjTitle').textContent = '✖️ Tolak Pengajuan — ' + nama;
  openModal('modalReject');
}

function openEdit(id, tgl, jamMulai, jamSelesai, ruang, pengujiId, status, catatan) {
  document.getElementById('editForm').action = `{{ url('admin/seminar') }}/${id}`;
  document.getElementById('eTgl').value        = tgl || '';
  document.getElementById('eJamMulai').value   = jamMulai || '';
  document.getElementById('eJamSelesai').value = jamSelesai || '';
  document.getElementById('eRuang').value      = ruang || '';
  document.getElementById('eStatus').value     = status === 'selesai' ? 'selesai' : 'terjadwal';
  document.getElementById('eCatatan').value    = catatan || '';
  openModal('modalEdit');
}

@if($errors->tambah->any())
window.addEventListener('load', function () { openModal('modalTambah'); });
@endif
</script>
@endpush
@endsection
