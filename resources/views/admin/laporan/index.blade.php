@extends('layouts.app')
@section('title','Laporan & Rekap')
@section('content')

<div class="page-header">
  <h1>Laporan & Rekap KP</h1>
  <p>Ringkasan statistik Kerja Praktik semester ini</p>
</div>

<div class="stats-grid stats-4">
  <div class="stat-card c-dosen"><div class="stat-label">Selesai</div><div class="stat-val">{{ $stats['selesai'] }}</div><div class="stat-sub">{{ $stats['total'] > 0 ? round($stats['selesai']/$stats['total']*100) : 0 }}% dari total</div><div class="stat-icon">✅</div></div>
  <div class="stat-card c-mhs"><div class="stat-label">Seminar</div><div class="stat-val">{{ $stats['seminar'] }}</div><div class="stat-sub">{{ $stats['total'] > 0 ? round($stats['seminar']/$stats['total']*100) : 0 }}% dari total</div><div class="stat-icon">🎤</div></div>
  <div class="stat-card c-inst"><div class="stat-label">Proses</div><div class="stat-val">{{ $stats['proses'] }}</div><div class="stat-sub">{{ $stats['total'] > 0 ? round($stats['proses']/$stats['total']*100) : 0 }}% dari total</div><div class="stat-icon">⚙️</div></div>
  <div class="stat-card c-admin"><div class="stat-label">Rata-rata Progress</div><div class="stat-val">{{ $stats['avg_progress'] }}%</div><div class="stat-sub">Seluruh mahasiswa</div><div class="stat-icon">📊</div></div>
</div>

<div class="grid-2">
  {{-- Tabel Nilai --}}
  <div class="card">
    <div class="card-header"><h3>Rekapitulasi Nilai Akhir</h3></div>
    <table>
      <thead><tr><th>Mahasiswa</th><th>Instansi</th><th>Pembimbing</th><th>Seminar</th><th>Akhir</th></tr></thead>
      <tbody>
        @foreach($mahasiswas as $m)
        @if($m->nilai && ($m->nilai->nilai_instansi || $m->nilai->nilai_pembimbing))
        <tr>
          <td><strong>{{ $m->nama }}</strong></td>
          <td style="text-align:center"><strong>{{ $m->nilai->nilai_instansi ?? '–' }}</strong></td>
          <td style="text-align:center"><strong>{{ $m->nilai->nilai_pembimbing ?? '–' }}</strong></td>
          <td style="text-align:center"><strong>{{ $m->nilai->nilai_seminar ?? '–' }}</strong></td>
          <td style="text-align:center">
            @if($m->nilai->nilai_akhir)
              <strong style="color:var(--dosen-l);font-size:15px">{{ $m->nilai->nilai_akhir }}</strong>
            @else
              <span style="color:var(--muted)">–</span>
            @endif
          </td>
        </tr>
        @endif
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Distribusi per Instansi --}}
  <div class="card">
    <div class="card-header"><h3>Distribusi Mahasiswa per Instansi</h3></div>
    <div class="card-body">
      @foreach($instansis as $inst)
      @php $pct = $stats['total'] > 0 ? round($inst->mahasiswas_count / $stats['total'] * 100) : 0; @endphp
      <div style="margin-bottom:18px">
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
          <span>{{ $inst->nama }}</span>
          <span style="color:var(--muted)">{{ $inst->mahasiswas_count }} mhs ({{ $pct }}%)</span>
        </div>
        <div class="prog-wrap" style="height:8px">
          <div class="prog-bar" style="width:{{ $pct }}%;background:var(--inst)"></div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</div>

{{-- Tabel Lengkap --}}
<div class="card">
  <div class="card-header"><h3>Rekap Lengkap Semua Mahasiswa</h3></div>
  <table>
    <thead><tr><th>NIM</th><th>Nama</th><th>Angkatan</th><th>Instansi</th><th>Dosen Pembimbing</th><th>Progress</th><th>Status</th><th>Nilai Akhir</th></tr></thead>
    <tbody>
      @foreach($mahasiswas as $m)
      @php $pct = $m->progressPersen(); @endphp
      <tr>
        <td><code>{{ $m->nim }}</code></td>
        <td><strong>{{ $m->nama }}</strong></td>
        <td>{{ $m->angkatan }}</td>
        <td style="font-size:12px">{{ $m->instansi?->nama ?? '–' }}</td>
        <td style="font-size:12px">{{ $m->dosen?->nama ?? '–' }}</td>
        <td>
          <div class="prog-wrap"><div class="prog-bar" style="width:{{ $pct }}%;background:var(--admin)"></div></div>
          <div class="prog-txt">{{ $pct }}%</div>
        </td>
        <td><span class="pill pill-{{ $m->status }}">{{ ucfirst($m->status) }}</span></td>
        <td><strong style="color:var(--dosen-l)">{{ $m->nilai?->nilai_akhir ?? '–' }}</strong></td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection