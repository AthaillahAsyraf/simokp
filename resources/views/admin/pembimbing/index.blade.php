@extends('layouts.app')
@section('title','Pembimbing')

@push('styles')
<style>
.tab-bar{display:flex;gap:4px;margin-bottom:18px;border-bottom:1.5px solid var(--gray-200)}
.tab-btn{padding:10px 18px;font-size:13px;font-weight:600;color:var(--gray-500);background:none;border:none;cursor:pointer;border-bottom:2.5px solid transparent;margin-bottom:-1.5px;transition:all .15s}
.tab-btn:hover{color:var(--gray-800)}
.tab-btn.active{color:var(--blue-600);border-bottom-color:var(--blue-600)}
.tab-panel{display:none}
.tab-panel.active{display:block}
.err-box{background:var(--red-50);border:1px solid var(--red-100);border-radius:6px;padding:10px 14px;margin-bottom:14px}
.err-box strong{color:var(--red-600);font-size:.85rem}
.err-box ul{margin:4px 0 0 18px;color:var(--red-600);font-size:.82rem}
</style>
@endpush

@section('content')
<div class="page-header"><h1>Pembimbing</h1><p>Dosen pembimbing akademik &amp; pembimbing lapangan di instansi</p></div>

@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
@if(session('db_error'))<div class="err-box">❌ {{ session('db_error') }}</div>@endif

<div class="tab-bar">
  <button type="button" class="tab-btn" id="tabBtnDosen" onclick="switchTab('dosen')">👨‍🏫 Dosen Pembimbing</button>
  <button type="button" class="tab-btn" id="tabBtnLapangan" onclick="switchTab('lapangan')">🏢 Pembimbing Lapangan</button>
</div>

{{-- ============ TAB: DOSEN PEMBIMBING ============ --}}
<div class="tab-panel" id="panelDosen">

  <div class="page-header-row" style="margin-bottom:14px">
    <p style="color:var(--gray-500);font-size:13px">{{ $dosens->count() }} dosen terdaftar</p>
    <button class="btn btn-primary btn-sm" onclick="openModal('modalTambah')">+ Tambah Dosen</button>
  </div>

  <div class="card" style="margin-bottom:16px">
    <div class="card-body" style="padding:14px 18px">
      <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <input type="hidden" name="tab" value="dosen">
        <input type="text" name="search_dosen" value="{{ request('search_dosen') }}" placeholder="🔍 Cari nama / NIP..." class="form-control" style="width:240px">
        <button type="submit" class="btn btn-outline btn-sm">Filter</button>
        <a href="{{ route('admin.pembimbing.index',['tab'=>'dosen']) }}" class="btn btn-outline btn-sm">Reset</a>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>NIP</th><th>Nama Dosen</th><th>Email</th><th>No. HP</th><th>Mhs Dibimbing</th><th>Aksi</th></tr></thead>
        <tbody>
          @forelse($dosens as $d)
          <tr>
            <td><code>{{ $d->nip }}</code></td>
            <td><strong>{{ $d->nama }}</strong></td>
            <td class="text-sm text-muted">{{ $d->user?->email ?? '–' }}</td>
            <td class="text-sm text-muted">{{ $d->no_hp ?? '–' }}</td>
            <td><span class="badge badge-seminar">{{ $d->mahasiswas->count() }} mhs</span></td>
            <td>
              <div style="display:flex;gap:4px">
                <a href="{{ route('admin.dosen.show',$d) }}" class="btn btn-ghost btn-xs">Detail</a>
                <button class="btn btn-outline btn-xs"
                  onclick="openEditDosen({{ $d->id }},'{{ addslashes($d->nama) }}','{{ $d->nip }}','{{ $d->no_hp }}')">Edit</button>
                <form method="POST" action="{{ route('admin.dosen.destroy',$d) }}"
                  onsubmit="return confirm('Hapus dosen ini?')" style="display:inline">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
          @empty
            <tr><td colspan="6" style="text-align:center;padding:28px;color:var(--gray-400)">Belum ada data dosen.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- ============ TAB: PEMBIMBING LAPANGAN ============ --}}
