@extends('layouts.app')
@section('title', 'Seminar KP')

@section('content')
@php
  $s = $mahasiswa->seminar;
  $slotTerisi = $jadwalTerisi->map(fn ($jadwal) => [
    'tanggal' => (string) $jadwal->tanggal,
    'mulai' => substr((string) $jadwal->jam_mulai, 0, 5),
    'selesai' => substr((string) $jadwal->jam_selesai, 0, 5),
    'ruangan' => strtolower(trim((string) $jadwal->ruangan)),
    'dosen_id' => $jadwal->mahasiswa?->dosen_id,
  ])->values();
@endphp

<div class="page-header"><h1>Seminar KP</h1><p>Pilih jadwal saat mengajukan ACC. Slot yang dipilih langsung dikunci sampai keputusan dosen pembimbing.</p></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

@if(!$s || $s->status === \App\Models\Seminar::STATUS_ACC_DITOLAK)
  <datalist id="daftarRuangan">@foreach($ruanganList as $ruangan)<option value="{{ $ruangan }}">@endforeach</datalist>
  <div class="card"><div class="card-header"><h3>Ajukan Seminar dan Minta ACC</h3></div><div class="card-body">
    @if($s)<div class="alert alert-warning">ACC sebelumnya ditolak: {{ $s->catatan }}</div>@endif
    <div class="alert alert-info">Pilih tanggal, jam, dan ruangan sekarang. Sistem mengecek bentrok ruangan dan dosen pembimbing, termasuk slot yang masih menunggu ACC.</div>
    <form method="POST" action="{{ route('mahasiswa.seminar.minta-acc') }}" id="seminarForm">@csrf
      <div class="form-group"><label class="form-label">Judul KP *</label><textarea name="judul_kp" class="form-control" rows="2" required>{{ old('judul_kp') }}</textarea></div>
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Tanggal *</label><input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ old('tanggal') }}" min="{{ now()->toDateString() }}" required></div>
        <div class="form-group"><label class="form-label">Jam mulai *</label><input type="time" name="jam_mulai" id="jamMulai" class="form-control" value="{{ old('jam_mulai') }}" required></div>
        <div class="form-group"><label class="form-label">Jam selesai *</label><input type="time" name="jam_selesai" id="jamSelesai" class="form-control" value="{{ old('jam_selesai') }}" required></div>
      </div>
      <div class="form-group"><label class="form-label">Ruangan *</label><input type="text" name="ruangan" id="ruangan" class="form-control" list="daftarRuangan" value="{{ old('ruangan') }}" placeholder="Pilih atau ketik ruangan" required></div>
      <div id="slotInfo" class="alert alert-info">Lengkapi pilihan jadwal untuk mengecek ketersediaan slot.</div>
      <button class="btn btn-primary" id="submitSeminar">Kirim ke Dosen Pembimbing</button>
    </form>
  </div></div>

  @if($jadwalTerisi->isNotEmpty())
    <div class="card" style="margin-top:16px"><div class="card-header"><h3>Slot yang sudah terisi</h3></div><div class="card-body"><div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Jam</th><th>Ruangan</th></tr></thead><tbody>@foreach($jadwalTerisi as $jadwal)<tr><td>{{ \Carbon\Carbon::parse($jadwal->tanggal)->format('d M Y') }}</td><td>{{ substr((string) $jadwal->jam_mulai, 0, 5) }} - {{ substr((string) $jadwal->jam_selesai, 0, 5) }} WIB</td><td>{{ $jadwal->ruangan }}</td></tr>@endforeach</tbody></table></div></div></div>
  @endif
@else
  <div class="card"><div class="card-header"><h3>Status Seminar</h3><span class="badge {{ $s->status === \App\Models\Seminar::STATUS_MENUNGGU_ACC_DOSPEM ? 'badge-proses' : 'badge-selesai' }}">{{ ucwords(str_replace('_', ' ', $s->status)) }}</span></div><div class="card-body">
    <div class="form-group"><label class="form-label">Judul KP</label><div>{{ $s->judul_kp }}</div></div>
    <div class="form-grid"><div><span class="text-sm text-muted">Tanggal & jam</span><div>{{ \Carbon\Carbon::parse($s->tanggal)->format('d M Y') }} · {{ substr((string) $s->jam_mulai, 0, 5) }} - {{ substr((string) $s->jam_selesai, 0, 5) }} WIB</div></div><div><span class="text-sm text-muted">Ruangan</span><div>{{ $s->ruangan }}</div></div></div>
    @if($s->status === \App\Models\Seminar::STATUS_MENUNGGU_ACC_DOSPEM)<p style="margin-top:16px">Permintaan ACC sedang diperiksa dosen pembimbing. Slot di atas tetap dikunci selama proses ini.</p>@elseif($s->catatan)<div class="alert alert-warning" style="margin-top:16px">{{ $s->catatan }}</div>@endif
  </div></div>
@endif

@push('scripts')
<script>
const slots = @json($slotTerisi);
const dosenId = {{ (int) $mahasiswa->dosen_id }};
const tanggal = document.getElementById('tanggal');
const mulai = document.getElementById('jamMulai');
const selesai = document.getElementById('jamSelesai');
const ruangan = document.getElementById('ruangan');
const slotInfo = document.getElementById('slotInfo');
const submit = document.getElementById('submitSeminar');
if (tanggal && mulai && selesai && ruangan) {
  const cekSlot = () => {
    const date = tanggal.value, start = mulai.value, end = selesai.value, room = ruangan.value.trim().toLowerCase();
    if (!date || !start || !end || !room) return;
    const conflict = slots.find(slot => slot.tanggal === date && start < slot.selesai && end > slot.mulai && (slot.ruangan === room || Number(slot.dosen_id) === dosenId));
    const invalidTime = start >= end;
    if (conflict || invalidTime) {
      slotInfo.className = 'alert alert-danger';
      slotInfo.textContent = invalidTime ? 'Jam selesai harus setelah jam mulai.' : 'Slot ini sudah dipilih mahasiswa lain. Silakan pilih waktu atau ruangan lain yang kosong.';
      submit.disabled = true;
    } else {
      slotInfo.className = 'alert alert-success';
      slotInfo.textContent = 'Slot tersedia dan akan dikunci setelah pengajuan dikirim.';
      submit.disabled = false;
    }
  };
  [tanggal, mulai, selesai, ruangan].forEach(input => input.addEventListener('input', cekSlot));
  cekSlot();
}
</script>
@endpush
@endsection
