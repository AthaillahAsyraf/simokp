@extends('layouts.app')
@section('title','Data Dosen')
@section('content')

<div class="page-header">
  <h1>Data Dosen Pembimbing</h1>
  <p>Kelola dosen pembimbing KP Ilmu Komputer</p>
</div>

<div class="card">
  <div class="card-header">
    <div><h3>Daftar Dosen</h3><p>{{ $dosens->count() }} dosen terdaftar</p></div>
    <button class="btn btn-primary" onclick="openModal('modalTambah')">+ Tambah Dosen</button>
  </div>
  <table>
    <thead><tr><th>NIP</th><th>Nama Dosen</th><th>Bidang Keahlian</th><th>Email</th><th>Mhs Dibimbing</th><th>Aksi</th></tr></thead>
    <tbody>
      @forelse($dosens as $d)
      <tr>
        <td><code>{{ $d->nip }}</code></td>
        <td><strong>{{ $d->nama }}</strong></td>
        <td style="font-size:12px;color:var(--muted)">{{ $d->bidang ?? '–' }}</td>
        <td style="font-size:12px;color:var(--muted)">{{ $d->user?->email }}</td>
        <td><span class="pill pill-proses">{{ $d->mahasiswas->count() }} mhs</span></td>
        <td style="display:flex;gap:6px">
          <button class="btn btn-ghost btn-xs" onclick="openEdit({{ $d->id }},'{{ $d->nip }}','{{ $d->nama }}','{{ $d->bidang }}','{{ $d->no_hp }}')">Edit</button>
          <form method="POST" action="{{ route('admin.dosen.destroy',$d) }}" onsubmit="return confirm('Hapus dosen ini?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
          </form>
        </td>
      </tr>
      @empty
        <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:24px">Belum ada data dosen.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal-bg" id="modalTambah">
  <div class="modal-box">
    <h3>➕ Tambah Dosen Pembimbing</h3>
    <form method="POST" action="{{ route('admin.dosen.store') }}">
      @csrf
      <div class="form-group"><label>NIP *</label><input type="text" name="nip" class="form-control" placeholder="197..." required></div>
      <div class="form-group"><label>Nama Lengkap + Gelar *</label><input type="text" name="nama" class="form-control" placeholder="Dr. ..." required></div>
      <div class="form-group"><label>Bidang Keahlian</label><input type="text" name="bidang" class="form-control" placeholder="Rekayasa Perangkat Lunak"></div>
      <div class="form-group"><label>No. HP</label><input type="text" name="no_hp" class="form-control" placeholder="08xx"></div>
      <div class="form-group"><label>Email Unila *</label><input type="email" name="email" class="form-control" placeholder="nama@cs.unila.ac.id" required></div>
      <div class="form-group"><label>Password *</label><input type="password" name="password" class="form-control" placeholder="Min. 6 karakter" required></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modalTambah')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal-bg" id="modalEdit">
  <div class="modal-box">
    <h3>✏️ Edit Dosen</h3>
    <form method="POST" id="editForm">
      @csrf @method('PUT')
      <div class="form-group"><label>NIP *</label><input type="text" name="nip" id="eNip" class="form-control" required></div>
      <div class="form-group"><label>Nama Lengkap *</label><input type="text" name="nama" id="eNama" class="form-control" required></div>
      <div class="form-group"><label>Bidang Keahlian</label><input type="text" name="bidang" id="eBidang" class="form-control"></div>
      <div class="form-group"><label>No. HP</label><input type="text" name="no_hp" id="eHp" class="form-control"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modalEdit')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function openEdit(id, nip, nama, bidang, hp) {
  document.getElementById('editForm').action = `/admin/dosen/${id}`;
  document.getElementById('eNip').value = nip;
  document.getElementById('eNama').value = nama;
  document.getElementById('eBidang').value = bidang || '';
  document.getElementById('eHp').value = hp || '';
  openModal('modalEdit');
}
</script>
@endpush
@endsection