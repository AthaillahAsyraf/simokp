@extends('layouts.app')
@section('title','Data Mahasiswa')
@section('content')

<div class="page-header">
  <h1>Data Mahasiswa KP</h1>
  <p>Kelola semua mahasiswa Kerja Praktik</p>
</div>

<div class="card">
  <div class="card-header">
    <div><h3>Daftar Mahasiswa</h3><p>{{ $mahasiswas->total() }} mahasiswa terdaftar</p></div>
    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <form method="GET" style="display:flex;gap:8px">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Cari nama / NIM…" class="form-control" style="width:200px">
        <select name="status" class="form-control" style="width:130px">
          <option value="">Semua Status</option>
          <option value="proses" {{ request('status')=='proses'?'selected':'' }}>Proses</option>
          <option value="seminar" {{ request('status')=='seminar'?'selected':'' }}>Seminar</option>
          <option value="selesai" {{ request('status')=='selesai'?'selected':'' }}>Selesai</option>
        </select>
        <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
      </form>
      <button class="btn btn-primary" onclick="openModal('modalTambah')">+ Tambah Mahasiswa</button>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>NIM</th><th>Nama</th><th>Angkatan</th><th>Instansi</th><th>Dosen Pembimbing</th><th>Progress</th><th>Status</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($mahasiswas as $m)
      @php $pct = $m->progressPersen(); @endphp
      <tr>
        <td><code>{{ $m->nim }}</code></td>
        <td><strong>{{ $m->nama }}</strong></td>
        <td>{{ $m->angkatan }}</td>
        <td style="font-size:12px">{{ $m->instansi?->nama ?? '<span style="color:var(--muted)">–</span>' }}</td>
        <td style="font-size:12px">{{ $m->dosen?->nama ?? '<span style="color:var(--muted)">–</span>' }}</td>
        <td style="min-width:100px">
          <div class="prog-wrap"><div class="prog-bar" style="width:{{ $pct }}%;background:var(--admin)"></div></div>
          <div class="prog-txt">{{ $pct }}%</div>
        </td>
        <td><span class="pill pill-{{ $m->status }}">{{ ucfirst($m->status) }}</span></td>
        <td>
          <a href="{{ route('admin.mahasiswa.show', $m) }}" class="btn btn-ghost btn-xs">Detail</a>
          <button class="btn btn-ghost btn-xs" style="margin:2px 0" onclick="openEdit({{ $m->id }}, '{{ $m->nama }}', '{{ $m->angkatan }}', '{{ $m->status }}', '{{ $m->dosen_id }}', '{{ $m->instansi_id }}')">Edit</button>
          <form method="POST" action="{{ route('admin.mahasiswa.destroy', $m) }}" style="display:inline" onsubmit="return confirm('Hapus mahasiswa ini?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
          </form>
        </td>
      </tr>
      @empty
      <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:28px">Tidak ada data mahasiswa.</td></tr>
      @endforelse
    </tbody>
  </table>

  <div style="padding:16px 20px">
    {{ $mahasiswas->links() }}
  </div>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal-bg" id="modalTambah">
  <div class="modal-box">
    <h3>➕ Tambah Mahasiswa KP</h3>
    <form method="POST" action="{{ route('admin.mahasiswa.store') }}">
      @csrf
      <div class="form-grid">
        <div class="form-group"><label>NIM *</label><input type="text" name="nim" class="form-control" placeholder="2021..." required></div>
        <div class="form-group"><label>Nama Lengkap *</label><input type="text" name="nama" class="form-control" placeholder="Nama mahasiswa" required></div>
        <div class="form-group"><label>Angkatan *</label><input type="text" name="angkatan" class="form-control" placeholder="2021" required></div>
        <div class="form-group"><label>No. HP</label><input type="text" name="no_hp" class="form-control" placeholder="08xx"></div>
        <div class="form-group"><label>Email *</label><input type="email" name="email" class="form-control" placeholder="nim@students.cs.unila.ac.id" required></div>
        <div class="form-group"><label>Password *</label><input type="password" name="password" class="form-control" placeholder="Min. 6 karakter" required></div>
        <div class="form-group"><label>Dosen Pembimbing</label>
          <select name="dosen_id" class="form-control">
            <option value="">-- Pilih Dosen --</option>
            @foreach($dosens as $d)<option value="{{ $d->id }}">{{ $d->nama }}</option>@endforeach
          </select>
        </div>
        <div class="form-group"><label>Instansi / Lokasi KP</label>
          <select name="instansi_id" class="form-control">
            <option value="">-- Pilih Instansi --</option>
            @foreach($instansis as $i)<option value="{{ $i->id }}">{{ $i->nama }}</option>@endforeach
          </select>
        </div>
        <div class="form-group"><label>Tanggal Mulai KP</label><input type="date" name="tanggal_mulai" class="form-control"></div>
      </div>
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
    <h3>✏️ Edit Mahasiswa</h3>
    <form method="POST" id="editForm">
      @csrf @method('PUT')
      <div class="form-grid">
        <div class="form-group" style="grid-column:span 2"><label>Nama Lengkap *</label><input type="text" name="nama" id="editNama" class="form-control" required></div>
        <div class="form-group"><label>Angkatan *</label><input type="text" name="angkatan" id="editAngkatan" class="form-control" required></div>
        <div class="form-group"><label>Status KP *</label>
          <select name="status" id="editStatus" class="form-control">
            <option value="proses">Proses</option>
            <option value="seminar">Seminar</option>
            <option value="selesai">Selesai</option>
          </select>
        </div>
        <div class="form-group"><label>Dosen Pembimbing</label>
          <select name="dosen_id" id="editDosen" class="form-control">
            <option value="">-- Pilih Dosen --</option>
            @foreach($dosens as $d)<option value="{{ $d->id }}">{{ $d->nama }}</option>@endforeach
          </select>
        </div>
        <div class="form-group"><label>Instansi</label>
          <select name="instansi_id" id="editInstansi" class="form-control">
            <option value="">-- Pilih Instansi --</option>
            @foreach($instansis as $i)<option value="{{ $i->id }}">{{ $i->nama }}</option>@endforeach
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modalEdit')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function openEdit(id, nama, angkatan, status, dosenId, instansiId) {
  document.getElementById('editForm').action = `/admin/mahasiswa/${id}`;
  document.getElementById('editNama').value = nama;
  document.getElementById('editAngkatan').value = angkatan;
  document.getElementById('editStatus').value = status;
  document.getElementById('editDosen').value = dosenId;
  document.getElementById('editInstansi').value = instansiId;
  openModal('modalEdit');
}
</script>
@endpush
@endsection