<div class="tab-panel" id="panelLapanganLegacy">
@if(false)

  <div class="page-header-row" style="margin-bottom:14px">
    <p style="color:var(--gray-500);font-size:13px">{{ $instansis->count() }} pembimbing lapangan terdaftar</p>
    <button class="btn btn-primary btn-sm" onclick="openModal('modalTambahLapangan')">+ Tambah Pembimbing Lapangan</button>
  </div>

  <div class="card" style="margin-bottom:16px">
    <div class="card-body" style="padding:14px 18px">
      <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <input type="hidden" name="tab" value="lapangan">
        <input type="text" name="search_lapangan" value="{{ request('search_lapangan') }}" placeholder="🔍 Cari nama / NIM mahasiswa..." class="form-control" style="width:240px">
        <button type="submit" class="btn btn-outline btn-sm">Filter</button>
        <a href="{{ route('admin.pembimbing.index',['tab'=>'lapangan']) }}" class="btn btn-outline btn-sm">Reset</a>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Instansi</th><th>Pembimbing Lapangan</th><th>Bidang</th><th>Lokasi</th><th>Kontak</th><th>Mhs Dibimbing</th><th>Aksi</th></tr></thead>
        <tbody>
          @forelse($instansis as $inst)
          <tr>
            <td><code>{{ $m->nim }}</code></td>
            <td><strong>{{ $m->nama }}</strong></td>
            <td class="text-sm">{{ $m->instansi->nama ?? '–' }}</td>
            <td class="text-sm {{ $m->pembimbing_lapangan_nama ? '' : 'text-muted' }}">{{ $m->pembimbing_lapangan_nama ?? 'Belum diisi' }}</td>
            <td class="text-sm text-muted">{{ $m->pembimbing_lapangan_jabatan ?? '–' }}</td>
            <td class="text-sm text-muted">{{ $m->pembimbing_lapangan_no_hp ?? '–' }}</td>
            <td>
              <button class="btn btn-outline btn-xs"
                onclick="openEditLapangan({{ $m->id }},'{{ addslashes($m->nama) }}','{{ addslashes($m->pembimbing_lapangan_nama ?? '') }}','{{ addslashes($m->pembimbing_lapangan_jabatan ?? '') }}','{{ $m->pembimbing_lapangan_no_hp }}')">Edit</button>
            </td>
          </tr>
          @empty
            <tr><td colspan="7" style="text-align:center;padding:28px;color:var(--gray-400)">Belum ada mahasiswa yang ditempatkan di instansi.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@endif
</div>

{{-- ============ TAB BARU: PEMBIMBING LAPANGAN ============ --}}
<div class="tab-panel" id="panelLapangan">
  <div class="page-header-row" style="margin-bottom:14px">
    <p style="color:var(--gray-500);font-size:13px">{{ $instansis->count() }} pembimbing lapangan terdaftar</p>
    <button class="btn btn-primary btn-sm" onclick="openModal('modalTambahLapangan')">+ Tambah Pembimbing Lapangan</button>
  </div>
  <div class="card" style="margin-bottom:16px"><div class="card-body" style="padding:14px 18px">
    <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap"><input type="hidden" name="tab" value="lapangan"><input type="text" name="search_lapangan" value="{{ request('search_lapangan') }}" placeholder="Cari instansi / pembimbing / lokasi..." class="form-control" style="width:280px"><button type="submit" class="btn btn-outline btn-sm">Filter</button><a href="{{ route('admin.pembimbing.index',['tab'=>'lapangan']) }}" class="btn btn-outline btn-sm">Reset</a></form>
  </div></div>
  <div class="card"><div class="table-wrap"><table><thead><tr><th>Instansi</th><th>Pembimbing Lapangan</th><th>Email</th><th>No. HP</th><th>Mhs Dibimbing</th><th>Aksi</th></tr></thead><tbody>
    @forelse($instansis as $inst)<tr>
      <td><strong>{{ $inst->nama }}</strong></td><td class="text-sm">{{ $inst->kontak_person ?? '-' }}</td><td class="text-sm text-muted">{{ $inst->user?->email ?? '-' }}</td><td class="text-sm text-muted">{{ $inst->no_hp ?? '-' }}</td><td><span class="badge badge-proses">{{ $inst->mahasiswas->count() }} mhs</span></td>
      <td><div style="display:flex;gap:4px"><a href="{{ route('admin.instansi.show',$inst) }}" class="btn btn-ghost btn-xs">Detail</a><button type="button" class="btn btn-outline btn-xs" onclick="openEditInstansi({{ $inst->id }}, @json($inst->nama), @json($inst->bidang), @json($inst->alamat), @json($inst->kontak_person), @json($inst->no_hp), @json($inst->latitude), @json($inst->longitude))">Edit</button><form method="POST" action="{{ route('admin.instansi.destroy',$inst) }}" onsubmit="return confirm('Hapus pembimbing lapangan ini?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Hapus</button></form></div></td>
    </tr>@empty<tr><td colspan="6" style="text-align:center;padding:28px;color:var(--gray-400)">Belum ada pembimbing lapangan.</td></tr>@endforelse
  </tbody></table></div></div>
