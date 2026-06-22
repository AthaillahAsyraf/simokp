@extends('layouts.app')
@section('title','Info Seminar')
@section('content')
<div class="page-header"><h1>Info Seminar KP</h1><p>Jadwal dan informasi seminar Kerja Praktik Anda</p></div>

@if($mahasiswa->seminar)
@php $s = $mahasiswa->seminar; @endphp
<div class="card">
  <div class="card-header"><h3>🎤 Jadwal Seminar KP Anda</h3><span class="pill pill-{{ $s->status }}">{{ str_replace('_',' ',$s->status) }}</span></div>
  <div class="card-body">
    <div class="grid-2" style="gap:20px;margin-bottom:20px">
      <div style="background:rgba(99,102,241,.1);border-radius:12px;padding:24px;text-align:center">
        <div style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">Tanggal Seminar</div>
        <div style="font-size:42px;font-weight:800;color:var(--admin-l);margin:8px 0">{{ \Carbon\Carbon::parse($s->tanggal)->format('d') }}</div>
        <div style="color:var(--muted);font-size:14px">{{ \Carbon\Carbon::parse($s->tanggal)->translatedFormat('F Y') }}</div>
      </div>
      <div style="display:flex;flex-direction:column;gap:14px;font-size:13px">
        <div><div style="color:var(--muted);font-size:11px;margin-bottom:3px">JAM PELAKSANAAN</div><strong style="font-size:20px">{{ \Carbon\Carbon::parse($s->jam)->format('H:i') }} WIB</strong></div>
        <div><div style="color:var(--muted);font-size:11px;margin-bottom:3px">RUANGAN</div><strong>{{ $s->ruangan }}</strong></div>
        <div><div style="color:var(--muted);font-size:11px;margin-bottom:3px">DOSEN PENGUJI</div><strong>{{ $s->dosen_penguji ?? '–' }}</strong></div>
        @if($s->nilai)
          <div><div style="color:var(--muted);font-size:11px;margin-bottom:3px">NILAI SEMINAR</div><strong style="font-size:28px;color:var(--dosen-l)">{{ $s->nilai }}</strong></div>
        @endif
      </div>
    </div>
    @if($s->nilai)
      <div class="alert alert-success">🎉 Selamat! Seminar KP Anda telah selesai dengan nilai <strong>{{ $s->nilai }}</strong>.</div>
    @else
      <div class="alert alert-info">📌 Hadir tepat waktu dan siapkan presentasi laporan KP Anda dengan baik.</div>
    @endif
  </div>
</div>
@else
<div class="card">
  <div class="card-body" style="text-align:center;padding:40px">
    <div style="font-size:48px;margin-bottom:16px">🗓️</div>
    <p style="color:var(--muted);font-size:14px">Jadwal seminar belum tersedia.<br>Admin akan menjadwalkan setelah laporan KP Anda selesai 100%.</p>
  </div>
</div>
@endif

<div class="card" style="margin-top:20px">
  <div class="card-header"><h3>📋 Checklist Syarat Seminar KP</h3></div>
  <div class="card-body" style="display:grid;gap:12px">
@php
  $checks = [
    ['Laporan BAB I–V selesai 100%', $mahasiswa->progressPersen() === 100],
    ['Status sudah mencapai tahap seminar', $mahasiswa->status === 'seminar' || $mahasiswa->status === 'selesai'],
    ['Data mahasiswa lengkap', !!($mahasiswa->nim && $mahasiswa->instansi)],
  ];
@endphp
    @foreach($checks as [$label, $done])
    <div style="display:flex;align-items:center;gap:12px;padding:10px;background:{{ $done?'rgba(34,197,94,.05)':'var(--surface2)' }};border-radius:8px;border:1px solid {{ $done?'rgba(34,197,94,.2)':'var(--border)' }}">
      <span style="font-size:18px">{{ $done ? '✅' : '⬜' }}</span>
      <span style="color:{{ $done?'var(--text)':'var(--muted)' }};font-size:13px">{{ $label }}</span>
    </div>
    @endforeach
  </div>
</div>
@endsection