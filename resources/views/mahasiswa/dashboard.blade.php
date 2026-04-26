@extends('layouts.app')
@section('title','Dashboard Mahasiswa')
@section('content')
@php $pct = $mahasiswa->progressPersen(); @endphp

<div class="page-header">
  <h1>Halo, {{ $mahasiswa->nama }}! 👋</h1>
  <p>Pantau progress Kerja Praktik Anda di sini.</p>
</div>

<div class="stats-grid stats-3">
  <div class="stat-card c-mhs"><div class="stat-label">Progress BAB</div><div class="stat-val">{{ $pct }}%</div><div class="stat-sub">{{ $mahasiswa->progressBabs->where('status','selesai')->count() }}/6 BAB selesai</div><div class="stat-icon">📚</div></div>
  <div class="stat-card c-dosen"><div class="stat-label">Logbook Terkirim</div><div class="stat-val">{{ $mahasiswa->logbooks->count() }}</div><div class="stat-sub">{{ $mahasiswa->logbooks->where('status_instansi','disetujui')->count() }} diverifikasi</div><div class="stat-icon">📋</div></div>
  <div class="stat-card c-inst"><div class="stat-label">Status KP</div><div class="stat-val" style="font-size:20px;padding-top:6px">{{ ['proses'=>'Proses ⚙️','seminar'=>'Seminar 🎤','selesai'=>'Selesai ✅'][$mahasiswa->status] }}</div></div>
</div>

<div class="grid-2">
  {{-- Progress Timeline --}}
  <div class="card">
    <div class="card-header"><h3>Progress Laporan KP</h3><span style="font-weight:700;color:var(--mhs-l)">{{ $pct }}%</span></div>
    <div class="card-body">
      <div style="margin-bottom:20px">
        <div class="prog-wrap" style="height:8px">
          <div class="prog-bar" style="width:{{ $pct }}%;background:var(--mhs)"></div>
        </div>
      </div>
      @foreach($mahasiswa->progressBabs as $p)
      <div style="display:flex;align-items:flex-start;gap:12px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.04)">
        <div style="width:26px;height:26px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:12px;
          background:{{ $p->status==='selesai'?'rgba(34,197,94,.2)':($p->status==='proses'?'rgba(245,158,11,.2)':'var(--surface2)') }};
          color:{{ $p->status==='selesai'?'#4ade80':($p->status==='proses'?'var(--inst-l)':'var(--muted)') }}">
          {{ $p->status==='selesai'?'✓':($p->status==='proses'?'→':'·') }}
        </div>
        <div style="flex:1">
          <div style="font-size:13px;font-weight:600">{{ $p->bab }}</div>
          <div style="font-size:11px;color:var(--muted)">
            {{ $p->status==='selesai'?'Selesai '.($p->tanggal_update??''):($p->status==='proses'?'Sedang dikerjakan':'Belum dimulai') }}
            @if($p->catatan) · {{ $p->catatan }} @endif
          </div>
        </div>
        <a href="{{ route('mahasiswa.progress.index') }}" style="color:var(--mhs-l);font-size:11px;margin-top:2px">Edit</a>
      </div>
      @endforeach
    </div>
  </div>

  <div>
    {{-- Info KP --}}
    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><h3>Info KP Saya</h3></div>
      <div class="card-body" style="font-size:13px;display:grid;gap:10px">
        <div><div style="color:var(--muted);font-size:11px">NIM</div><code>{{ $mahasiswa->nim }}</code></div>
        <div><div style="color:var(--muted);font-size:11px">Instansi</div><strong>{{ $mahasiswa->instansi?->nama ?? '–' }}</strong></div>
        <div><div style="color:var(--muted);font-size:11px">Bidang</div>{{ $mahasiswa->instansi?->bidang ?? '–' }}</div>
        <div><div style="color:var(--muted);font-size:11px">Dosen Pembimbing</div><strong>{{ $mahasiswa->dosen?->nama ?? 'Belum ditentukan' }}</strong></div>
        <div><div style="color:var(--muted);font-size:11px">Tanggal Mulai</div>{{ $mahasiswa->tanggal_mulai ?? '–' }}</div>
      </div>
    </div>

    {{-- Seminar --}}
    @if($mahasiswa->seminar)
    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><h3>🎤 Info Seminar</h3><span class="pill pill-{{ $mahasiswa->seminar->status }}">{{ $mahasiswa->seminar->status }}</span></div>
      <div class="card-body" style="font-size:13px;display:grid;gap:8px">
        <div><div style="color:var(--muted);font-size:11px">Tanggal & Jam</div><strong>{{ $mahasiswa->seminar->tanggal }} · {{ \Carbon\Carbon::parse($mahasiswa->seminar->jam)->format('H:i') }} WIB</strong></div>
        <div><div style="color:var(--muted);font-size:11px">Ruangan</div>{{ $mahasiswa->seminar->ruangan }}</div>
        <div><div style="color:var(--muted);font-size:11px">Dosen Penguji</div>{{ $mahasiswa->seminar->dosen_penguji ?? '–' }}</div>
        @if($mahasiswa->seminar->nilai)<div><div style="color:var(--muted);font-size:11px">Nilai Seminar</div><strong style="font-size:22px;color:var(--dosen-l)">{{ $mahasiswa->seminar->nilai }}</strong></div>@endif
      </div>
    </div>
    @endif

    {{-- Nilai --}}
    @if($mahasiswa->nilai && ($mahasiswa->nilai->nilai_instansi || $mahasiswa->nilai->nilai_pembimbing))
    <div class="card">
      <div class="card-header"><h3>🏆 Nilai KP</h3></div>
      <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">
          <div style="background:rgba(245,158,11,.1);border-radius:10px;padding:14px;text-align:center">
            <div style="font-size:10px;color:var(--muted)">INSTANSI</div>
            <div style="font-size:26px;font-weight:800;color:var(--inst-l)">{{ $mahasiswa->nilai->nilai_instansi ?? '–' }}</div>
          </div>
          <div style="background:rgba(16,185,129,.1);border-radius:10px;padding:14px;text-align:center">
            <div style="font-size:10px;color:var(--muted)">PEMBIMBING</div>
            <div style="font-size:26px;font-weight:800;color:var(--dosen-l)">{{ $mahasiswa->nilai->nilai_pembimbing ?? '–' }}</div>
          </div>
        </div>
        @if($mahasiswa->nilai->nilai_akhir)
          <div class="alert alert-success" style="justify-content:center">🏆 Nilai Akhir: <strong style="font-size:18px;margin-left:8px">{{ $mahasiswa->nilai->nilai_akhir }}</strong></div>
        @endif
      </div>
    </div>
    @endif
  </div>
</div>
@endsection