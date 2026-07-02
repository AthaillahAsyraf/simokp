@extends('layouts.app')
@section('title','Dashboard Dosen')
@section('content')

<div class="page-header">
  <h1>Dashboard Dosen Pembimbing</h1>
  <p>Selamat datang, {{ $dosen->nama }}!</p>
</div>

<div class="stats-grid stats-4">
  <div class="stat-card c-dosen"><div class="stat-label">Total Bimbingan</div><div class="stat-val">{{ $stats['total'] }}</div><div class="stat-icon">🎓</div></div>
  <div class="stat-card c-inst"><div class="stat-label">Sedang Proses</div><div class="stat-val">{{ $stats['proses'] }}</div><div class="stat-icon">⚙️</div></div>
  <div class="stat-card c-mhs"><div class="stat-label">Tahap Seminar</div><div class="stat-val">{{ $stats['seminar'] }}</div><div class="stat-icon">🎤</div></div>
  <div class="stat-card c-admin"><div class="stat-label">Selesai</div><div class="stat-val">{{ $stats['selesai'] }}</div><div class="stat-icon">✅</div></div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-header">
      <h3>Perlu Verifikasi</h3>
      @if($pendingVerifikasi->count())
        <span class="badge badge-proses">{{ $pendingVerifikasi->count() }} menunggu</span>
      @endif
    </div>
    <div class="card-body">
      @forelse($pendingVerifikasi as $p)
        <div class="ver-row">
          @if($p->mahasiswa?->foto_profil)
            <img src="{{ $p->mahasiswa->fotoUrl() }}" alt="{{ $p->mahasiswa->nama }}" class="mhs-avatar">
          @else
            <div class="mhs-avatar mhs-avatar-fallback">{{ $p->mahasiswa?->inisial() }}</div>
          @endif
          <div class="ver-info">
            <div class="ver-name">{{ $p->mahasiswa?->nama }}</div>
            <div class="ver-sub">BAB {{ $p->bab_ke }} · diajukan {{ \Carbon\Carbon::parse($p->tanggal_update)->diffForHumans() }}</div>
          </div>
          <a href="{{ route('dosen.progress.index') }}" class="btn btn-sm btn-primary">Review</a>
        </div>
      @empty
        <p class="empty-note">🎉 Tidak ada laporan yang perlu diverifikasi.</p>
      @endforelse
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3>Seminar Terdekat</h3></div>
    <div class="card-body">
      @forelse($seminars as $i => $s)
        <div class="sem-row @if($i === 0) sem-next @endif">
          <div class="sem-date">
            <div class="sem-day">{{ \Carbon\Carbon::parse($s->tanggal)->format('d') }}</div>
            <div class="sem-month">{{ \Carbon\Carbon::parse($s->tanggal)->format('M') }}</div>
          </div>
          <div class="sem-info">
            <div class="sem-name">{{ $s->mahasiswa?->nama }}</div>
            <div class="sem-sub">{{ \Carbon\Carbon::parse($s->jam)->format('H:i') }} · {{ $s->ruangan }}</div>
          </div>
          @if($i === 0)
            <span class="sem-countdown">{{ \Carbon\Carbon::parse($s->tanggal)->diffForHumans(null, true) }} lagi</span>
          @else
            <span class="pill pill-{{ $s->status }}">{{ $s->status }}</span>
          @endif
        </div>
      @empty
        <p class="empty-note">Tidak ada seminar dalam waktu dekat.</p>
      @endforelse
    </div>
  </div>
</div>

<style>
.mhs-avatar{width:36px;height:36px;border-radius:50%;flex-shrink:0;object-fit:cover;border:2px solid var(--green-200)}
.mhs-avatar-fallback{display:flex;align-items:center;justify-content:center;background:var(--green-50);font-size:14px;font-weight:700;color:var(--green-600)}
.ver-row{display:flex;align-items:center;gap:10px;padding:12px 0;border-bottom:1px solid var(--gray-100)}
.ver-info{flex:1;min-width:0}
.ver-name{font-size:13px;font-weight:700;color:var(--gray-900)}
.ver-sub{font-size:11px;color:var(--gray-500)}
.sem-row{display:flex;align-items:center;gap:12px;padding:9px 0;border-bottom:1px solid var(--border)}
.sem-next{background:rgba(16,185,129,.06);border-radius:10px;padding:9px 10px;border-bottom:none;margin-bottom:4px}
.sem-date{background:rgba(16,185,129,.15);border-radius:8px;padding:7px 11px;text-align:center;min-width:46px}
.sem-day{font-size:17px;font-weight:800;color:var(--dosen-l)}
.sem-month{font-size:10px;color:var(--muted)}
.sem-info{flex:1}
.sem-name{font-size:13px;font-weight:600}
.sem-sub{font-size:11px;color:var(--muted)}
.sem-countdown{font-size:11px;font-weight:700;color:var(--dosen-l);background:rgba(16,185,129,.12);padding:4px 9px;border-radius:20px;white-space:nowrap}
.empty-note{color:var(--gray-400);font-size:13px;text-align:center;padding:20px}
</style>
@endsection