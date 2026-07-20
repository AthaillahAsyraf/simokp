@extends('layouts.app')

@section('title', 'Absensi KP')

@push('styles')
<style>
.badge-valid{background:var(--green-100);color:var(--green-700)}
.badge-invalid{background:var(--red-100);color:var(--red-600)}

.absen-today{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media (max-width:680px){.absen-today{grid-template-columns:1fr}}
.absen-slot{border:1.5px solid var(--gray-200);border-radius:10px;padding:18px;text-align:center;background:var(--gray-50)}
.absen-slot.done{background:var(--purple-50);border-color:var(--purple-100)}
.absen-slot .label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--gray-500)}
.absen-slot .jam{font-family:'JetBrains Mono',monospace;font-size:28px;font-weight:800;color:var(--gray-900);margin:6px 0}
.absen-slot .btn{margin-top:4px}

.cam-wrap{position:relative;background:#000;border-radius:10px;overflow:hidden;aspect-ratio:4/3;max-height:320px}
.cam-wrap video,.cam-wrap canvas{width:100%;height:100%;object-fit:cover;display:block}

.loc-info{display:flex;align-items:center;gap:8px;padding:9px 12px;border-radius:8px;font-size:12px;font-weight:600;margin-bottom:12px}
.loc-info.wait{background:var(--gray-100);color:var(--gray-500)}
.loc-info.ok{background:var(--green-50);color:var(--green-700)}
.loc-info.bad{background:var(--red-50);color:var(--red-600)}
.cam-actions{display:flex;gap:8px;margin-top:10px}
.foto-thumb{width:40px;height:40px;border-radius:6px;object-fit:cover;cursor:pointer;border:1px solid var(--gray-200)}
</style>
@endpush

@section('content')
<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1>📍 Absensi KP</h1>
      <p>{{ now()->translatedFormat('l, d F Y') }} — absen wajib dilakukan di lokasi instansi</p>
    </div>
  </div>
</div>

@if($errors->absenMasuk->any() || $errors->absenKeluar->any())
  <div class="alert alert-danger">
    ⚠️ {{ $errors->absenMasuk->first() ?: $errors->absenKeluar->first() }}
  </div>
@endif

@if(!$instansi)
  <div class="alert alert-danger">
    ⚠️ Anda belum terdaftar pada instansi KP manapun. Hubungi admin untuk mendaftarkan instansi Anda.
  </div>
@elseif(is_null($instansi->latitude) || is_null($instansi->longitude))
  <div class="alert alert-warning">
    ⚠️ Titik lokasi instansi <strong>{{ $instansi->nama }}</strong> belum diatur oleh admin, sehingga absen belum dapat dilakukan. Hubungi admin.
  </div>
@else

  <div class="card">
    <div class="card-header">
      <div>
        <h3>🏢 {{ $instansi->nama }}</h3>
        <p>{{ $instansi->alamat ?? '-' }}</p>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <h3>🕐 Absensi Hari Ini</h3>
        <p>Setiap absen direkam dengan lokasi GPS, foto langsung dari kamera, dan jam server. Durasi kerja minimal <strong>{{ \App\Models\Absensi::DURASI_MINIMAL_JAM }} jam/hari</strong> sesuai Panduan Kerja Praktik.</p>
      </div>
    </div>
    <div class="card-body">
      <div class="absen-today">
        <div class="absen-slot {{ $absensiHariIni?->jam_masuk ? 'done' : '' }}">
          <div class="label">Absen Masuk</div>
          <div class="jam">{{ $absensiHariIni?->jam_masuk ? \Carbon\Carbon::parse($absensiHariIni->jam_masuk)->format('H:i') : '--:--' }}</div>
          @if($absensiHariIni?->jam_masuk)
            <span class="badge {{ $absensiHariIni->status_masuk === 'valid' ? 'badge-valid' : 'badge-invalid' }}">
              {{ $absensiHariIni->status_masuk === 'valid' ? '✓ Di lokasi ('.$absensiHariIni->jarak_masuk.'m)' : '⚠ '.$absensiHariIni->jarak_masuk.'m dari lokasi' }}
            </span>
          @else
            <div><button type="button" class="btn btn-primary btn-sm" onclick="openAbsenModal('masuk')">📸 Absen Masuk</button></div>
          @endif
        </div>

        <div class="absen-slot {{ $absensiHariIni?->jam_keluar ? 'done' : '' }}">
          <div class="label">Absen Pulang</div>
          <div class="jam">{{ $absensiHariIni?->jam_keluar ? \Carbon\Carbon::parse($absensiHariIni->jam_keluar)->format('H:i') : '--:--' }}</div>
          @if($absensiHariIni?->jam_keluar)
            <span class="badge {{ $absensiHariIni->status_keluar === 'valid' ? 'badge-valid' : 'badge-invalid' }}">
              {{ $absensiHariIni->status_keluar === 'valid' ? '✓ Di lokasi ('.$absensiHariIni->jarak_keluar.'m)' : '⚠ '.$absensiHariIni->jarak_keluar.'m dari lokasi' }}
            </span>
          @elseif($absensiHariIni?->jam_masuk)
            <div><button type="button" class="btn btn-primary btn-sm" onclick="openAbsenModal('keluar')">📸 Absen Pulang</button></div>
          @else
            <span class="text-muted text-sm">Absen masuk dahulu</span>
          @endif
        </div>
      </div>
    </div>
  </div>
@endif

<div class="card">
  <div class="card-header">
    <div><h3>📋 Riwayat Absensi</h3></div>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Tanggal</th><th>Masuk</th><th>Jarak</th><th>Foto</th>
          <th>Pulang</th><th>Jarak</th><th>Foto</th><th>Durasi</th><th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($riwayat as $r)
        <tr>
          <td>{{ \Carbon\Carbon::parse($r->tanggal)->translatedFormat('d M Y') }}</td>
          <td>{{ $r->jam_masuk ? \Carbon\Carbon::parse($r->jam_masuk)->format('H:i') : '-' }}</td>
          <td>{{ $r->jarak_masuk !== null ? $r->jarak_masuk.'m' : '-' }}</td>
          <td>
            @if($r->foto_masuk)
              <img src="{{ asset('storage/'.$r->foto_masuk) }}" class="foto-thumb" onclick="window.open(this.src,'_blank')">
            @else - @endif
          </td>
          <td>{{ $r->jam_keluar ? \Carbon\Carbon::parse($r->jam_keluar)->format('H:i') : '-' }}</td>
          <td>{{ $r->jarak_keluar !== null ? $r->jarak_keluar.'m' : '-' }}</td>
          <td>
            @if($r->foto_keluar)
              <img src="{{ asset('storage/'.$r->foto_keluar) }}" class="foto-thumb" onclick="window.open(this.src,'_blank')">
            @else - @endif
          </td>
          <td>
            @if($r->durasi_jam !== null)
              <span style="font-weight:600;{{ $r->isDurasiKurang() ? 'color:#dc2626' : '' }}">
                {{ number_format($r->durasi_jam, 1) }} jam
              </span>
            @else - @endif
          </td>
          <td>
            @if($r->perluDitinjau())
              <span class="badge badge-invalid" title="{{ implode(' • ', $r->alasanPerluTinjau()) }}">Perlu Ditinjau</span>
            @elseif($r->jam_masuk && $r->jam_keluar)
              <span class="badge badge-valid">Lengkap</span>
            @else
              <span class="badge badge-proses">Berlangsung</span>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="9"><div class="empty-state"><div class="icon">📭</div>Belum ada riwayat absensi</div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($riwayat->hasPages())
    <div class="card-body">{{ $riwayat->links() }}</div>
  @endif
</div>

{{-- ── Modal kamera + lokasi ─────────────────────────────────── --}}
<div class="modal-bg" id="modalAbsen">
  <div class="modal-box" style="width:440px">
    <div class="modal-title" id="modalAbsenTitle">Absen Masuk</div>

    <div class="loc-info wait" id="locStatus">📡 Mendeteksi lokasi GPS Anda...</div>

    {{-- ← BARU: info durasi kerja live, hanya tampil saat absen pulang --}}
    <div class="loc-info" id="durasiInfo" style="display:none"></div>

    <div class="cam-wrap">
      <video id="camVideo" autoplay playsinline muted></video>
      <canvas id="camCanvas" style="display:none"></canvas>
    </div>
    <p class="form-hint" id="camHint" style="margin-top:6px">Pastikan wajah Anda terlihat jelas pada foto.</p>

    <div class="cam-actions">
      <button type="button" class="btn btn-outline" id="btnRetake" style="display:none" onclick="retakePhoto()">🔄 Ambil Ulang</button>
      <button type="button" class="btn btn-primary" id="btnCapture" onclick="capturePhoto()">📸 Ambil Foto</button>
    </div>

    {{-- ← BARU: catatan kegiatan (rencana saat masuk, realisasi saat pulang) --}}
    <div class="form-group" style="margin-top:14px">
      <label class="form-label" style="font-weight:600" id="catatanLabel">📋 Rencana Kegiatan Hari Ini *</label>
      <textarea id="inputCatatan" class="form-control" rows="3" minlength="10" maxlength="1000"
        placeholder="Tuliskan rencana kegiatan Anda hari ini..." oninput="updateSubmitState()"></textarea>
      <p class="form-hint" id="catatanHint">Minimal 10 karakter.</p>
    </div>

    <div class="modal-footer">
      <button type="button" class="btn btn-outline" onclick="closeAbsenModal()">Batal</button>
      <button type="button" class="btn btn-success" id="btnSubmitAbsen" disabled onclick="submitAbsen()">Kirim Absen</button>
    </div>
  </div>
</div>

{{-- Form tersembunyi — diisi & disubmit oleh JS setelah foto+lokasi siap --}}
<form id="formAbsenMasuk" action="{{ route('mahasiswa.absensi.checkin') }}" method="POST" enctype="multipart/form-data" style="display:none">
  @csrf
  <input type="hidden" name="latitude" id="latMasuk">
  <input type="hidden" name="longitude" id="lngMasuk">
  <input type="hidden" name="accuracy" id="accMasuk">
  <input type="file" name="foto" id="fotoMasuk">
  <input type="hidden" name="rencana" id="rencanaMasuk">
</form>
<form id="formAbsenKeluar" action="{{ route('mahasiswa.absensi.checkout') }}" method="POST" enctype="multipart/form-data" style="display:none">
  @csrf
  <input type="hidden" name="latitude" id="latKeluar">
  <input type="hidden" name="longitude" id="lngKeluar">
  <input type="hidden" name="accuracy" id="accKeluar">
  <input type="file" name="foto" id="fotoKeluar">
  <input type="hidden" name="realisasi" id="realisasiKeluar">
</form>
@endsection

@push('scripts')
<script>
const INSTANSI_LAT   = {{ $instansi?->latitude ?? 'null' }};
const INSTANSI_LNG   = {{ $instansi?->longitude ?? 'null' }};

const JAM_MASUK_HARI_INI = @json($absensiHariIni?->jam_masuk); // "HH:mm:ss" atau null
const DURASI_MINIMAL_JAM = {{ \App\Models\Absensi::DURASI_MINIMAL_JAM }};

let currentMode     = null;   // 'masuk' | 'keluar'
let cameraStream    = null;
let currentPosition = null;
let capturedBlob    = null;
let durasiInterval   = null;  // ← BARU: interval untuk update durasi live

function hitungJarakMeter(lat1, lng1, lat2, lng2){
  const R = 6371000;
  const toRad = d => d * Math.PI / 180;
  const dLat = toRad(lat2 - lat1);
  const dLng = toRad(lng2 - lng1);
  const a = Math.sin(dLat/2)**2 + Math.cos(toRad(lat1))*Math.cos(toRad(lat2))*Math.sin(dLng/2)**2;
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

// ← BARU: hitung & tampilkan durasi kerja berjalan (sejak jam_masuk s.d. sekarang)
function updateDurasiInfo(){
  const el = document.getElementById('durasiInfo');
  if (currentMode !== 'keluar' || !JAM_MASUK_HARI_INI) {
    el.style.display = 'none';
    return;
  }
  const [h, m, s] = JAM_MASUK_HARI_INI.split(':').map(Number);
  const masuk = new Date();
  masuk.setHours(h, m, s || 0, 0);
  const sekarang = new Date();
  let jam = (sekarang - masuk) / 1000 / 3600;
  if (jam < 0) jam += 24; // jaga-jaga lintas tengah malam

  el.style.display = 'flex';
  if (jam < DURASI_MINIMAL_JAM) {
    const sisaMenit = Math.ceil((DURASI_MINIMAL_JAM - jam) * 60);
    el.className = 'loc-info bad';
    el.textContent = `⚠️ Durasi kerja baru ${jam.toFixed(1)} jam — kurang ${sisaMenit} menit lagi dari minimal ${DURASI_MINIMAL_JAM} jam sesuai panduan KP. Anda tetap bisa absen pulang, namun akan ditandai untuk ditinjau dosen pembimbing.`;
  } else {
    el.className = 'loc-info ok';
    el.textContent = `✅ Durasi kerja sudah ${jam.toFixed(1)} jam — memenuhi minimal ${DURASI_MINIMAL_JAM} jam.`;
  }
}

function openAbsenModal(mode){
  currentMode = mode;
  capturedBlob = null;
  currentPosition = null;

  document.getElementById('modalAbsenTitle').textContent = mode === 'masuk' ? 'Absen Masuk' : 'Absen Pulang';
  document.getElementById('btnSubmitAbsen').disabled = true;
  document.getElementById('btnSubmitAbsen').textContent = 'Kirim Absen';
  document.getElementById('btnRetake').style.display = 'none';
  document.getElementById('btnCapture').style.display = 'inline-flex';

  // ← BARU: reset & sesuaikan label textarea catatan sesuai mode
  const inputCatatan = document.getElementById('inputCatatan');
  inputCatatan.value = '';
  if (mode === 'masuk') {
    document.getElementById('catatanLabel').textContent = '📋 Rencana Kegiatan Hari Ini *';
    inputCatatan.placeholder = 'Tuliskan rencana kegiatan yang akan Anda lakukan hari ini...';
  } else {
    document.getElementById('catatanLabel').textContent = '✅ Realisasi Kegiatan Hari Ini *';
    inputCatatan.placeholder = 'Tuliskan kegiatan yang sudah Anda selesaikan hari ini...';
  }

  // ← BARU: mulai update durasi live (hanya relevan & tampil saat mode keluar)
  updateDurasiInfo();
  if (durasiInterval) clearInterval(durasiInterval);
  durasiInterval = setInterval(updateDurasiInfo, 15000); // refresh tiap 15 detik

  const loc = document.getElementById('locStatus');
  loc.className = 'loc-info wait';
  loc.textContent = '📡 Mendeteksi lokasi GPS Anda...';

  openModal('modalAbsen');
  startLocation();
  startCamera();
}

function closeAbsenModal(){
  stopCamera();
  if (durasiInterval) { clearInterval(durasiInterval); durasiInterval = null; } // ← BARU
  closeModal('modalAbsen');
}

function startLocation(){
  if (!navigator.geolocation){
    const loc = document.getElementById('locStatus');
    loc.className = 'loc-info bad';
    loc.textContent = '❌ Perangkat tidak mendukung GPS.';
    return;
  }
  navigator.geolocation.getCurrentPosition(pos => {
    currentPosition = pos;
    const jarak   = Math.round(hitungJarakMeter(pos.coords.latitude, pos.coords.longitude, INSTANSI_LAT, INSTANSI_LNG));
    const akurasi = Math.round(pos.coords.accuracy);
    const loc = document.getElementById('locStatus');
    loc.className = 'loc-info ok';
    loc.textContent = `📍 ${jarak}m dari instansi (akurasi ±${akurasi}m)`;
    updateSubmitState();
  }, () => {
    const loc = document.getElementById('locStatus');
    loc.className = 'loc-info bad';
    loc.textContent = '❌ Gagal mengambil lokasi. Aktifkan GPS dan izinkan akses lokasi pada browser.';
  }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
}

function startCamera(){
  const video = document.getElementById('camVideo');
  video.style.display = 'block';
  document.getElementById('camCanvas').style.display = 'none';
  navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false })
    .then(stream => { cameraStream = stream; video.srcObject = stream; })
    .catch(() => {
      document.getElementById('camHint').textContent = '❌ Gagal mengakses kamera. Izinkan akses kamera pada browser Anda.';
    });
}

function stopCamera(){
  if (cameraStream){ cameraStream.getTracks().forEach(t => t.stop()); cameraStream = null; }
}

function capturePhoto(){
  const video  = document.getElementById('camVideo');
  const canvas = document.getElementById('camCanvas');
  canvas.width  = video.videoWidth;
  canvas.height = video.videoHeight;
  const ctx = canvas.getContext('2d');
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

  // Watermark waktu & koordinat langsung di foto — bukti tambahan di luar data lokasi terstruktur
  const barH = Math.round(canvas.height * 0.13);
  ctx.fillStyle = 'rgba(0,0,0,.55)';
  ctx.fillRect(0, canvas.height - barH, canvas.width, barH);
  ctx.fillStyle = '#fff';
  ctx.font = `${Math.round(barH * 0.32)}px sans-serif`;
  const waktu = new Date().toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'medium' });
  const lokasiTxt = currentPosition
    ? `Lat ${currentPosition.coords.latitude.toFixed(5)}, Lng ${currentPosition.coords.longitude.toFixed(5)}`
    : 'Lokasi: mencari...';
  ctx.fillText(waktu, 12, canvas.height - barH * 0.62);
  ctx.fillText(lokasiTxt, 12, canvas.height - barH * 0.20);

  video.style.display = 'none';
  canvas.style.display = 'block';
  stopCamera();

  canvas.toBlob(blob => { capturedBlob = blob; updateSubmitState(); }, 'image/jpeg', 0.85);

  document.getElementById('btnCapture').style.display = 'none';
  document.getElementById('btnRetake').style.display = 'inline-flex';
}

