@extends('layouts.app')
@section('title','Data Instansi')
@section('content')

<div class="page-header page-header-row">
  <div><h1>Data Instansi / Lokasi KP</h1><p>{{ $instansis->count() }} instansi terdaftar</p></div>
  <button class="btn btn-primary" onclick="openModal('modalTambah')">+ Tambah Instansi</button>
</div>

{{-- Flash success --}}
@if(session('success'))
  <div style="background:#dcfce7;border:1px solid #86efac;border-radius:6px;padding:10px 14px;margin-bottom:14px;color:#166534">
    ✅ {{ session('success') }}
  </div>
@endif

{{-- Flash error DB (di LUAR modal agar selalu terlihat) --}}
@if(session('db_error'))
  <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:6px;padding:10px 14px;margin-bottom:14px;color:#dc2626">
    ❌ {{ session('db_error') }}
  </div>
@endif

{{-- Filter --}}
<div class="card" style="margin-bottom:16px">
  <div class="card-body" style="padding:14px 18px">
    <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Cari nama instansi..." class="form-control" style="width:240px">
      <button type="submit" class="btn btn-outline btn-sm">Filter</button>
      <a href="{{ route('admin.instansi.index') }}" class="btn btn-outline btn-sm">Reset</a>
    </form>
  </div>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Nama Instansi</th><th>Bidang</th><th>Kontak Person</th><th>Lokasi</th><th>Mhs KP</th><th>Aksi</th></tr></thead>
      <tbody>
        @forelse($instansis as $i => $inst)
        <tr>
          <td class="text-muted">{{ $i+1 }}</td>
          <td>
            <strong>{{ $inst->nama }}</strong>
            @if($inst->alamat)<div class="text-sm text-muted">{{ Str::limit($inst->alamat,50) }}</div>@endif
          </td>
          <td class="text-sm">{{ $inst->bidang ?? '–' }}</td>
          <td class="text-sm text-muted">
            {{ $inst->kontak_person ?? '–' }}
            @if($inst->no_hp)<br><span>{{ $inst->no_hp }}</span>@endif
          </td>
          <td class="text-sm">
            @if($inst->latitude && $inst->longitude)
              <span class="badge badge-selesai">📍 Diatur ({{ $inst->radius_absen ?? 100 }}m)</span>
            @else
              <span class="badge badge-belum">Belum diatur</span>
            @endif
          </td>
          <td><span class="badge badge-proses">{{ $inst->mahasiswas->count() }} mhs</span></td>
          <td>
            <div style="display:flex;gap:4px">
              <a href="{{ route('admin.instansi.show',$inst) }}" class="btn btn-ghost btn-xs">Detail</a>
              <button class="btn btn-outline btn-xs"
                onclick="openEdit({{ $inst->id }},'{{ addslashes($inst->nama) }}','{{ addslashes($inst->bidang) }}','{{ addslashes($inst->alamat) }}','{{ addslashes($inst->kontak_person) }}','{{ $inst->no_hp }}','{{ $inst->latitude }}','{{ $inst->longitude }}','{{ $inst->radius_absen ?? 100 }}')">Edit</button>
              <form method="POST" action="{{ route('admin.instansi.destroy',$inst) }}"
                onsubmit="return confirm('Hapus instansi ini?')" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
          <tr><td colspan="7" style="text-align:center;padding:28px;color:#94a3b8">Belum ada data instansi.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal-bg" id="modalTambah">
  <div class="modal-box">
    <div class="modal-title">🏢 Tambah Instansi</div>

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

    <form method="POST" action="{{ route('admin.instansi.store') }}" id="formTambah" novalidate>
      @csrf

      <div class="form-group">
        <label class="form-label">Nama Instansi *</label>
        <input type="text" name="nama"
               class="form-control @error('nama','tambah') is-invalid @enderror"
               value="{{ old('nama') }}" required>
        @error('nama','tambah')<small style="color:#dc2626">{{ $message }}</small>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">Bidang Usaha</label>
        <input type="text" name="bidang"
               class="form-control @error('bidang','tambah') is-invalid @enderror"
               placeholder="Software Development"
               value="{{ old('bidang') }}">
        @error('bidang','tambah')<small style="color:#dc2626">{{ $message }}</small>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">Alamat</label>
        <textarea name="alamat"
                  class="form-control @error('alamat','tambah') is-invalid @enderror"
                  rows="2">{{ old('alamat') }}</textarea>
        @error('alamat','tambah')<small style="color:#dc2626">{{ $message }}</small>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">Kontak Person</label>
        <input type="text" name="kontak_person"
               class="form-control @error('kontak_person','tambah') is-invalid @enderror"
               value="{{ old('kontak_person') }}">
        @error('kontak_person','tambah')<small style="color:#dc2626">{{ $message }}</small>@enderror
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

      <hr class="divider">
      <div class="form-group" style="margin-bottom:8px">
        <label class="form-label">📍 Lokasi Titik Absen</label>
        <p class="form-hint" style="margin:0 0 8px">Wajib diisi agar mahasiswa bisa absen di instansi ini. Tempelkan link Google Maps lokasi instansi (tombol "Bagikan" → "Salin link" di Google Maps), atau gunakan tombol GPS jika Anda sedang berada di lokasi instansi.</p>

        <div style="display:flex;gap:6px;margin-bottom:4px">
          <input type="text" id="tLinkMaps" class="form-control"
                 placeholder="Tempel link Google Maps di sini, mis. https://maps.app.goo.gl/xxxx">
          <button type="button" class="btn btn-outline btn-sm" onclick="ambilDariLink('tambah')" id="btnLinkTambah" style="white-space:nowrap">🔗 Ambil dari Link</button>
        </div>
        <small id="linkTambahInfo" class="form-hint" style="display:block;margin-bottom:10px"></small>

        <button type="button" class="btn btn-outline btn-sm" onclick="ambilLokasiSaya('tambah')" id="btnLokasiTambah">📡 Gunakan Lokasi Saya Sekarang</button>
        <small id="lokasiTambahInfo" class="form-hint" style="display:block;margin-top:4px"></small>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Latitude</label>
          <input type="text" name="latitude" id="tLat"
                 class="form-control @error('latitude','tambah') is-invalid @enderror"
                 placeholder="-5.4291839" value="{{ old('latitude') }}">
          @error('latitude','tambah')<small style="color:#dc2626">{{ $message }}</small>@enderror
        </div>
        <div class="form-group">
          <label class="form-label">Longitude</label>
          <input type="text" name="longitude" id="tLng"
                 class="form-control @error('longitude','tambah') is-invalid @enderror"
                 placeholder="105.2618658" value="{{ old('longitude') }}">
          @error('longitude','tambah')<small style="color:#dc2626">{{ $message }}</small>@enderror
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Radius Toleransi Absen (meter)</label>
        <input type="number" name="radius_absen"
               class="form-control @error('radius_absen','tambah') is-invalid @enderror"
               min="10" max="5000" placeholder="100" value="{{ old('radius_absen', 100) }}">
        <p class="form-hint">Jarak maksimal dari titik koordinat di atas agar absen dianggap valid. Default 100 meter.</p>
        @error('radius_absen','tambah')<small style="color:#dc2626">{{ $message }}</small>@enderror
      </div>
      <hr class="divider">

      <div class="form-group">
        <label class="form-label">Email Login *</label>
        <input type="email" name="email"
               class="form-control @error('email','tambah') is-invalid @enderror"
               value="{{ old('email') }}" required>
        @error('email','tambah')<small style="color:#dc2626">{{ $message }}</small>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">Password * <small class="text-muted">(min. 8 karakter)</small></label>
        <input type="password" name="password"
               class="form-control @error('password','tambah') is-invalid @enderror"
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
    <div class="modal-title">✏️ Edit Instansi</div>

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
        <label class="form-label">Nama *</label>
        <input type="text" name="nama" id="eNama"
               class="form-control @error('nama','edit') is-invalid @enderror"
               required>
        @error('nama','edit')<small style="color:#dc2626">{{ $message }}</small>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">Bidang</label>
        <input type="text" name="bidang" id="eBidang"
               class="form-control @error('bidang','edit') is-invalid @enderror">
        @error('bidang','edit')<small style="color:#dc2626">{{ $message }}</small>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">Alamat</label>
        <textarea name="alamat" id="eAlamat"
                  class="form-control @error('alamat','edit') is-invalid @enderror"
                  rows="2"></textarea>
        @error('alamat','edit')<small style="color:#dc2626">{{ $message }}</small>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">Kontak Person</label>
        <input type="text" name="kontak_person" id="eKontak"
               class="form-control @error('kontak_person','edit') is-invalid @enderror">
        @error('kontak_person','edit')<small style="color:#dc2626">{{ $message }}</small>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">No. HP <small class="text-muted">(angka)</small></label>
        <input type="text" name="no_hp" id="eHp"
               class="form-control @error('no_hp','edit') is-invalid @enderror"
               inputmode="numeric" pattern="[0-9]+">
        @error('no_hp','edit')<small style="color:#dc2626">{{ $message }}</small>@enderror
      </div>

      <hr class="divider">
      <div class="form-group" style="margin-bottom:8px">
        <label class="form-label">📍 Lokasi Titik Absen</label>
        <p class="form-hint" style="margin:0 0 8px">Wajib diisi agar mahasiswa bisa absen di instansi ini. Tempelkan link Google Maps lokasi instansi, atau gunakan tombol GPS jika Anda sedang berada di lokasi.</p>

        <div style="display:flex;gap:6px;margin-bottom:4px">
          <input type="text" id="eLinkMaps" class="form-control"
                 placeholder="Tempel link Google Maps di sini, mis. https://maps.app.goo.gl/xxxx">
          <button type="button" class="btn btn-outline btn-sm" onclick="ambilDariLink('edit')" id="btnLinkEdit" style="white-space:nowrap">🔗 Ambil dari Link</button>
        </div>
        <small id="linkEditInfo" class="form-hint" style="display:block;margin-bottom:10px"></small>

        <button type="button" class="btn btn-outline btn-sm" onclick="ambilLokasiSaya('edit')" id="btnLokasiEdit">📡 Gunakan Lokasi Saya Sekarang</button>
        <small id="lokasiEditInfo" class="form-hint" style="display:block;margin-top:4px"></small>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Latitude</label>
          <input type="text" name="latitude" id="eLat"
                 class="form-control @error('latitude','edit') is-invalid @enderror"
                 placeholder="-5.4291839">
          @error('latitude','edit')<small style="color:#dc2626">{{ $message }}</small>@enderror
        </div>
        <div class="form-group">
          <label class="form-label">Longitude</label>
          <input type="text" name="longitude" id="eLng"
                 class="form-control @error('longitude','edit') is-invalid @enderror"
                 placeholder="105.2618658">
          @error('longitude','edit')<small style="color:#dc2626">{{ $message }}</small>@enderror
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Radius Toleransi Absen (meter)</label>
        <input type="number" name="radius_absen" id="eRadius"
               class="form-control @error('radius_absen','edit') is-invalid @enderror"
               min="10" max="5000" placeholder="100">
        @error('radius_absen','edit')<small style="color:#dc2626">{{ $message }}</small>@enderror
      </div>
      <hr class="divider">

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalEdit')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
/* ── Auto-buka modal TAMBAH jika ada error validasi ── */
@if($errors->tambah->any())
window.addEventListener('load', function () { openModal('modalTambah'); });
@endif

