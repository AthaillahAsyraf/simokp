@extends('layouts.app')
@section('title','Jadwal Seminar')
@section('content')

<div class="page-header page-header-row">
  <div><h1>Jadwal Seminar KP</h1><p>Kelola jadwal seminar mahasiswa</p></div>
  <button class="btn btn-primary" onclick="openModal('modalTambah')">+ Tambah Jadwal</button>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Mahasiswa</th><th>Tanggal</th><th>Jam</th><th>Ruangan</th><th>Dosen Penguji</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        @forelse($seminars as $s)
        <tr>
          <td>
            <strong>{{ $s->mahasiswa?->nama }}</strong><br>
            <code>{{ $s->mahasiswa?->nim }}</code>
          </td>
          <td style="font-size:12px;font-family:monospace">{{ $s->tanggal }}</td>
          <td class="text-sm text-muted">{{ \Carbon\Carbon::parse($s->jam)->format('H:i') }} WIB</td>
          <td class="text-sm">{{ $s->ruangan }}</td>
          <td class="text-sm text-muted">{{ $s->dosen_penguji ?? '–' }}</td>
          <td><span class="badge badge-{{ $s->status }}">{{ ucfirst($s->status) }}</span></td>
          <td>
            <div style="display:flex;gap:4px">
              <button class="btn btn-outline btn-xs" onclick="openEdit({{ $s->id }},'{{ $s->tanggal }}','{{ \Carbon\Carbon::parse($s->jam)->format('H:i') }}','{{ addslashes($s->ruangan) }}','{{ addslashes($s->dosen_penguji) }}','{{ $s->status }}','{{ addslashes($s->catatan) }}')">Edit</button>
              <form method="POST" action="{{ route('admin.seminar.destroy',$s) }}" onsubmit="return confirm('Hapus jadwal ini?')" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
          <tr><td colspan="7" style="text-align:center;padding:28px;color:#94a3b8">Belum ada jadwal seminar.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal-bg" id="modalTambah">
  <div class="modal-box">
    <div class="modal-title">🎤 Tambah Jadwal Seminar</div>
    <form method="POST" action="{{ route('admin.seminar.store') }}">
      @csrf
      <div class="form-group">
        <label class="form-label">Mahasiswa *</label>
        <select name="mahasiswa_id" class="form-control" required>
          <option value="">-- Pilih Mahasiswa --</option>
          @foreach($mahasiswas as $m)
            <option value="{{ $m->id }}">{{ $m->nama }} ({{ $m->nim }})</option>
          @endforeach
        </select>
      </div>
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Tanggal *</label><input type="date" name="tanggal" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Jam *</label><input type="time" name="jam" class="form-control" required></div>
      </div>
      <div class="form-group"><label class="form-label">Ruangan *</label><input type="text" name="ruangan" class="form-control" placeholder="Lab A-301" required></div>
      <div class="form-group"><label class="form-label">Dosen Penguji</label><input type="text" name="dosen_penguji" class="form-control"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalTambah')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal-bg" id="modalEdit">
  <div class="modal-box">
    <div class="modal-title">✏️ Edit Seminar</div>
    <form method="POST" id="editForm">
      @csrf @method('PUT')
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Tanggal</label><input type="date" name="tanggal" id="eTgl" class="form-control"></div>
        <div class="form-group"><label class="form-label">Jam</label><input type="time" name="jam" id="eJam" class="form-control"></div>
      </div>
      <div class="form-group"><label class="form-label">Ruangan</label><input type="text" name="ruangan" id="eRuang" class="form-control"></div>
      <div class="form-group"><label class="form-label">Dosen Penguji</label><input type="text" name="dosen_penguji" id="ePenguji" class="form-control"></div>
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
function openEdit(id,tgl,jam,ruang,penguji,status,catatan){
  document.getElementById('editForm').action=`/admin/seminar/${id}`;
  document.getElementById('eTgl').value=tgl;
  document.getElementById('eJam').value=jam;
  document.getElementById('eRuang').value=ruang;
  document.getElementById('ePenguji').value=penguji||'';
  document.getElementById('eStatus').value=status;
  document.getElementById('eCatatan').value=catatan||'';
  openModal('modalEdit');
}
</script>
@endpush
@endsection