function retakePhoto(){
  capturedBlob = null;
  document.getElementById('btnRetake').style.display = 'none';
  document.getElementById('btnCapture').style.display = 'inline-flex';
  startCamera();
  updateSubmitState();
}

function updateSubmitState(){
  const catatan = document.getElementById('inputCatatan').value.trim();
  document.getElementById('btnSubmitAbsen').disabled = !(capturedBlob && currentPosition && catatan.length >= 10);
}

function submitAbsen(){
  const catatan = document.getElementById('inputCatatan').value.trim();
  if (!capturedBlob || !currentPosition || catatan.length < 10) return;
  const suffix = currentMode === 'masuk' ? 'Masuk' : 'Keluar';

  document.getElementById('lat'+suffix).value = currentPosition.coords.latitude;
  document.getElementById('lng'+suffix).value = currentPosition.coords.longitude;
  document.getElementById('acc'+suffix).value = Math.round(currentPosition.coords.accuracy);

  // ← BARU: salin catatan ke hidden field yang sesuai (rencana saat masuk, realisasi saat pulang)
  if (currentMode === 'masuk') {
    document.getElementById('rencanaMasuk').value = catatan;
  } else {
    document.getElementById('realisasiKeluar').value = catatan;
  }

  const file = new File([capturedBlob], 'absen_'+Date.now()+'.jpg', { type: 'image/jpeg' });
  const dt = new DataTransfer();
  dt.items.add(file);
  document.getElementById('foto'+suffix).files = dt.files;

  document.getElementById('btnSubmitAbsen').disabled = true;
  document.getElementById('btnSubmitAbsen').textContent = 'Mengirim...';
  document.getElementById('formAbsen'+suffix).submit();
}

@if($errors->absenMasuk->any())
  document.addEventListener('DOMContentLoaded', () => {
    openAbsenModal('masuk');
    document.getElementById('inputCatatan').value = @json(old('rencana', ''));
    updateSubmitState();
  });
@elseif($errors->absenKeluar->any())
  document.addEventListener('DOMContentLoaded', () => {
    openAbsenModal('keluar');
    document.getElementById('inputCatatan').value = @json(old('realisasi', ''));
    updateSubmitState();
  });
@endif
</script>
@endpush