</div>

<div class="modal-bg" id="modalTambahLapangan"><div class="modal-box"><div class="modal-title">Tambah Pembimbing Lapangan</div>@if($errors->instansiTambah->any())<div class="err-box"><ul>@foreach($errors->instansiTambah->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif<form method="POST" action="{{ route('admin.instansi.store') }}">@csrf
  <div class="form-group"><label class="form-label">Nama Instansi *</label><input class="form-control" name="nama" required></div><div class="form-group"><label class="form-label">Nama Pembimbing Lapangan *</label><input class="form-control" name="kontak_person" required></div><div class="form-group"><label class="form-label">Bidang / Jabatan</label><input class="form-control" name="bidang"></div><div class="form-group"><label class="form-label">Lokasi / Alamat</label><textarea class="form-control" name="alamat" rows="2"></textarea></div><div class="form-grid"><div class="form-group"><label class="form-label">No. HP</label><input class="form-control" name="no_hp"></div><div class="form-group"><label class="form-label">Email Login *</label><input class="form-control" type="email" name="email" required></div></div><div class="form-grid"><div class="form-group"><label class="form-label">Latitude</label><input class="form-control" name="latitude"></div><div class="form-group"><label class="form-label">Longitude</label><input class="form-control" name="longitude"></div></div><div class="form-group"><label class="form-label">Password *</label><input class="form-control" type="password" name="password" minlength="8" required></div><div class="modal-footer"><button type="button" class="btn btn-outline" onclick="closeModal('modalTambahLapangan')">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>

<div class="modal-bg" id="modalEditInstansi"><div class="modal-box"><div class="modal-title">Edit Pembimbing Lapangan</div>@if($errors->instansiEdit->any())<div class="err-box"><ul>@foreach($errors->instansiEdit->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif<form method="POST" id="editFormInstansi">@csrf @method('PUT')
  <div class="form-group"><label class="form-label">Nama Instansi *</label><input class="form-control" name="nama" id="eiNama" required></div><div class="form-group"><label class="form-label">Nama Pembimbing Lapangan</label><input class="form-control" name="kontak_person" id="eiKontak"></div><div class="form-group"><label class="form-label">Bidang / Jabatan</label><input class="form-control" name="bidang" id="eiBidang"></div><div class="form-group"><label class="form-label">Lokasi / Alamat</label><textarea class="form-control" name="alamat" id="eiAlamat" rows="2"></textarea></div><div class="form-grid"><div class="form-group"><label class="form-label">No. HP</label><input class="form-control" name="no_hp" id="eiHp"></div><div class="form-group"><label class="form-label">Latitude</label><input class="form-control" name="latitude" id="eiLat"></div><div class="form-group"><label class="form-label">Longitude</label><input class="form-control" name="longitude" id="eiLng"></div></div><div class="modal-footer"><button type="button" class="btn btn-outline" onclick="closeModal('modalEditInstansi')">Batal</button><button class="btn btn-primary">Simpan Perubahan</button></div>
</form></div></div>

{{-- ============ MODAL TAMBAH DOSEN ============ --}}
<div class="modal-bg" id="modalTambah">
  <div class="modal-box">
    <div class="modal-title">➕ Tambah Dosen Pembimbing</div>
    @if($errors->tambah->any())
    <div class="err-box"><strong>⚠️ Terdapat kesalahan:</strong>
      <ul>@foreach($errors->tambah->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif
    <form method="POST" action="{{ route('admin.dosen.store') }}" id="formTambah" novalidate>
      @csrf
      <div class="form-group">
        <label class="form-label">NIP * <small class="text-muted">(angka)</small></label>
        <input type="text" name="nip" class="form-control @error('nip','tambah') is-invalid @enderror"
               placeholder="197001012000121001" value="{{ old('nip') }}" inputmode="numeric" pattern="[0-9]+" required>
        @error('nip','tambah')<small style="color:var(--red-600)">{{ $message }}</small>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">Nama Lengkap + Gelar *</label>
        <input type="text" name="nama" class="form-control @error('nama','tambah') is-invalid @enderror"
               placeholder="Dr. Ahmad, M.Kom." value="{{ old('nama') }}" required>
        @error('nama','tambah')<small style="color:var(--red-600)">{{ $message }}</small>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">No. HP <small class="text-muted">(angka)</small></label>
        <input type="text" name="no_hp" class="form-control @error('no_hp','tambah') is-invalid @enderror"
               placeholder="08123456789" value="{{ old('no_hp') }}" inputmode="numeric" pattern="[0-9]+">
        @error('no_hp','tambah')<small style="color:var(--red-600)">{{ $message }}</small>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">Email Unila *</label>
        <input type="email" name="email" class="form-control @error('email','tambah') is-invalid @enderror"
               placeholder="nama@cs.unila.ac.id" value="{{ old('email') }}" required>
        @error('email','tambah')<small style="color:var(--red-600)">{{ $message }}</small>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">Password * <small class="text-muted">(min. 8 karakter)</small></label>
        <input type="password" name="password" class="form-control @error('password','tambah') is-invalid @enderror"
               placeholder="Min. 8 karakter" minlength="8" required>
        @error('password','tambah')<small style="color:var(--red-600)">{{ $message }}</small>@enderror
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalTambah')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

{{-- ============ MODAL EDIT DOSEN ============ --}}
<div class="modal-bg" id="modalEditDosen">
  <div class="modal-box">
    <div class="modal-title">✏️ Edit Dosen</div>
    @if($errors->edit->any())
    <div class="err-box"><strong>⚠️ Terdapat kesalahan:</strong>
      <ul>@foreach($errors->edit->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif
    <form method="POST" id="editFormDosen">
      @csrf @method('PUT')
      <div class="form-group">
        <label class="form-label">NIP *</label>
        <input type="text" name="nip" id="eNip" class="form-control @error('nip','edit') is-invalid @enderror" inputmode="numeric" pattern="[0-9]+" required>
        @error('nip','edit')<small style="color:var(--red-600)">{{ $message }}</small>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">Nama Lengkap *</label>
        <input type="text" name="nama" id="eNama" class="form-control @error('nama','edit') is-invalid @enderror" required>
        @error('nama','edit')<small style="color:var(--red-600)">{{ $message }}</small>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">No. HP</label>
        <input type="text" name="no_hp" id="eHp" class="form-control @error('no_hp','edit') is-invalid @enderror" inputmode="numeric" pattern="[0-9]+">
        @error('no_hp','edit')<small style="color:var(--red-600)">{{ $message }}</small>@enderror
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalEditDosen')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

{{-- ============ MODAL EDIT PEMBIMBING LAPANGAN ============ --}}
<div class="modal-bg" id="modalEditLapangan">
  <div class="modal-box">
    <div class="modal-title" id="elTitle">✏️ Edit Pembimbing Lapangan</div>
    @if($errors->lapangan->any())
    <div class="err-box"><strong>⚠️ Terdapat kesalahan:</strong>
      <ul>@foreach($errors->lapangan->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif
    <form method="POST" id="editFormLapangan">
      @csrf @method('PUT')
      <div class="form-group">
        <label class="form-label">Nama Pembimbing Lapangan</label>
        <input type="text" name="pembimbing_lapangan_nama" id="elNama"
               class="form-control @error('pembimbing_lapangan_nama','lapangan') is-invalid @enderror"
               placeholder="Nama penanggung jawab mahasiswa di instansi" value="{{ old('pembimbing_lapangan_nama') }}">
        @error('pembimbing_lapangan_nama','lapangan')<small style="color:var(--red-600)">{{ $message }}</small>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">Jabatan</label>
        <input type="text" name="pembimbing_lapangan_jabatan" id="elJabatan"
               class="form-control @error('pembimbing_lapangan_jabatan','lapangan') is-invalid @enderror"
               placeholder="cth. Kepala Divisi IT" value="{{ old('pembimbing_lapangan_jabatan') }}">
        @error('pembimbing_lapangan_jabatan','lapangan')<small style="color:var(--red-600)">{{ $message }}</small>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">No. HP</label>
        <input type="text" name="pembimbing_lapangan_no_hp" id="elHp"
               class="form-control @error('pembimbing_lapangan_no_hp','lapangan') is-invalid @enderror"
               inputmode="numeric" placeholder="08123456789" value="{{ old('pembimbing_lapangan_no_hp') }}">
        @error('pembimbing_lapangan_no_hp','lapangan')<small style="color:var(--red-600)">{{ $message }}</small>@enderror
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalEditLapangan')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
@if($errors->instansiTambah->any())
window.addEventListener('load', function () { switchTab('lapangan'); openModal('modalTambahLapangan'); });
@endif
@if($errors->instansiEdit->any() && session('edit_instansi_id'))
window.addEventListener('load', function () {
  switchTab('lapangan');
  openEditInstansi({{ session('edit_instansi_id') }}, @json(old('nama')), @json(old('bidang')), @json(old('alamat')), @json(old('kontak_person')), @json(old('no_hp')), @json(old('latitude')), @json(old('longitude')));
});
@endif
function switchTab(tab) {
  const isLapangan = tab === 'lapangan';
  document.getElementById('panelDosen').classList.toggle('active', !isLapangan);
  document.getElementById('panelLapangan').classList.toggle('active', isLapangan);
  document.getElementById('tabBtnDosen').classList.toggle('active', !isLapangan);
  document.getElementById('tabBtnLapangan').classList.toggle('active', isLapangan);
  const url = new URL(window.location);
  url.searchParams.set('tab', tab);
  window.history.replaceState({}, '', url);
}
switchTab(@json($tab) === 'lapangan' ? 'lapangan' : 'dosen');

function openEditDosen(id, nama, nip, hp) {
  document.getElementById('editFormDosen').action = `{{ url('admin/dosen') }}/${id}`;
  document.getElementById('eNama').value = nama;
  document.getElementById('eNip').value  = nip || '';
  document.getElementById('eHp').value   = hp  || '';
  openModal('modalEditDosen');
}

function openEditInstansi(id, nama, bidang, alamat, kontak, hp, lat, lng) {
  document.getElementById('editFormInstansi').action = `{{ url('admin/instansi') }}/${id}`;
  document.getElementById('eiNama').value = nama || '';
  document.getElementById('eiBidang').value = bidang || '';
  document.getElementById('eiAlamat').value = alamat || '';
  document.getElementById('eiKontak').value = kontak || '';
  document.getElementById('eiHp').value = hp || '';
  document.getElementById('eiLat').value = lat || '';
  document.getElementById('eiLng').value = lng || '';
  openModal('modalEditInstansi');
}

function openEditLapangan(id, namaMhs, nama, jabatan, hp) {
  document.getElementById('editFormLapangan').action = `{{ url('admin/pembimbing-lapangan') }}/${id}`;
  document.getElementById('elTitle').textContent = '✏️ Pembimbing Lapangan — ' + namaMhs;
  document.getElementById('elNama').value    = nama || '';
  document.getElementById('elJabatan').value = jabatan || '';
  document.getElementById('elHp').value      = hp || '';
  openModal('modalEditLapangan');
}

@if($errors->tambah->any())
window.addEventListener('load', function () { switchTab('dosen'); openModal('modalTambah'); });
@endif

@if($errors->edit->any() && session('edit_id'))
window.addEventListener('load', function () {
    switchTab('dosen');
    openEditDosen(
        {{ session('edit_id') }},
        '{{ addslashes(old('nama', '')) }}',
        '{{ old('nip', '') }}',
        '{{ old('no_hp', '') }}'
    );
});
@endif

@if($errors->lapangan->any() && session('edit_lapangan_id'))
window.addEventListener('load', function () {
    switchTab('lapangan');
    openEditLapangan(
        {{ session('edit_lapangan_id') }},
        '',
        '{{ addslashes(old('pembimbing_lapangan_nama', '')) }}',
        '{{ addslashes(old('pembimbing_lapangan_jabatan', '')) }}',
        '{{ old('pembimbing_lapangan_no_hp', '') }}'
    );
});
@endif

document.getElementById('formTambah').addEventListener('submit', function (e) {
    const nip  = this.nip.value.trim();
    const noHp = this.no_hp.value.trim();
    const pwd  = this.password.value;
    const errs = [];
    if (!/^[0-9]+$/.test(nip))          errs.push('NIP harus berupa angka.');
    if (noHp && !/^[0-9]+$/.test(noHp)) errs.push('No. HP harus berupa angka.');
    if (pwd.length < 8)                  errs.push('Password minimal 8 karakter.');
    if (errs.length) {
        e.preventDefault();
        let box = document.getElementById('cerrTambah');
        if (!box) {
            box = document.createElement('div');
            box.id = 'cerrTambah';
            box.className = 'err-box';
            this.parentNode.insertBefore(box, this);
        }
        box.innerHTML = '<strong>⚠️ Terdapat kesalahan:</strong><ul>'
            + errs.map(e => '<li>' + e + '</li>').join('') + '</ul>';
    }
});
</script>
@endpush
@endsection
