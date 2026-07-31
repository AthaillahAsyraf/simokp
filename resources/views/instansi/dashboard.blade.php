@extends('layouts.app')
@section('title','Dashboard Pembimbing Lapangan')
@section('content')

@php
  // Turunan data dari $mahasiswas yang sudah dikirim controller — tidak butuh query baru
  $statusCounts   = $mahasiswas->groupBy('status')->map->count();
  $avgProgress    = $mahasiswas->count() ? round($mahasiswas->avg(fn($m) => $m->progressPersen())) : 0;

  // Perlu disorot: mahasiswa yang belum diberi nilai lapangan oleh pembimbing.
  // Mahasiswa yang sudah selesai KP tidak perlu terus muncul di sini.
  $perluPerhatian = $mahasiswas
      ->filter(fn($m) => is_null($m->nilai?->nilai_lapangan) && $m->status !== 'selesai')
      ->sortBy(fn($m) => $m->nama)
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
</div>

<div class="grid-2">

  <div class="card">
    <div class="card-header">
      <h3>Perlu Perhatian</h3>
      <a href="{{ route('instansi.nilai.index') }}" class="btn btn-ghost btn-sm">Lihat Semua →</a>
    </div>
    <div class="card-body">
      @forelse($perluPerhatian as $m)
        <div style="padding:10px 0;border-bottom:1px solid var(--border)">
          <div style="display:flex;align-items:center;justify-content:space-between">
            <div>
              <div style="font-size:14px;font-weight:700">{{ $m->nama }}</div>
              <div style="font-size:11px;color:var(--muted)">{{ $m->nim }}</div>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
              <span class="pill" style="background:var(--red-50);color:var(--red-600)">⚠️ Belum dinilai</span>
              <a href="{{ route('instansi.nilai.index') }}" class="btn btn-outline btn-xs">Input Nilai</a>
            </div>
          </div>
        </div>
      @empty
        <p style="color:var(--muted);font-size:13px;text-align:center;padding:20px">
          ✅ Semua mahasiswa aktif sudah diberi nilai lapangan.
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