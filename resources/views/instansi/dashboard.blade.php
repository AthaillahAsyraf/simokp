@extends('layouts.app')
@section('title','Dashboard Pembimbing Lapangan')
@section('content')

@php
  // Turunan data dari $mahasiswas yang sudah dikirim controller — tidak butuh query baru
  $statusCounts   = $mahasiswas->groupBy('status')->map->count();
  $avgProgress    = $mahasiswas->count() ? round($mahasiswas->avg(fn($m) => $m->progressPersen())) : 0;

  // Hanya mahasiswa yang perlu disorot: progress rendah atau status bermasalah/ditolak
  $perluPerhatian = $mahasiswas
      ->filter(fn($m) => $m->progressPersen() < 30 || in_array($m->status, ['bermasalah','ditolak']))
      ->sortBy(fn($m) => $m->progressPersen())
      ->take(5);
@endphp

<div class="page-header">
  <h1>Dashboard Pembimbing Lapangan</h1>
  <p>{{ $instansi->nama }} — Selamat datang!</p>
</div>

<div class="stats-grid stats-3">
  <div class="stat-card c-inst">
    <div class="stat-label">Total Mahasiswa</div>
    <div class="stat-val">{{ $stats['total'] }}</div>
    <div class="stat-sub">Terdaftar di instansi Anda</div>
    <div class="stat-icon">🎓</div>
  </div>
  <div class="stat-card c-inst">
    <div class="stat-label">Sudah Dinilai</div>
    <div class="stat-val">{{ $stats['sudah_dinilai'] }}</div>
    <div class="stat-sub">Nilai lapangan sudah diisi</div>
    <div class="stat-icon">📝</div>
  </div>
  <div class="stat-card c-inst">
    <div class="stat-label">Progress Rata-rata</div>
    <div class="stat-val">{{ $avgProgress }}%</div>
    <div class="stat-sub">Seluruh mahasiswa</div>
    <div class="stat-icon">📊</div>
  </div>
</div>

<div class="grid-2">

  <div class="card">
    <div class="card-header">
      <h3>Perlu Perhatian</h3>
      <a href="{{ route('instansi.nilai.index') }}" class="btn btn-ghost btn-sm">Lihat Semua →</a>
    </div>
    <div class="card-body">
      @forelse($perluPerhatian as $m)
        @php $pct = $m->progressPersen(); @endphp
        <div style="padding:10px 0;border-bottom:1px solid var(--border)">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
            <div>
              <div style="font-size:14px;font-weight:700">{{ $m->nama }}</div>
              <div style="font-size:11px;color:var(--muted)">{{ $m->nim }}</div>
            </div>
            <span class="pill pill-{{ $m->status }}">{{ ucfirst($m->status) }}</span>
          </div>
          <div class="prog-wrap"><div class="prog-bar" style="width:{{ $pct }}%;background:var(--inst)"></div></div>
          <div class="prog-txt">{{ $pct }}% laporan selesai</div>
        </div>
      @empty
        <p style="color:var(--muted);font-size:13px;text-align:center;padding:20px">
          ✅ Semua mahasiswa progress-nya aman, tidak ada yang perlu perhatian khusus.
        </p>
      @endforelse
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3>Distribusi Status</h3></div>
    <div class="card-body" style="display:grid;gap:14px">
      @forelse($statusCounts as $status => $count)
        @php $pctStatus = $stats['total'] ? round($count / $stats['total'] * 100) : 0; @endphp
        <div>
          <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px">
            <span>{{ ucfirst($status) }}</span>
            <strong>{{ $count }} mahasiswa</strong>
          </div>
          <div class="prog-wrap"><div class="prog-bar" style="width:{{ $pctStatus }}%;background:var(--inst)"></div></div>
        </div>
      @empty
        <p style="color:var(--muted);font-size:13px;text-align:center;padding:20px">Belum ada data mahasiswa.</p>
      @endforelse
    </div>
  </div>

</div>

@endsection