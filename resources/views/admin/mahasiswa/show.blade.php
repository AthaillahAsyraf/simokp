@extends('layouts.app')
@section('title','Detail Mahasiswa')
@section('content')

<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-ghost btn-sm">← Kembali</a>
    <div>
      <h1>{{ $mahasiswa->nama }}</h1>
      <p><code>{{ $mahasiswa->nim }}</code> · Angkatan {{ $mahasiswa->angkatan }}</p>
    </div>
  </div>
</div>

<div class="grid-2">
  {{-- Info Umum --}}
  <div class="card">
    <div class="card-header"><h3>Informasi KP</h3><span class="pill pill-{{ $mahasiswa->status }}">{{ ucfirst($mahasiswa->status) }}</span></div>
    <div class="card-body" style="display:grid;gap:12px;font-size:13px">
      <div><div style="color:var(--muted);font-size:11px">Dosen Pembimbing</div><strong>{{ $mahasiswa->dosen?->nama ?? '–' }}</strong></div>
      <div><div style="color:var(--muted);font-size:11px">Instansi / Lokasi KP</div><strong>{{ $mahasiswa->instansi?->nama ?? '–' }}</strong></div>
      <div><div style="color:var(--muted);font-size:11px">Alamat Instansi</div>{{ $mahasiswa->instansi?->alamat ?? '–' }}</div>
      <div><div style="color:var(--muted);font-size:11px">Tanggal Mulai</div><strong>{{ $mahasiswa->tanggal_mulai ?? '–' }}</strong></div>
      <div><div style="color:var(--muted);font-size:11px">Tanggal Selesai</div><strong>{{ $mahasiswa->tanggal_selesai ?? '–' }}</strong></div>
    </div>
  </div>

  {{-- Nilai --}}
  <div class="card">
    <div class="card-header"><h3>Rekapitulasi Nilai</h3></div>
    <div class="card-body">
      @php $nilai = $mahasiswa->nilai; @endphp
      @if($nilai)
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;text-align:center;margin-bottom:16px">
        <div style="background:rgba(245,158,11,.1);border-radius:10px;padding:14px">
          <div style="font-size:11px;color:var(--muted)">Instansi (40%)</div>
          <div style="font-size:26px;font-weight:800;color:var(--inst-l)">{{ $nilai->nilai_instansi ?? '–' }}</div>
        </div>
        <div style="background:rgba(16,185,129,.1);border-radius:10px;padding:14px">
          <div style="font-size:11px;color:var(--muted)">Pembimbing (30%)</div>
          <div style="font-size:26px;font-weight:800;color:var(--dosen-l)">{{ $nilai->nilai_pembimbing ?? '–' }}</div>
        </div>
        <div style="background:rgba(99,102,241,.1);border-radius:10px;padding:14px">
          <div style="font-size:11px;color:var(--muted)">Seminar (30%)</div>
          <div style="font-size:26px;font-weight:800;color:var(--admin-l)">{{ $nilai->nilai_seminar ?? '–' }}</div>
        </div>
      </div>
      @if($nilai->nilai_akhir)
        <div class="alert alert-success" style="justify-content:center;font-size:15px">
          🏆 Nilai Akhir: <strong style="font-size:20px;margin-left:8px">{{ $nilai->nilai_akhir }}</strong>
        </div>
      @else
        <div class="alert alert-info">ℹ️ Nilai akhir belum dapat dihitung. Lengkapi semua komponen nilai.</div>
      @endif
      @else
        <p style="color:var(--muted);font-size:13px">Belum ada data nilai.</p>
      @endif
    </div>
  </div>
</div>

{{-- Progress BAB --}}
<div class="card">
  <div class="card-header">
    <div><h3>Progress BAB Laporan</h3><p>{{ $mahasiswa->progressPersen() }}% selesai</p></div>
    <div class="prog-wrap" style="width:160px"><div class="prog-bar" style="width:{{ $mahasiswa->progressPersen() }}%;background:var(--admin)"></div></div>
  </div>
  <div style="padding:20px">
    <div class="bab-grid">
      @foreach($mahasiswa->progressBabs as $p)
      <div class="bab-item {{ $p->status === 'selesai' ? 'done' : ($p->status === 'proses' ? 'proses' : '') }}">
        <div class="bab-name">{{ $p->bab }}</div>
        <div class="bab-status">{{ $p->status === 'selesai' ? '✅' : ($p->status === 'proses' ? '🔄' : '⏳') }}</div>
        <div class="bab-label">{{ ucfirst($p->status) }}</div>
        @if($p->tanggal_update)<div style="font-size:9px;color:var(--muted);margin-top:2px">{{ $p->tanggal_update }}</div>@endif
        @if($p->catatan)<div style="font-size:9px;color:var(--muted);margin-top:1px">{{ Str::limit($p->catatan,20) }}</div>@endif
      </div>
      @endforeach
    </div>
  </div>
</div>

<div class="grid-2">
  {{-- Logbook --}}
  <div class="card">
    <div class="card-header"><h3>Logbook Harian</h3><p>{{ $mahasiswa->logbooks->count() }} entri</p></div>
    <table>
      <thead><tr><th>Tanggal</th><th>Kegiatan</th><th>Status</th></tr></thead>
      <tbody>
        @forelse($mahasiswa->logbooks->take(8) as $l)
        <tr>
          <td style="font-size:11px;color:var(--muted);white-space:nowrap">{{ $l->tanggal }}</td>
          <td style="font-size:12px">{{ Str::limit($l->kegiatan, 60) }}</td>
          <td><span class="pill pill-{{ $l->status_instansi }}">{{ $l->status_instansi }}</span></td>
        </tr>
        @empty
          <tr><td colspan="3" style="text-align:center;color:var(--muted);padding:18px">Belum ada logbook</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Surat & Seminar --}}
  <div>
    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><h3>Seminar KP</h3></div>
      <div class="card-body" style="font-size:13px">
        @if($mahasiswa->seminar)
        @php $s = $mahasiswa->seminar; @endphp
        <div style="display:grid;gap:8px">
          <div><span style="color:var(--muted)">Tanggal:</span> <strong>{{ $s->tanggal }}</strong></div>
          <div><span style="color:var(--muted)">Jam:</span> {{ \Carbon\Carbon::parse($s->jam)->format('H:i') }} WIB · {{ $s->ruangan }}</div>
          <div><span style="color:var(--muted)">Penguji:</span> {{ $s->dosen_penguji ?? '–' }}</div>
          <div><span style="color:var(--muted)">Status:</span> <span class="pill pill-{{ $s->status }}">{{ $s->status }}</span></div>
          @if($s->nilai)<div><span style="color:var(--muted)">Nilai Seminar:</span> <strong style="color:var(--dosen-l);font-size:18px">{{ $s->nilai }}</strong></div>@endif
        </div>
        @else
          <p style="color:var(--muted)">Belum ada jadwal seminar.</p>
        @endif
      </div>
    </div>
    <div class="card">
      <div class="card-header"><h3>Pengajuan Surat</h3></div>
      <table>
        <thead><tr><th>Jenis</th><th>Tanggal</th><th>Status</th></tr></thead>
        <tbody>
          @forelse($mahasiswa->surats as $s)
          <tr>
            <td style="font-size:12px">{{ ucfirst($s->jenis) }}</td>
            <td style="font-size:11px;color:var(--muted)">{{ $s->created_at->format('d/m/Y') }}</td>
            <td><span class="pill pill-{{ $s->status }}">{{ $s->status }}</span></td>
          </tr>
          @empty
            <tr><td colspan="3" style="text-align:center;color:var(--muted);padding:14px">Belum ada surat</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection