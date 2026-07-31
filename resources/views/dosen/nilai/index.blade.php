@extends('layouts.app')
@section('title','Nilai Mahasiswa')

@push('styles')
<style>
.predikat{display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;padding:0 6px;border-radius:8px;font-weight:700;font-size:13px}
.predikat-A{background:var(--green-100);color:var(--green-700)}
.predikat-B{background:var(--blue-100);color:var(--blue-700)}
.predikat-C{background:var(--amber-100);color:var(--amber-600)}
.predikat-D, .predikat-E, .predikat-BL{background:var(--red-100);color:var(--red-600)}
.status-lulus{color:var(--green-600);font-weight:600}
.status-tidak-lulus{color:var(--red-600);font-weight:600}
.status-belum{color:var(--gray-400)}
.err-box{background:var(--red-50);border:1px solid var(--red-100);border-radius:6px;padding:10px 14px;margin-bottom:14px}
.err-box ul{margin:4px 0 0 18px;color:var(--red-600);font-size:.82rem}
</style>
@endpush

@section('content')
<div class="page-header"><h1>Nilai Mahasiswa</h1><p>Input nilai pembimbing & lihat nilai akhir mahasiswa bimbingan Anda</p></div>

<div class="card" style="margin-bottom:16px"><div class="card-body" style="padding:14px 18px">
  <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, NIM, atau instansi..." class="form-control" style="width:260px">
    <select name="filter" class="form-control" style="width:210px"><option value="">Semua Mahasiswa</option><option value="siap_dinilai" {{ $filter === 'siap_dinilai' ? 'selected' : '' }}>Belum Dinilai</option><option value="sudah_dinilai" {{ $filter === 'sudah_dinilai' ? 'selected' : '' }}>Sudah Dinilai</option></select>
    <button type="submit" class="btn btn-outline btn-sm">Filter</button><a href="{{ route('dosen.nilai.index') }}" class="btn btn-outline btn-sm">Reset</a>
  </form>
</div></div>

@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">⚠️ {{ session('error') }}</div>@endif

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Mahasiswa</th><th>Nilai Pembimbing Lapangan</th><th>Nilai Dosen Pembimbing</th>
          <th>Nilai Akhir</th><th>Status</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($mahasiswas as $m)
        @php $n = $m->nilai; $s = $m->seminar; @endphp
        <tr>
          <td><strong>{{ $m->nama }}</strong><br><code class="text-sm">{{ $m->nim }}</code><br><span class="text-sm text-muted">{{ $m->instansi?->nama }}</span></td>
          <td class="text-sm">{{ $n?->nilai_lapangan ?? '–' }}</td>
          <td class="text-sm">{{ $n?->nilai_seminar ?? '–' }} @if($n?->huruf_mutu_seminar)<span class="text-muted">({{ $n->huruf_mutu_seminar }})</span>@endif</td>
          <td><strong>{{ $n?->nilai_akhir ?? '–' }}</strong>
            @if($n?->predikat)<span class="predikat predikat-{{ $n->predikat }}" style="margin-left:6px">{{ $n->predikat }}</span>@endif
          </td>
          <td>
            @php $status = $n?->status_kelulusan ?? 'Belum Lengkap'; @endphp
            <span class="{{ $status === 'Lulus' ? 'status-lulus' : ($status === 'Tidak Lulus' ? 'status-tidak-lulus' : 'status-belum') }}">{{ $status }}</span>
          </td>
          <td>
            <div style="display:flex;gap:4px;flex-wrap:wrap">
              @if($s && ($s->isTerjadwal() || $s->isSelesai()))
                <button class="btn btn-outline btn-xs" onclick="openSeminar({{ $m->id }}, {{ json_encode($m->nama) }}, {{ json_encode($n) }})">Isi Nilai</button>
              @else
                <button class="btn btn-outline btn-xs" disabled title="Seminar belum dijadwalkan">Isi Nilai</button>
              @endif
              @if($n?->nilai_seminar !== null)
                <a class="btn btn-outline btn-xs" href="{{ route('dosen.nilai.cetak', $m->id) }}" target="_blank">Cetak Nilai</a>
              @endif
            </div>
          </td>
        </tr>
        @empty
          <tr><td colspan="6" style="text-align:center;padding:28px;color:var(--gray-400)">Belum ada mahasiswa bimbingan.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- MODAL ISI NILAI PEMBIMBING — 6 aspek berbobot sesuai Lembar Penilaian Seminar KP Dosen Pembimbing --}}
