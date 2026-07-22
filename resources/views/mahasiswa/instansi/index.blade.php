@extends('layouts.app')
@section('title','Daftarkan Instansi')

@push('styles')
<style>
.mode-tabs{display:flex;gap:8px;margin-bottom:20px}
.mode-tab{flex:1;text-align:center;padding:12px;border:2px solid var(--gray-200,#e5e7eb);border-radius:8px;cursor:pointer;font-weight:600;font-size:14px;color:var(--gray-500)}
.mode-tab.active{border-color:var(--blue-500,#3b82f6);background:var(--blue-50,#eff6ff);color:var(--blue-700,#1d4ed8)}
.info-box{background:var(--blue-50,#eff6ff);border:1px solid var(--blue-100,#dbeafe);border-radius:8px;padding:10px 12px;font-size:13px;color:var(--blue-700,#1d4ed8);margin-bottom:16px}
</style>
@endpush

@section('content')
<div class="page-header">
  <h1>Daftarkan Instansi</h1>
  <p>Daftarkan instansi tempat KP dan Pembimbing Lapangan Anda di sana.</p>
</div>

@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">⚠️ {{ session('error') }}</div>@endif
@if(session('db_error'))<div class="alert alert-danger">❌ {{ session('db_error') }}</div>@endif

<div class="info-box">
  💡 Pembimbing Lapangan diperlakukan seperti Dosen Pembimbing — satu akun bisa membimbing beberapa mahasiswa.
  Kalau instansi & PIC Anda kebetulan sama dengan mahasiswa lain yang sudah mendaftar duluan, <strong>pilih dari daftar yang sudah ada</strong> saja, jangan buat akun baru.
</div>

<div class="card">
  <div class="card-body">

    <div class="mode-tabs">
      <div class="mode-tab active" id="tabPilih" onclick="gantiMode('pilih')">🔎 Pilih yang Sudah Ada</div>
      <div class="mode-tab" id="tabBaru" onclick="gantiMode('baru')">➕ Tambah Baru</div>
    </div>

    {{-- ── MODE: PILIH INSTANSI YANG SUDAH ADA ── --}}
    <form method="POST" action="{{ route('mahasiswa.instansi.store') }}" id="formPilih">
      @csrf
      <input type="hidden" name="mode" value="pilih">

      @if($errors->daftarInstansi->any())
        <div class="alert alert-danger">
          ❌ @foreach($errors->daftarInstansi->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
      @endif

      <div class="form-group">
         <label class="form-label">Instansi & Pembimbing Lapangan *</label>
        <select name="instansi_id" class="form-control" required>
          <option value="">— Pilih Instansi —</option>
          @foreach($instansis as $inst)
            <option value="{{ $inst->id }}" {{ old('instansi_id') == $inst->id ? 'selected' : '' }}>
              {{ $inst->nama }} — PIC: {{ $inst->kontak_person ?? '-' }}
            </option>
          @endforeach
        </select>
        @if($instansis->isEmpty())
          <div style="font-size:12px;color:var(--gray-500);margin-top:6px">
            Belum ada instansi yang terdaftar. Silakan gunakan tab "Tambah Baru".
          </div>
        @endif
      </div>

      <div style="text-align:right;margin-top:18px">
        <button type="submit" class="btn btn-primary" {{ $instansis->isEmpty() ? 'disabled' : '' }}>Daftarkan</button>
      </div>
    </form>

    {{-- ── MODE: TAMBAH INSTANSI BARU ── --}}
   <form method="POST" action="{{ route('mahasiswa.instansi.store') }}" id="formBaru" style="display:none">
      @csrf
      <input type="hidden" name="mode" value="baru">

      @if($errors->daftarInstansiBaru->any())
        <div class="alert alert-danger">
          ❌ @foreach($errors->daftarInstansiBaru->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
      @endif

      <div class="form-group">
        <label class="form-label">Nama Instansi *</label>
       <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" placeholder="cth: PT Teknologi Nusantara" required>
      </div>

      <div class="form-group">
        <label class="form-label">Bidang</label>
        <input type="text" name="bidang" class="form-control" value="{{ old('bidang') }}" placeholder="cth: Teknologi Informasi">
      </div>

      <div class="form-group">
        <label class="form-label">Alamat</label>
        <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat lengkap instansi">{{ old('alamat') }}</textarea>
      </div>

      <hr style="margin:20px 0;border:none;border-top:1px solid var(--gray-100,#f1f5f9)">
      <h4 style="font-size:14px;margin-bottom:12px">Titik Lokasi Instansi (untuk Absensi GPS)</h4>
      <div class="info-box">
        📍 Cari lokasi instansi di aplikasi/situs Google Maps, tekan tombol <strong>"Bagikan"</strong>, salin linknya, lalu tempel di bawah ini. Anda <strong>tidak perlu sedang berada di lokasi</strong> untuk mengisi ini.
      </div>

      <div class="form-group">
        <label class="form-label">Link Google Maps Lokasi Instansi *</label>
        <div style="display:flex;gap:8px">
          <input type="text" id="linkMaps" class="form-control" placeholder="https://maps.app.goo.gl/...">
          <button type="button" class="btn btn-outline btn-sm" onclick="ambilDariLink()" id="btnLink">Ambil Koordinat</button>
        </div>
        <small id="linkInfo" style="display:block;margin-top:6px;font-size:12px"></small>
      </div>

      <div class="form-group">
        <label class="form-label" style="font-size:12px;color:var(--gray-500)">Atau, kalau kebetulan Anda sedang di lokasi instansi sekarang:</label>
        <button type="button" class="btn btn-outline btn-sm" onclick="ambilLokasiSaya()" id="btnLokasi">📡 Gunakan Lokasi Saya Sekarang</button>
        <small id="lokasiInfo" style="display:block;margin-top:6px;font-size:12px"></small>
      </div>

      <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div class="form-group">
          <label class="form-label">Latitude *</label>
          <input type="text" name="latitude" id="fLat" class="form-control @error('latitude','daftarInstansiBaru') is-invalid @enderror"
            value="{{ old('latitude') }}" placeholder="-5.4291839" readonly>
          @error('latitude','daftarInstansiBaru')<small style="color:#dc2626">{{ $message }}</small>@enderror
        </div>
        <div class="form-group">
          <label class="form-label">Longitude *</label>
          <input type="text" name="longitude" id="fLng" class="form-control @error('longitude','daftarInstansiBaru') is-invalid @enderror"
            value="{{ old('longitude') }}" placeholder="105.2618658" readonly>
          @error('longitude','daftarInstansiBaru')<small style="color:#dc2626">{{ $message }}</small>@enderror
        </div>
      </div>
      <div style="font-size:12px;color:var(--gray-500);margin-top:-8px;margin-bottom:16px">
        Terisi otomatis dari link/GPS di atas. Bisa diedit manual kalau perlu.
      </div>

      <hr style="margin:20px 0;border:none;border-top:1px solid var(--gray-100,#f1f5f9)">
      <h4 style="font-size:14px;margin-bottom:12px">Data Pembimbing Lapangan (PIC)</h4>

      <div class="form-group">
        <label class="form-label">Nama Pembimbing Lapangan *</label>
        <input type="text" name="kontak_person" class="form-control" value="{{ old('kontak_person') }}" placeholder="Nama PIC / Pembimbing Lapangan" required>
      </div>

      <div class="form-group">
        <label class="form-label">No. HP Pembimbing Lapangan</label>
        <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx">
      </div>

      <div class="form-group">
        <label class="form-label">Email (untuk akun login Pembimbing Lapangan) *</label>
        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="email@instansi.com" required>
      </div>

      <div class="form-group">
        <label class="form-label">Password Awal *</label>
        <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter" required>
        <div style="font-size:12px;color:var(--gray-500);margin-top:4px">
          Sampaikan password ini ke pembimbing lapangan Anda. Mereka akan diminta menggantinya saat login pertama.
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Konfirmasi Password *</label>
        <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password" required>
      </div>

      <div style="text-align:right;margin-top:18px">
        <button type="submit" class="btn btn-primary">Daftarkan & Buat Akun</button>
      </div>
    </form>

  </div>
</div>

<script>
function gantiMode(mode) {
  const isPilih = mode === 'pilih';
  document.getElementById('tabPilih').classList.toggle('active', isPilih);
  document.getElementById('tabBaru').classList.toggle('active', !isPilih);
  document.getElementById('formPilih').style.display = isPilih ? 'block' : 'none';
  document.getElementById('formBaru').style.display   = isPilih ? 'none' : 'block';
}
@if($errors->daftarInstansiBaru->any())
  gantiMode('baru');
@endif

/* ── Ambil lokasi GPS perangkat saat ini ── */
function ambilLokasiSaya() {
  const info = document.getElementById('lokasiInfo');
  const btn  = document.getElementById('btnLokasi');

  if (!navigator.geolocation) {
    info.style.color = '#dc2626';
    info.textContent = '❌ Perangkat/browser tidak mendukung GPS.';
    return;
  }

  btn.disabled = true;
  info.style.color = '#64748b';
  info.textContent = '📡 Mendeteksi lokasi...';

  navigator.geolocation.getCurrentPosition(pos => {
    const lat = pos.coords.latitude.toFixed(7);
    const lng = pos.coords.longitude.toFixed(7);
    const akurasi = Math.round(pos.coords.accuracy);
    document.getElementById('fLat').value = lat;
    document.getElementById('fLng').value = lng;
    info.style.color = '#16a34a';
    info.textContent = `✅ Lokasi terisi (akurasi ±${akurasi}m). Periksa kembali sebelum menyimpan.`;
    btn.disabled = false;
  }, () => {
    info.style.color = '#dc2626';
    info.textContent = '❌ Gagal mengambil lokasi. Pastikan GPS aktif dan izin lokasi diberikan, atau isi manual lewat link Google Maps.';
    btn.disabled = false;
  }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
}

/* ── Ambil koordinat dari link Google Maps yang ditempel ── */
function parseLatLngFromMapsLink(url) {
  let m = url.match(/@(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)/);
  if (m) return { lat: m[1], lng: m[2] };
  m = url.match(/[?&](?:q|query)=(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)/);
  if (m) return { lat: m[1], lng: m[2] };
  m = url.match(/[?&]ll=(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)/);
  if (m) return { lat: m[1], lng: m[2] };
  m = url.match(/!3d(-?\d{1,3}\.\d+)!4d(-?\d{1,3}\.\d+)/);
  if (m) return { lat: m[1], lng: m[2] };
  return null;
}

async function ambilDariLink() {
  const info = document.getElementById('linkInfo');
  const btn  = document.getElementById('btnLink');
  const link = document.getElementById('linkMaps').value.trim();

  if (!link) {
    info.style.color = '#dc2626';
    info.textContent = '❌ Tempelkan link Google Maps terlebih dahulu.';
    return;
  }

  const langsung = parseLatLngFromMapsLink(link);
  if (langsung) {
    document.getElementById('fLat').value = langsung.lat;
    document.getElementById('fLng').value = langsung.lng;
    info.style.color = '#16a34a';
    info.textContent = `✅ Koordinat ditemukan: ${langsung.lat}, ${langsung.lng}.`;
    return;
  }

  btn.disabled = true;
  info.style.color = '#64748b';
  info.textContent = '🔗 Membaca link...';

  try {
    const res = await fetch('{{ route("mahasiswa.instansi.resolveLokasi") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({ link }),
    });
    const data = await res.json();

    if (res.ok && data.success) {
      document.getElementById('fLat').value = data.latitude;
      document.getElementById('fLng').value = data.longitude;
      info.style.color = '#16a34a';
      info.textContent = `✅ Koordinat ditemukan: ${data.latitude}, ${data.longitude}.`;
    } else {
      info.style.color = '#dc2626';
      info.textContent = '❌ ' + (data.message || 'Koordinat tidak ditemukan pada link tersebut.');
    }
  } catch (err) {
    info.style.color = '#dc2626';
    info.textContent = '❌ Gagal menghubungi server. Coba lagi.';
  }
  btn.disabled = false;
}
</script>
@endsection
