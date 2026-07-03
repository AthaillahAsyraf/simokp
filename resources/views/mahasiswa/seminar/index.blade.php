@extends('layouts.app')
@section('title','Seminar KP')

@push('styles')
<style>
.seminar-hero{background:var(--purple-50);border-radius:12px;padding:24px;text-align:center}
.seminar-hero .tgl{font-size:42px;font-weight:800;color:var(--purple-600);margin:8px 0}
.check-item{display:flex;align-items:center;gap:12px;padding:10px;border-radius:8px;border:1px solid var(--gray-200)}
.check-item.done{background:var(--green-50);border-color:var(--green-100)}
.err-box{background:var(--red-50);border:1px solid var(--red-100);border-radius:6px;padding:10px 14px;margin-bottom:14px}
.err-box strong{color:var(--red-600);font-size:.85rem}
.err-box ul{margin:4px 0 0 18px;color:var(--red-600);font-size:.82rem}
</style>
@endpush

@section('content')
<div class="page-header"><h1>Seminar KP</h1><p>Jadwal dan pengajuan seminar Kerja Praktik Anda</p></div>

@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">⚠️ {{ session('error') }}</div>@endif
@if($errors->daftar->any())
  <div class="err-box"><strong>⚠️ Terdapat kesalahan:</strong>
    <ul>@foreach($errors->daftar->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

@php
  $s = $mahasiswa->seminar;
  $babSelesai = $mahasiswa->allBabSelesai();
@endphp

@if($s && $s->isPending())
  {{-- MENUNGGU PERSETUJUAN --}}
  <div class="card">
    <div class="card-header"><h3>🕓 Pengajuan Seminar Anda</h3><span class="badge badge-proses">Menunggu Persetujuan Admin</span></div>
    <div class="card-body">
      <p class="text-sm text-muted" style="margin-bottom:14px">Pengajuan jadwal di bawah ini sedang ditinjau admin. Dosen penguji akan ditentukan saat disetujui.</p>
      <div style="margin-bottom:14px"><div class="text-sm text-muted">Judul KP/PKL</div><strong>{{ $s->judul_kp }}</strong></div>
      <div class="form-grid">
        <div><div class="text-sm text-muted">Tanggal Diajukan</div><strong>{{ \Carbon\Carbon::parse($s->tanggal)->translatedFormat('d F Y') }}</strong></div>
        <div><div class="text-sm text-muted">Jam</div><strong>{{ \Carbon\Carbon::parse($s->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->jam_selesai)->format('H:i') }} WIB</strong></div>
        <div><div class="text-sm text-muted">Ruangan</div><strong>{{ $s->ruangan }}</strong></div>
      </div>
    </div>
  </div>

@elseif($s && $s->isTerjadwal())
  {{-- TERJADWAL --}}
  <div class="card">
    <div class="card-header"><h3>🎤 Jadwal Seminar KP Anda</h3><span class="badge badge-terjadwal">Terjadwal</span></div>
    <div class="card-body">
      <div style="margin-bottom:16px"><div class="text-sm text-muted">Judul KP/PKL</div><strong>{{ $s->judul_kp }}</strong></div>
      <div class="grid-2" style="gap:20px;margin-bottom:20px">
        <div class="seminar-hero">
          <div class="text-sm text-muted" style="text-transform:uppercase;letter-spacing:.5px">Tanggal Seminar</div>
          <div class="tgl">{{ \Carbon\Carbon::parse($s->tanggal)->format('d') }}</div>
          <div class="text-sm text-muted">{{ \Carbon\Carbon::parse($s->tanggal)->translatedFormat('F Y') }}</div>
        </div>
        <div style="display:flex;flex-direction:column;gap:14px;font-size:13px">
          <div><div class="text-sm text-muted" style="margin-bottom:3px">JAM PELAKSANAAN</div><strong style="font-size:20px">{{ \Carbon\Carbon::parse($s->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->jam_selesai)->format('H:i') }} WIB</strong></div>
          <div><div class="text-sm text-muted" style="margin-bottom:3px">RUANGAN</div><strong>{{ $s->ruangan }}</strong></div>
          <div><div class="text-sm text-muted" style="margin-bottom:3px">DOSEN PENGUJI</div><strong>{{ $s->dosenPenguji?->nama ?? 'Belum ditentukan' }}</strong></div>
        </div>
      </div>
      <div class="alert alert-info">📌 Hadir tepat waktu dan siapkan presentasi laporan KP Anda dengan baik.</div>
    </div>
  </div>

@elseif($s && $s->isSelesai())
  {{-- SELESAI --}}
  <div class="card">
    <div class="card-header"><h3>🎉 Seminar KP Selesai</h3><span class="badge badge-selesai">Selesai</span></div>
    <div class="card-body">
      <div style="margin-bottom:14px"><div class="text-sm text-muted">Judul KP/PKL</div><strong>{{ $s->judul_kp }}</strong></div>
      <div class="form-grid" style="margin-bottom:16px">
        <div><div class="text-sm text-muted">Tanggal</div><strong>{{ \Carbon\Carbon::parse($s->tanggal)->translatedFormat('d F Y') }}</strong></div>
        <div><div class="text-sm text-muted">Ruangan</div><strong>{{ $s->ruangan }}</strong></div>
        <div><div class="text-sm text-muted">Dosen Penguji</div><strong>{{ $s->dosenPenguji?->nama ?? '–' }}</strong></div>
      </div>
      @if($mahasiswa->nilai?->nilai_seminar)
        <div class="alert alert-success">🎉 Selamat! Seminar KP Anda telah selesai dengan nilai <strong>{{ $mahasiswa->nilai->nilai_seminar }}</strong>.</div>
      @else
        <div class="alert alert-info">Seminar telah dilaksanakan, menunggu nilai dari dosen pembimbing.</div>
      @endif
    </div>
  </div>

@elseif($s && $s->isDitolak())
  {{-- DITOLAK --}}
  <div class="card" style="margin-bottom:20px">
    <div class="card-header"><h3>✖️ Pengajuan Ditolak</h3><span class="badge badge-ditolak" style="background:var(--red-100);color:var(--red-600)">Ditolak</span></div>
    <div class="card-body">
      <div class="alert alert-danger">{{ $s->catatan ?? 'Pengajuan seminar ditolak admin. Silakan ajukan jadwal baru.' }}</div>
    </div>
  </div>
@endif

@if(!$s || $s->isDitolak())
  {{-- FORM PENGAJUAN (belum ada seminar, atau yang lama ditolak) --}}
  <div class="card">
    <div class="card-header"><h3>🗓️ Ajukan Jadwal Seminar</h3></div>
    <div class="card-body">
      @if(!$babSelesai)
        <div class="alert alert-warning">🔒 Seminar belum bisa diajukan — selesaikan & tunggu persetujuan semua BAB laporan dulu (progress: {{ $mahasiswa->progressPersen() }}%).</div>
      @else
        <form method="POST" action="{{ route('mahasiswa.seminar.store') }}">
          @csrf
          <div class="form-group">
            <label class="form-label">Judul KP/PKL *</label>
            <textarea name="judul_kp" class="form-control" rows="2" placeholder="Judul laporan Kerja Praktik Anda" required>{{ old('judul_kp') }}</textarea>
            <p class="form-hint">Judul ini otomatis muncul di lembar penilaian seminar dosen pembimbing — pastikan sudah final.</p>
          </div>
          <div class="form-grid">
            <div class="form-group"><label class="form-label">Tanggal *</label><input type="date" name="tanggal" class="form-control" value="{{ old('tanggal') }}" min="{{ now()->toDateString() }}" required></div>
            <div class="form-group"><label class="form-label">Jam Mulai *</label><input type="time" name="jam_mulai" class="form-control" value="{{ old('jam_mulai') }}" required></div>
            <div class="form-group"><label class="form-label">Jam Selesai *</label><input type="time" name="jam_selesai" class="form-control" value="{{ old('jam_selesai') }}" required></div>
          </div>
          <div class="form-group">
            <label class="form-label">Ruangan *</label>
            <input type="text" name="ruangan" class="form-control" list="daftarRuangan" placeholder="Pilih dari daftar atau ketik ruangan baru" value="{{ old('ruangan') }}" required>
            <datalist id="daftarRuangan">
              @foreach($ruanganList as $r)<option value="{{ $r }}">@endforeach
            </datalist>
            <p class="form-hint">Bisa pilih ruangan yang sudah ada, atau ketik nama ruangan baru kalau belum ada di daftar.</p>
          </div>
          <p class="form-hint" style="margin-bottom:14px">Dosen penguji akan ditentukan oleh admin saat menyetujui pengajuan.</p>
          <button type="submit" class="btn btn-primary">Ajukan Jadwal Seminar</button>
        </form>

        @if($jadwalTerisi->isNotEmpty())
        <div style="margin-top:20px">
          <p class="text-sm text-muted" style="margin-bottom:8px">📌 Jadwal yang sudah terisi (hindari memilih tanggal/jam/ruangan yang sama):</p>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Tanggal</th><th>Jam</th><th>Ruangan</th><th>Status</th></tr></thead>
              <tbody>
                @foreach($jadwalTerisi as $j)
                <tr>
                  <td class="text-sm">{{ \Carbon\Carbon::parse($j->tanggal)->format('d M Y') }}</td>
                  <td class="text-sm text-muted">{{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($j->jam_selesai)->format('H:i') }}</td>
                  <td class="text-sm">{{ $j->ruangan }}</td>
                  <td><span class="badge {{ $j->isPending() ? 'badge-proses' : 'badge-terjadwal' }}">{{ $j->isPending() ? 'Menunggu' : 'Terjadwal' }}</span></td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
        @endif
      @endif
    </div>
  </div>
@endif

<div class="card" style="margin-top:20px">
  <div class="card-header"><h3>📋 Checklist Syarat Seminar KP</h3></div>
  <div class="card-body" style="display:grid;gap:12px">
@php
  $checks = [
    ['Laporan BAB I–V selesai & disetujui 100%', $mahasiswa->progressPersen() === 100 && $babSelesai],
    ['Status sudah mencapai tahap seminar', in_array($mahasiswa->status, ['seminar','selesai'])],
    ['Data mahasiswa lengkap (NIM & Instansi)', !!($mahasiswa->nim && $mahasiswa->instansi_id) ],
  ];
@endphp
    @foreach($checks as [$label, $done])
    <div class="check-item {{ $done ? 'done' : '' }}">
      <span style="font-size:18px">{{ $done ? '✅' : '⬜' }}</span>
      <span class="{{ $done ? '' : 'text-muted' }}" style="font-size:13px">{{ $label }}</span>
    </div>
    @endforeach
  </div>
</div>
@endsection