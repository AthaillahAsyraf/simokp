@extends('layouts.app')
@section('title','Data Instansi')
@section('content')

<div class="page-header">
  <h1>Data Instansi / Lokasi KP</h1>
  <p>Kelola tempat Kerja Praktik mahasiswa</p>
</div>

<div class="card">
  <div class="card-header">
    <div><h3>Daftar Instansi</h3><p>{{ $instansis->count() }} instansi terdaftar</p></div>
    <button class="btn btn-primary" onclick="openModal('modalTambah')">+ Tambah Instansi</button>
  </div>
  <table>
    <thead><tr><th>#</th><th>Nama Instansi</th><th>Bidang</th><th>Kontak Person</th><th>Mhs KP</th><th>Aksi</th></tr></thead>
    <tbody>
      @forelse($instansis as $i => $inst)
      <tr>
        <td style="color:var(--muted)">{{ $i+1 }}</td>
        <td>
          <strong>{{ $inst->nama }}</strong><br>
          <span style="font-size:11px;color:var(--muted)">{{ Str::limit($inst->alamat, 45) }}</span>
        </td>
        <td style="font-size:12px">{{ $inst->bidang ?? '–' }}</td>
        <td style="font-size:12px;color:var(--muted)">{{ $inst->kontak_person ?? '–' }}<br>{{ $inst->no_hp ?? '' }}</td>
        <td><span class="pill pill-proses">{{ $inst->mahasiswas->count() }}</span></td>
        <td style="display:flex;gap:6px">
          <button class="btn btn-ghost btn-xs" onclick="openEdit({{ $inst->id }},'{{ addslashes($inst->nama) }}','{{ addslashes($inst->bidang) }}','{{ addslashes($inst->alamat) }}','{{ $inst->kontak_person }}','{{ $inst->no_hp }}')">Edit</button>
          <form method="POST" action="{{ route('admin.instansi.destroy',$inst) }}" onsubmit="return confirm('Hapus instansi ini?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
          </form>
        </td>
      </tr>
      @empty
        <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:24px">Belum ada data instansi.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal-bg" id="modalTambah">
  <div class="modal-box">
    <h3>🏢 Tambah Instansi</h3>
    <form method="POST" action="{{ route('admin.instansi.store') }}">
      @csrf
      <div class="form-group"><label>Nama Instansi *</label><input type="text" name="nama" class="form-control" placeholder="PT / Dinas / Badan ..." required></div>
      <div class="form-group"><label>Bidang Usaha</label><input type="text" name="bidang" class="form-control" placeholder="Software Development"></div>
      <div class="form-group"><label>Alamat Lengkap</label><textarea name="alamat" class="form-control" rows="2" placeholder="Jl. ..."></textarea></div>
      <div class="form-group"><label>Kontak Person</label><input type="text" name="kontak_person" class="form-control" placeholder="Nama PIC"></div>
      <div class="form-group"><label>No. HP Kontak</label><input type="text" name="no_hp" class="form-control" placeholder="08xx"></div>
      <div class="form-group"><label>Email Login Instansi *</label><input type="email" name="email" class="form-control" placeholder="hrd@perusahaan.com" required></div>
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
    <h3>✏️ Edit Instansi</h3>
    <form method="POST" id="editForm">
      @csrf @method('PUT')
      <div class="form-group"><label>Nama Instansi *</label><input type="text" name="nama" id="eNama" class="form-control" required></div>
      <div class="form-group"><label>Bidang</label><input type="text" name="bidang" id="eBidang" class="form-control"></div>
      <div class="form-group"><label>Alamat</label><textarea name="alamat" id="eAlamat" class="form-control" rows="2"></textarea></div>
      <div class="form-group"><label>Kontak Person</label><input type="text" name="kontak_person" id="eKontak" class="form-control"></div>
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
function openEdit(id,nama,bidang,alamat,kontak,hp){
  document.getElementById('editForm').action=`/admin/instansi/${id}`;
  document.getElementById('eNama').value=nama;
  document.getElementById('eBidang').value=bidang||'';
  document.getElementById('eAlamat').value=alamat||'';
  document.getElementById('eKontak').value=kontak||'';
  document.getElementById('eHp').value=hp||'';
  openModal('modalEdit');
}
</script>
@endpush
@endsection