/* ── Auto-buka modal EDIT jika ada error validasi edit ── */
@if($errors->edit->any() && session('edit_id'))
window.addEventListener('load', function () {
    openEdit(
        {{ session('edit_id') }},
        '{{ addslashes(old('nama', '')) }}',
        '{{ addslashes(old('bidang', '')) }}',
        '{{ addslashes(old('alamat', '')) }}',
        '{{ addslashes(old('kontak_person', '')) }}',
        '{{ old('no_hp', '') }}',
        '{{ old('latitude', '') }}',
        '{{ old('longitude', '') }}',
        '{{ old('radius_absen', 100) }}'
    );
});
@endif

/* ── Validasi client-side sebelum submit ── */
document.getElementById('formTambah').addEventListener('submit', function (e) {
    const noHp = this.no_hp.value.trim();
    const pwd  = this.password.value;
    const errs = [];

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
function openEdit(id, nama, bidang, alamat, kontak, hp, lat, lng, radius) {
    document.getElementById('editForm').action = `/admin/instansi/${id}`;
    document.getElementById('eNama').value    = nama    || '';
    document.getElementById('eBidang').value  = bidang  || '';
    document.getElementById('eAlamat').value  = alamat  || '';
    document.getElementById('eKontak').value  = kontak  || '';
    document.getElementById('eHp').value      = hp      || '';
    document.getElementById('eLat').value     = lat     || '';
    document.getElementById('eLng').value     = lng     || '';
    document.getElementById('eRadius').value  = radius  || 100;
    document.getElementById('eLinkMaps').value = '';
    document.getElementById('linkEditInfo').textContent = '';
    document.getElementById('lokasiEditInfo').textContent = '';
    openModal('modalEdit');
}

/* ── Ambil koordinat GPS perangkat saat ini dan isi ke form ── */
function ambilLokasiSaya(mode) {
    const suffix = mode === 'tambah' ? 't' : 'e';
    const infoEl  = document.getElementById('lokasi' + (mode === 'tambah' ? 'Tambah' : 'Edit') + 'Info');
    const btnEl   = document.getElementById('btnLokasi' + (mode === 'tambah' ? 'Tambah' : 'Edit'));

    if (!navigator.geolocation) {
        infoEl.textContent = '❌ Perangkat/browser tidak mendukung GPS.';
        infoEl.style.color = '#dc2626';
        return;
    }

    btnEl.disabled = true;
    infoEl.style.color = '#64748b';
    infoEl.textContent = '📡 Mendeteksi lokasi...';

    navigator.geolocation.getCurrentPosition(pos => {
        const lat = pos.coords.latitude.toFixed(7);
        const lng = pos.coords.longitude.toFixed(7);
        const akurasi = Math.round(pos.coords.accuracy);
        document.getElementById(suffix + 'Lat').value = lat;
        document.getElementById(suffix + 'Lng').value = lng;
        infoEl.style.color = '#16a34a';
        infoEl.textContent = `✅ Lokasi terisi (akurasi ±${akurasi}m). Periksa kembali sebelum menyimpan.`;
        btnEl.disabled = false;
    }, () => {
        infoEl.style.color = '#dc2626';
        infoEl.textContent = '❌ Gagal mengambil lokasi. Pastikan GPS aktif dan izin lokasi diberikan, atau isi manual lewat Google Maps.';
        btnEl.disabled = false;
    }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
}

/* ── Ambil koordinat dari link Google Maps yang ditempel user ── */
async function ambilDariLink(mode) {
    const suffix  = mode === 'tambah' ? 't' : 'e';
    const capSuf  = mode === 'tambah' ? 'Tambah' : 'Edit';
    const linkEl  = document.getElementById(suffix + 'LinkMaps');
    const infoEl  = document.getElementById('link' + capSuf + 'Info');
    const btnEl   = document.getElementById('btnLink' + capSuf);
    const link    = linkEl.value.trim();

    if (!link) {
        infoEl.style.color = '#dc2626';
        infoEl.textContent = '❌ Tempelkan link Google Maps terlebih dahulu.';
        return;
    }

    // 1) Coba parse langsung di browser dulu (cepat, cukup untuk link Maps versi panjang)
    const langsung = parseLatLngFromMapsLink(link);
    if (langsung) {
        isiKoordinat(mode, langsung.lat, langsung.lng);
        infoEl.style.color = '#16a34a';
        infoEl.textContent = `✅ Koordinat ditemukan: ${langsung.lat}, ${langsung.lng}. Periksa kembali sebelum menyimpan.`;
        return;
    }

    // 2) Link pendek (maps.app.goo.gl / goo.gl) → minta server menelusuri redirect-nya
    btnEl.disabled = true;
    infoEl.style.color = '#64748b';
    infoEl.textContent = '🔗 Membaca link...';

    try {
        const res = await fetch('{{ route("admin.instansi.resolveLokasi") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({ link }),
        });
        const data = await res.json();

        if (res.ok && data.success) {
            isiKoordinat(mode, data.latitude, data.longitude);
            infoEl.style.color = '#16a34a';
            infoEl.textContent = `✅ Koordinat ditemukan: ${data.latitude}, ${data.longitude}. Periksa kembali sebelum menyimpan.`;
        } else {
            infoEl.style.color = '#dc2626';
            infoEl.textContent = '❌ ' + (data.message || 'Koordinat tidak ditemukan pada link tersebut.');
        }
    } catch (err) {
        infoEl.style.color = '#dc2626';
        infoEl.textContent = '❌ Gagal memproses link. Periksa koneksi internet Anda.';
    } finally {
        btnEl.disabled = false;
    }
}

function isiKoordinat(mode, lat, lng) {
    const suffix = mode === 'tambah' ? 't' : 'e';
    document.getElementById(suffix + 'Lat').value = lat;
    document.getElementById(suffix + 'Lng').value = lng;
}

/* Parse berbagai format link Google Maps: @lat,lng / ?q=lat,lng / ?ll=lat,lng / !3dlat!4dlng */
function parseLatLngFromMapsLink(url) {
    let m;
    if ((m = url.match(/@(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)/)))                       return { lat: m[1], lng: m[2] };
    if ((m = url.match(/[?&](?:q|query)=(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)/)))        return { lat: m[1], lng: m[2] };
    if ((m = url.match(/[?&]ll=(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)/)))                 return { lat: m[1], lng: m[2] };
    if ((m = url.match(/!3d(-?\d{1,3}\.\d+)!4d(-?\d{1,3}\.\d+)/)))                   return { lat: m[1], lng: m[2] };
    return null;
}

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) return meta.content;
    const input = document.querySelector('input[name="_token"]');
    return input ? input.value : '';
}
</script>
@endpush
@endsection