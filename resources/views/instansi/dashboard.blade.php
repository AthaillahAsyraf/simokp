@extends('layouts.app')
@section('title','Dashboard Instansi')
@section('content')
<div class="page-header">
  <h1>Dashboard Instansi</h1>
  <p>{{ $instansi->nama }} — Selamat datang!</p>
</div>
@if($stats['logbook_pending'] > 0)
  <div class="alert alert-warning">⚠️ Ada <strong>{{ $stats['logbook_pending'] }} logbook</strong> mahasiswa yang menunggu verifikasi Anda. <a href="{{ route('instansi.logbook.index') }}" style="color:inherit;font-weight:700;margin-left:6px">Verifikasi sekarang →</a></div>
@endif

<div class="stats-grid stats-3">
  <div class="stat-card c-inst"><div class="stat-label">Mahasiswa KP</div><div class="stat-val">{{ $stats['total'] }}</div><div class="stat-sub">Di instansi Anda</div><div class="stat-icon">🎓</div></div>
 
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-header"><h3>Mahasiswa KP Anda</h3><button class="btn btn-ghost btn-sm" disabled>Beri Nilai</button></div>
    <div class="card-body">
      @forelse($mahasiswas as $m)
      @php $pct = $m->progressPersen(); @endphp
      <div style="padding:10px 0;border-bottom:1px solid var(--border)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
          <div>
            <div style="font-size:14px;font-weight:700">{{ $m->nama }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ $m->nim }} · Pembimbing: {{ $m->dosen?->nama ?? '–' }}</div>
          </div>
          <span class="pill pill-{{ $m->status }}">{{ ucfirst($m->status) }}</span>
        </div>
        <div class="prog-wrap"><div class="prog-bar" style="width:{{ $pct }}%;background:var(--inst)"></div></div>
        <div class="prog-txt">{{ $pct }}% laporan selesai</div>
      </div>
      @empty <p style="color:var(--muted);font-size:13px;text-align:center;padding:20px">Belum ada mahasiswa KP.</p>
      @endforelse
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3>Profil Instansi</h3></div>
    <div class="card-body" style="font-size:13px;display:grid;gap:12px">
      <div><div style="color:var(--muted);font-size:11px">Nama Instansi</div><strong>{{ $instansi->nama }}</strong></div>
      <div><div style="color:var(--muted);font-size:11px">Bidang</div>{{ $instansi->bidang ?? '–' }}</div>
      <div><div style="color:var(--muted);font-size:11px">Alamat</div>{{ $instansi->alamat ?? '–' }}</div>
      <div><div style="color:var(--muted);font-size:11px">Kontak Person</div>{{ $instansi->kontak_person ?? '–' }}</div>
      <div><div style="color:var(--muted);font-size:11px">No. HP</div>{{ $instansi->no_hp ?? '–' }}</div>
    </div>
  </div>
</div>
@endsection