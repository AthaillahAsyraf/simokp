@extends('layouts.app')
@section('title','Data Mahasiswa')
@section('content')

<div class="page-header page-header-row">
  <div>
    <h1>Daftar Mahasiswa</h1>
    <p>{{ $mahasiswas->total() }} mahasiswa terdaftar</p>
  </div>
  <button class="btn btn-primary" onclick="openModal('modalTambah')">+ Tambah Mahasiswa</button>
</div>

{{-- Flash success --}}
@if(session('success'))
  <div style="background:#dcfce7;border:1px solid #86efac;border-radius:6px;padding:10px 14px;margin-bottom:14px;color:#166534">
    ✅ {{ session('success') }}
  </div>
@endif

{{-- Filter --}}
<div class="card" style="margin-bottom:16px">
  <div class="card-body" style="padding:14px 18px">
    <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Cari nama / NIM..." class="form-control" style="width:210px">
      <select name="status" class="form-control" style="width:140px">
        <option value="">Semua Status</option>
        <option value="proses"  {{ request('status')=='proses'  ?'selected':'' }}>Proses</option>
        <option value="seminar" {{ request('status')=='seminar' ?'selected':'' }}>Seminar</option>
        <option value="selesai" {{ request('status')=='selesai' ?'selected':'' }}>Selesai</option>
      </select>
      <select name="dosen_id" class="form-control" style="width:190px">
        <option value="">Semua Dosen</option>
        @foreach($dosens as $d)
          <option value="{{ $d->id }}" {{ request('dosen_id')==$d->id?'selected':'' }}>{{ $d->nama }}</option>
        @endforeach
      </select>
      <select name="instansi_id" class="form-control" style="width:190px">
        <option value="">Semua Instansi</option>
        @foreach($instansis as $inst)
          <option value="{{ $inst->id }}" {{ request('instansi_id')==$inst->id?'selected':'' }}>{{ $inst->nama }}</option>
        @endforeach
      </select>
      <button type="submit" class="btn btn-outline btn-sm">Filter</button>
      <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline btn-sm">Reset</a>
    </form>
  </div>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Mahasiswa</th><th>Angkatan</th><th>Instansi</th><th>Dosen Pembimbing</th><th>Progress</th><th>Status</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        @forelse($mahasiswas as $m)
        @php $pct = $m->progressPersen(); @endphp
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              @if($m->foto_profil)
                <img src="{{ $m->fotoUrl() }}" alt="{{ $m->nama }}"
                     style="width:34px;height:34px;border-radius:50%;object-fit:cover;
                            border:2px solid var(--purple-200);flex-shrink:0">
              @else
                <div style="width:34px;height:34px;border-radius:50%;flex-shrink:0;
                            background:var(--purple-50);border:2px solid var(--purple-200);
                            display:flex;align-items:center;justify-content:center;
                            font-size:12px;font-weight:700;color:var(--purple-600)">
                  {{ $m->inisial() }}
                </div>
              @endif
              <div>
                <div style="font-weight:600">{{ $m->nama }}</div>
                <code style="font-size:11px">{{ $m->nim }}</code>
              </div>
            </div>
          </td>
          <td>{{ $m->angkatan }}</td>
          <td class="text-sm">
            @if($m->instansi) {{ $m->instansi->nama }}
            @else <span class="text-muted">–</span>
            @endif
          </td>
          <td class="text-sm">
            @if($m->dosen) {{ $m->dosen->nama }}
            @else <span class="text-muted">–</span>
            @endif
          </td>
          <td style="min-width:110px">
            <div class="prog-wrap" style="height:6px;margin-bottom:3px">
              <div class="prog-bar prog-bar-{{ $pct==100?'green':($pct>0?'amber':'blue') }}" style="width:{{ $pct }}%"></div>
            </div>
            <div class="text-sm text-muted">{{ $pct }}%</div>
          </td>
          <td>
            <span class="badge badge-{{ $m->status }}">{{ ucfirst($m->status) }}</span>
            @if($m->tahap !== 'aktif_kp')
              <br><span class="badge {{ $m->tahap === 'revisi_berkas' ? 'badge-rejected' : 'badge-belum' }}" style="margin-top:3px">{{ $m->tahapLabel() }}</span>
            @endif
          </td>
          <td>
            <div style="display:flex;gap:4px;flex-wrap:wrap">
              <a href="{{ route('admin.mahasiswa.show',$m) }}" class="btn btn-ghost btn-xs">Detail</a>
              <button class="btn btn-outline btn-xs" onclick="openEdit({{ $m->id }},'{{ addslashes($m->nama) }}','{{ $m->angkatan }}','{{ $m->status }}','{{ $m->dosen_id }}','{{ $m->instansi_id }}','{{ $m->tanggal_mulai }}',{{ $m->sudahMencapaiTahap('menunggu_instansi') ? 'true' : 'false' }})">Edit</button>
              <form method="POST" action="{{ route('admin.mahasiswa.destroy',$m) }}" onsubmit="return confirm('Hapus mahasiswa ini?')" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
          <tr><td colspan="7" style="text-align:center;padding:28px;color:#94a3b8">Tidak ada data mahasiswa.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:14px 18px">{{ $mahasiswas->links() }}</div>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal-bg" id="modalTambah">
  <div class="modal-box">
    <div class="modal-title">➕ Tambah Mahasiswa KP</div>

    {{-- ▼ Error Box: tampil jika validasi server gagal --}}
    @if($errors->any())
    <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:6px;padding:10px 14px;margin-bottom:14px">
      <strong style="color:#dc2626;font-size:.85rem">⚠️ Terdapat kesalahan:</strong>
      <ul style="margin:4px 0 0 18px;color:#dc2626;font-size:.82rem">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.mahasiswa.store') }}" id="formTambah" novalidate>
      @csrf
      <div class="form-grid">

        {{-- NIM: wajib angka --}}
        <div class="form-group">
          <label class="form-label">NIM * <small class="text-muted">(angka)</small></label>
          <input type="text" name="nim"
                 class="form-control @error('nim') is-invalid @enderror"
                 placeholder="202112345"
                 value="{{ old('nim') }}"
                 inputmode="numeric"
                 pattern="[0-9]+"
                 title="NIM harus berupa angka saja"
                 required>
          @error('nim')
            <small style="color:#dc2626">{{ $message }}</small>
          @enderror
        </div>

        {{-- Nama --}}
        <div class="form-group">
          <label class="form-label">Nama Lengkap *</label>
          <input type="text" name="nama"
                 class="form-control @error('nama') is-invalid @enderror"
                 value="{{ old('nama') }}"
                 required>
          @error('nama')
            <small style="color:#dc2626">{{ $message }}</small>
          @enderror
        </div>

        {{-- Angkatan: wajib 4 angka --}}
        <div class="form-group">
          <label class="form-label">Angkatan * <small class="text-muted">(4 digit, misal 2021)</small></label>
          <input type="text" name="angkatan"
                 class="form-control @error('angkatan') is-invalid @enderror"
                 placeholder="2021"
                 value="{{ old('angkatan') }}"
                 inputmode="numeric"
                 pattern="[0-9]{4}"
                 maxlength="4"
                 minlength="4"
                 title="Angkatan harus tepat 4 angka"
                 required>
          @error('angkatan')
            <small style="color:#dc2626">{{ $message }}</small>
          @enderror
        </div>

        {{-- No HP: wajib angka jika diisi --}}
        <div class="form-group">
          <label class="form-label">No. HP <small class="text-muted">(angka)</small></label>
          <input type="text" name="no_hp"
                 class="form-control @error('no_hp') is-invalid @enderror"
                 placeholder="08123456789"
                 value="{{ old('no_hp') }}"
                 inputmode="numeric"
                 pattern="[0-9]+"
                 title="No. HP harus berupa angka saja">
          @error('no_hp')
            <small style="color:#dc2626">{{ $message }}</small>
          @enderror
        </div>

        {{-- Email: wajib unik --}}
        <div class="form-group">
          <label class="form-label">Email *</label>
          <input type="email" name="email"
                 class="form-control @error('email') is-invalid @enderror"
                 value="{{ old('email') }}"
                 required>
          @error('email')
            <small style="color:#dc2626">{{ $message }}</small>
          @enderror
        </div>

        {{-- Password: minimal 8 karakter --}}
        <div class="form-group">
          <label class="form-label">Password * <small class="text-muted">(min. 8 karakter)</small></label>
          <input type="password" name="password"
                 class="form-control @error('password') is-invalid @enderror"
                 minlength="8"
                 title="Password minimal 8 karakter"
                 required>
          @error('password')
            <small style="color:#dc2626">{{ $message }}</small>
          @enderror
        </div>

        <div class="form-group">
          <label class="form-label">Dosen Pembimbing</label>
          <select name="dosen_id" class="form-control">
            <option value="">-- Pilih Dosen --</option>
            @foreach($dosens as $d)
              <option value="{{ $d->id }}" {{ old('dosen_id')==$d->id?'selected':'' }}>{{ $d->nama }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Instansi KP</label>
          <select name="instansi_id" class="form-control">
            <option value="">-- Pilih Instansi --</option>
            @foreach($instansis as $inst)
              <option value="{{ $inst->id }}" {{ old('instansi_id')==$inst->id?'selected':'' }}>{{ $inst->nama }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Tanggal Mulai</label>
          <input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai') }}">
        </div>

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
    <div class="modal-title">✏️ Edit Mahasiswa</div>
    <form method="POST" id="editForm">
      @csrf @method('PUT')
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Nama *</label><input type="text" name="nama" id="eNama" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Angkatan *</label><input type="text" name="angkatan" id="eAngkatan" class="form-control" pattern="[0-9]{4}" maxlength="4" required></div>
        <div class="form-group"><label class="form-label">Status</label>
          <select name="status" id="eStatus" class="form-control">
            <option value="proses">Proses</option>
            <option value="seminar">Seminar</option>
            <option value="selesai">Selesai</option>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Tanggal Mulai</label><input type="date" name="tanggal_mulai" id="eTglMulai" class="form-control"></div>
        <div class="form-group"><label class="form-label">Dosen Pembimbing</label>
          <select name="dosen_id" id="eDosen" class="form-control">
            <option value="">-- Pilih --</option>
            @foreach($dosens as $d)<option value="{{ $d->id }}">{{ $d->nama }}</option>@endforeach
          </select>
        </div>
        <div class="form-group"><label class="form-label">Instansi</label>
          <select name="instansi_id" id="eInstansi" class="form-control">
            <option value="">-- Pilih --</option>
            @foreach($instansis as $inst)<option value="{{ $inst->id }}">{{ $inst->nama }}</option>@endforeach
          </select>
        </div>
      </div>
      <div class="alert alert-warning" id="eHintBerkas" style="display:none;margin-top:4px">
        ⚠️ Berkas persyaratan mahasiswa ini belum disetujui (menu <a href="{{ route('admin.persyaratan.index') }}">Persyaratan KP</a>). Dosen/Instansi baru bisa ditentukan setelah berkas disetujui.
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
/* ── Auto buka modal Tambah jika ada error validasi dari server ── */
@if($errors->any())
  document.addEventListener('DOMContentLoaded', function () {
    openModal('modalTambah');
  });
@endif

/* ── Validasi client-side sebelum submit ── */
document.getElementById('formTambah').addEventListener('submit', function (e) {
  const nim      = this.nim.value.trim();
  const angkatan = this.angkatan.value.trim();
  const noHp     = this.no_hp.value.trim();
  const password = this.password.value;
  const errors   = [];

  if (!/^[0-9]+$/.test(nim))            errors.push('NIM harus berupa angka.');
  if (!/^[0-9]{4}$/.test(angkatan))     errors.push('Angkatan harus tepat 4 angka (contoh: 2021).');
  if (noHp && !/^[0-9]+$/.test(noHp))   errors.push('No. HP harus berupa angka.');
  if (password.length < 8)              errors.push('Password minimal 8 karakter.');

  if (errors.length) {
    e.preventDefault();
    let box = document.getElementById('clientErrorBox');
    if (!box) {
      box = document.createElement('div');
      box.id = 'clientErrorBox';
      box.style.cssText = 'background:#fee2e2;border:1px solid #fca5a5;border-radius:6px;padding:10px 14px;margin-bottom:14px';
      this.parentNode.insertBefore(box, this);
    }
    box.innerHTML = '<strong style="color:#dc2626;font-size:.85rem">⚠️ Terdapat kesalahan:</strong>'
      + '<ul style="margin:4px 0 0 18px;color:#dc2626;font-size:.82rem">'
      + errors.map(function(e){ return '<li>' + e + '</li>'; }).join('')
      + '</ul>';
  }
});

/* ── Open Edit Modal ── */
function openEdit(id, nama, angkatan, status, dosenId, instansiId, tglMulai, siapDitempatkan) {
  document.getElementById('editForm').action = `/admin/mahasiswa/${id}`;
  document.getElementById('eNama').value      = nama;
  document.getElementById('eAngkatan').value  = angkatan;
  document.getElementById('eStatus').value    = status;
  document.getElementById('eDosen').value     = dosenId    || '';
  document.getElementById('eInstansi').value  = instansiId || '';
  document.getElementById('eTglMulai').value  = tglMulai   || '';
  document.getElementById('eHintBerkas').style.display = siapDitempatkan ? 'none' : '';
  openModal('modalEdit');
}
</script>
@endpush
@endsection