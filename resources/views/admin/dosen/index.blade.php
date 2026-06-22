@extends('layouts.app')
@section('title','Data Dosen')
@section('content')

<div class="page-header page-header-row">
  <div><h1>Data Dosen Pembimbing</h1><p>{{ $dosens->count() }} dosen terdaftar</p></div>
  <button class="btn btn-primary" onclick="openModal('modalTambah')">+ Tambah Dosen</button>
</div>

{{-- Flash success --}}
@if(session('success'))
  <div style="background:#dcfce7;border:1px solid #86efac;border-radius:6px;padding:10px 14px;margin-bottom:14px;color:#166534">
    ✅ {{ session('success') }}
  </div>
@endif

{{-- ✅ FIX BUG 2: Tampilkan error dari DB/exception (di LUAR modal agar selalu terlihat) --}}
@if(session('db_error'))
  <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:6px;padding:10px 14px;margin-bottom:14px;color:#dc2626">
    ❌ {{ session('db_error') }}
  </div>
@endif

{{-- Filter --}}
<div class="card" style="margin-bottom:16px">
  <div class="card-body" style="padding:14px 18px">
    <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Cari nama / NIP..." class="form-control" style="width:240px">
      <button type="submit" class="btn btn-outline btn-sm">Filter</button>
      <a href="{{ route('admin.dosen.index') }}" class="btn btn-outline btn-sm">Reset</a>
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
          <td>
            <span class="badge badge-seminar">{{ $d->mahasiswas->count() }} mhs</span>
          </td>
          <td>
            <div style="display:flex;gap:4px">
              <a href="{{ route('admin.dosen.show',$d) }}" class="btn btn-ghost btn-xs">Detail</a>
              <button class="btn btn-outline btn-xs"
                onclick="openEdit({{ $d->id }},'{{ addslashes($d->nama) }}','{{ $d->nip }}','{{ $d->no_hp }}')">Edit</button>
              <form method="POST" action="{{ route('admin.dosen.destroy',$d) }}"
                onsubmit="return confirm('Hapus dosen ini?')" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
          <tr><td colspan="6" style="text-align:center;padding:28px;color:#94a3b8">Belum ada data dosen.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal-bg" id="modalTambah">
  <div class="modal-box">
    <div class="modal-title">➕ Tambah Dosen Pembimbing</div>

    @if($errors->tambah->any())
    <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:6px;padding:10px 14px;margin-bottom:14px">
      <strong style="color:#dc2626;font-size:.85rem">⚠️ Terdapat kesalahan:</strong>
      <ul style="margin:4px 0 0 18px;color:#dc2626;font-size:.82rem">
        @foreach($errors->tambah->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.dosen.store') }}" id="formTambah" novalidate>
      @csrf

      <div class="form-group">
        <label class="form-label">NIP * <small class="text-muted">(angka)</small></label>
        <input type="text" name="nip"
               class="form-control @error('nip','tambah') is-invalid @enderror"
               placeholder="197001012000121001"
               value="{{ old('nip') }}"
               inputmode="numeric" pattern="[0-9]+"
               title="NIP harus berupa angka saja" required>
        @error('nip','tambah')<small style="color:#dc2626">{{ $message }}</small>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">Nama Lengkap + Gelar *</label>
        <input type="text" name="nama"
               class="form-control @error('nama','tambah') is-invalid @enderror"
               placeholder="Dr. Ahmad, M.Kom."
               value="{{ old('nama') }}" required>
        @error('nama','tambah')<small style="color:#dc2626">{{ $message }}</small>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">No. HP <small class="text-muted">(angka)</small></label>
        <input type="text" name="no_hp"
               class="form-control @error('no_hp','tambah') is-invalid @enderror"
               placeholder="08123456789"
               value="{{ old('no_hp') }}"
               inputmode="numeric" pattern="[0-9]+"
               title="No. HP harus berupa angka saja">
        @error('no_hp','tambah')<small style="color:#dc2626">{{ $message }}</small>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">Email Unila *</label>
        <input type="email" name="email"
               class="form-control @error('email','tambah') is-invalid @enderror"
               placeholder="nama@cs.unila.ac.id"
               value="{{ old('email') }}" required>
        @error('email','tambah')<small style="color:#dc2626">{{ $message }}</small>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">Password * <small class="text-muted">(min. 8 karakter)</small></label>
        <input type="password" name="password"
               class="form-control @error('password','tambah') is-invalid @enderror"
               placeholder="Min. 8 karakter"
               minlength="8" title="Password minimal 8 karakter" required>
        @error('password','tambah')<small style="color:#dc2626">{{ $message }}</small>@enderror
      </div>

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
    <div class="modal-title">✏️ Edit Dosen</div>

    @if($errors->edit->any())
    <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:6px;padding:10px 14px;margin-bottom:14px">
      <strong style="color:#dc2626;font-size:.85rem">⚠️ Terdapat kesalahan:</strong>
      <ul style="margin:4px 0 0 18px;color:#dc2626;font-size:.82rem">
        @foreach($errors->edit->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <form method="POST" id="editForm">
      @csrf @method('PUT')
      <div class="form-group">
        <label class="form-label">NIP *</label>
        <input type="text" name="nip" id="eNip"
               class="form-control @error('nip','edit') is-invalid @enderror"
               inputmode="numeric" pattern="[0-9]+" required>
        @error('nip','edit')<small style="color:#dc2626">{{ $message }}</small>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">Nama Lengkap *</label>
        <input type="text" name="nama" id="eNama"
               class="form-control @error('nama','edit') is-invalid @enderror"
               required>
        @error('nama','edit')<small style="color:#dc2626">{{ $message }}</small>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">No. HP</label>
        <input type="text" name="no_hp" id="eHp"
               class="form-control @error('no_hp','edit') is-invalid @enderror"
               inputmode="numeric" pattern="[0-9]+">
        @error('no_hp','edit')<small style="color:#dc2626">{{ $message }}</small>@enderror
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalEdit')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
/* ── ✅ FIX BUG 3: auto-buka modal TAMBAH jika ada error validasi ── */
@if($errors->tambah->any())
window.addEventListener('load', function () { openModal('modalTambah'); });
@endif

/* ── Auto-buka modal EDIT jika ada error validasi edit ── */
@if($errors->edit->any() && session('edit_id'))
window.addEventListener('load', function () {
    openEdit(
        {{ session('edit_id') }},
        '{{ addslashes(old('nama', '')) }}',
        '{{ old('nip', '') }}',
        '{{ old('no_hp', '') }}'
    );
});
@endif

/* ── Validasi client-side sebelum submit ── */
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
            box.style.cssText = 'background:#fee2e2;border:1px solid #fca5a5;border-radius:6px;padding:10px 14px;margin-bottom:14px';
            this.parentNode.insertBefore(box, this);
        }
        box.innerHTML = '<strong style="color:#dc2626;font-size:.85rem">⚠️ Terdapat kesalahan:</strong>'
            + '<ul style="margin:4px 0 0 18px;color:#dc2626;font-size:.82rem">'
            + errs.map(e => '<li>' + e + '</li>').join('') + '</ul>';
    }
});

/* ── Open Edit Modal ── */
function openEdit(id, nama, nip, hp) {
    document.getElementById('editForm').action = `/admin/dosen/${id}`;
    document.getElementById('eNama').value = nama;
    document.getElementById('eNip').value  = nip || '';
    document.getElementById('eHp').value   = hp  || '';
    openModal('modalEdit');
}
</script>
@endpush
@endsection