@extends('layouts.app')
@section('title','Jadwal Seminar')
@section('content')

<div class="page-header">
  <h1>Jadwal Seminar KP</h1>
  <p>Kelola jadwal dan hasil seminar Kerja Praktik</p>
</div>

<div class="card">
  <div class="card-header">
    <div><h3>Daftar Seminar</h3><p>{{ $seminars->count() }} jadwal terdaftar</p></div>
    <button class="btn btn-primary" onclick="openModal('modalTambah')">+ Tambah Jadwal</button>
  </div>
  <table>
    <thead>
      <tr><th>Mahasiswa</th><th>Tanggal</th><th>Jam</th><th>Ruangan</th><th>Dosen Penguji</th><th>Status</th><th>Nilai</th><th>Aksi</th></tr>
    </thead>
    <tbody>
      @forelse($seminars as $s)
      <tr>
        <td><strong>{{ $s->mahasiswa?->nama }}</strong><br><code>{{ $s->mahasiswa?->nim }}</code></td>
        <td style="font-size:12px;font-family:'JetBrains Mono',monospace">{{ $s->tanggal }}</td>
        <td style="font-size:12px;color:var(--muted)">{{ \Carbon\Carbon::parse($s->jam)->format('H:i') }} WIB</td>
        <td style="font-size:12px">{{ $s->ruangan }}</td>
        <td style="font-size:12px;color:var(--muted)">{{ $s->dosen_penguji ?? '–' }}</td>
        <td><span class="pill pill-{{ $s->status }}">{{ str_replace('_',' ',$s->status) }}</span></td>
        <td><strong style="color:var(--dosen-l)">{{ $s->nilai ?? '–' }}</strong></td>
        <td>
          <button class="btn btn-ghost btn-xs" onclick="openEdit({{ $s->id }},'{{ $s->tanggal }}','{{ \Carbon\Carbon::parse($s->jam)->format('H:i') }}','{{ $s->ruangan }}','{{ $s->dosen_penguji }}','{{ $s->status }}','{{ $s->nilai }}','{{ addslashes($s->catatan) }}')">Edit</button>
        </td>
      </tr>
      @empty
        <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:24px">Belum ada jadwal seminar.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal-bg" id="modalTambah">
  <div class="modal-box">
    <h3>🎤 Tambah Jadwal Seminar</h3>
    <form method="POST" action="{{ route('admin.seminar.store') }}">
      @csrf
      <div class="form-group">
        <label>Mahasiswa *</label>
        <select name="mahasiswa_id" class="form-control" required>
          <option value="">-- Pilih Mahasiswa --</option>
          @foreach($mahasiswas as $m)<option value="{{ $m->id }}">{{ $m->nama }} ({{ $m->nim }})</option>@endforeach
        </select>
      </div>
      <div class="form-grid">
        <div class="form-group"><label>Tanggal *</label><input type="date" name="tanggal" class="form-control" required></div>
        <div class="form-group"><label>Jam *</label><input type="time" name="jam" class="form-control" required></div>
      </div>
      <div class="form-group"><label>Ruangan *</label><input type="text" name="ruangan" class="form-control" placeholder="Lab Komputer A-301" required></div>
      <div class="form-group"><label>Dosen Penguji</label><input type="text" name="dosen_penguji" class="form-control" placeholder="Nama dosen penguji"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modalTambah')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal-bg" id="modalEdit">
  <div class="modal-box">
    <h3>✏️ Edit Seminar</h3>
    <form method="POST" id="editForm">
      @csrf @method('PUT')
      <div class="form-grid">
        <div class="form-group"><label>Tanggal</label><input type="date" name="tanggal" id="eTgl" class="form-control"></div>
        <div class="form-group"><label>Jam</label><input type="time" name="jam" id="eJam" class="form-control"></div>
      </div>
      <div class="form-group"><label>Ruangan</label><input type="text" name="ruangan" id="eRuang" class="form-control"></div>
      <div class="form-group"><label>Dosen Penguji</label><input type="text" name="dosen_penguji" id="ePenguji" class="form-control"></div>
      <div class="form-grid">
        <div class="form-group">
          <label>Status</label>
          <select name="status" id="eStatus" class="form-control">
            <option value="terjadwal">Terjadwal</option>
            <option value="hadir">Hadir</option>
            <option value="tidak_hadir">Tidak Hadir</option>
          </select>
        </div>
        <div class="form-group"><label>Nilai (0–100)</label><input type="number" name="nilai" id="eNilai" class="form-control" min="0" max="100" step="0.01"></div>
      </div>
      <div class="form-group"><label>Catatan</label><textarea name="catatan" id="eCatatan" class="form-control" rows="2"></textarea></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modalEdit')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function openEdit(id,tgl,jam,ruang,penguji,status,nilai,catatan){
  document.getElementById('editForm').action=`/admin/seminar/${id}`;
  document.getElementById('eTgl').value=tgl;
  document.getElementById('eJam').value=jam;
  document.getElementById('eRuang').value=ruang;
  document.getElementById('ePenguji').value=penguji||'';
  document.getElementById('eStatus').value=status;
  document.getElementById('eNilai').value=nilai||'';
  document.getElementById('eCatatan').value=catatan||'';
  openModal('modalEdit');
}
</script>
@endpush
@endsection