<div class="modal-bg" id="modalSeminar">
  <div class="modal-box" style="width:560px">
    <div class="modal-title" id="sTitle">📝 Isi Nilai Pembimbing</div>
    @if($errors->seminar->any())
    <div class="err-box"><ul>@foreach($errors->seminar->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    <form method="POST" id="seminarForm" oninput="hitungTotalSeminar()">
      @csrf @method('PUT')

      <p class="text-sm" style="font-weight:700;color:var(--gray-700);margin-bottom:6px">1. Seminar</p>
      <div class="form-group"><label class="form-label">a. Penguasaan materi/metode (20%) *</label><input type="number" name="seminar_penguasaan_materi" id="s1a" class="form-control" min="0" max="100" step="0.01" required></div>
      <div class="form-group"><label class="form-label">b. Sikap ilmiah dan argumentasi (10%) *</label><input type="number" name="seminar_sikap_ilmiah" id="s1b" class="form-control" min="0" max="100" step="0.01" required></div>
      <div class="form-group"><label class="form-label">c. Teknik penyajian dan kebahasaan (10%) *</label><input type="number" name="seminar_teknik_penyajian" id="s1c" class="form-control" min="0" max="100" step="0.01" required></div>

      <p class="text-sm" style="font-weight:700;color:var(--gray-700);margin:14px 0 6px">2. Laporan</p>
      <div class="form-group"><label class="form-label">a. Originalitas (30%) *</label><input type="number" name="seminar_originalitas" id="s2a" class="form-control" min="0" max="100" step="0.01" required></div>
      <div class="form-group"><label class="form-label">b. Relevansi dan keterpaduan (15%) *</label><input type="number" name="seminar_relevansi" id="s2b" class="form-control" min="0" max="100" step="0.01" required></div>
      <div class="form-group"><label class="form-label">c. Penulisan (format dan bahasa) (15%) *</label><input type="number" name="seminar_penulisan" id="s2c" class="form-control" min="0" max="100" step="0.01" required></div>

      <div style="display:flex;justify-content:space-between;align-items:center;background:var(--gray-50);border-radius:8px;padding:10px 14px;margin-top:10px">
        <span class="text-sm text-muted">Nilai Total (otomatis, sesuai bobot)</span>
        <span style="display:flex;align-items:center;gap:8px">
          <strong id="sTotalOut" style="font-size:18px">–</strong>
          <span id="sHurufMutuOut" class="predikat" style="display:none"></span>
        </span>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalSeminar')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
const BOBOT_SEMINAR = { s1a:0.20, s1b:0.10, s1c:0.10, s2a:0.30, s2b:0.15, s2c:0.15 };

function openSeminar(id, nama, n) {
  n = n || {};
  document.getElementById('seminarForm').action = `{{ url('dosen-area/nilai') }}/${id}/seminar`;
  document.getElementById('sTitle').textContent = '📝 Isi Nilai Pembimbing — ' + nama;
  document.getElementById('s1a').value = n.seminar_penguasaan_materi ?? '';
  document.getElementById('s1b').value = n.seminar_sikap_ilmiah ?? '';
  document.getElementById('s1c').value = n.seminar_teknik_penyajian ?? '';
  document.getElementById('s2a').value = n.seminar_originalitas ?? '';
  document.getElementById('s2b').value = n.seminar_relevansi ?? '';
  document.getElementById('s2c').value = n.seminar_penulisan ?? '';
  hitungTotalSeminar();
  openModal('modalSeminar');
}

function hitungTotalSeminar() {
  let total = 0, lengkap = true;
  for (const id in BOBOT_SEMINAR) {
    const v = document.getElementById(id).value;
    if (v === '') { lengkap = false; continue; }
    total += parseFloat(v) * BOBOT_SEMINAR[id];
  }
  document.getElementById('sTotalOut').textContent = lengkap ? total.toFixed(2) : '–';

  const hurufEl = document.getElementById('sHurufMutuOut');
  if (!lengkap) {
    hurufEl.style.display = 'none';
    return;
  }
  const { huruf, kelas } = hitungHurufMutu(total);
  hurufEl.textContent = huruf;
  hurufEl.className = 'predikat predikat-' + kelas;
  hurufEl.style.display = 'inline-flex';
}

// Meniru persis tabel konversi di Nilai::getHurufMutuSeminarAttribute()
function hitungHurufMutu(nilai) {
  if (nilai >= 76) return { huruf: 'A',  kelas: 'A' };
  if (nilai >= 71) return { huruf: 'B+', kelas: 'B' };
  if (nilai >= 66) return { huruf: 'B',  kelas: 'B' };
  if (nilai >= 61) return { huruf: 'C+', kelas: 'C' };
  if (nilai >= 56) return { huruf: 'C',  kelas: 'C' };
  return { huruf: 'BL', kelas: 'BL' };
}
</script>
@endpush
@endsection