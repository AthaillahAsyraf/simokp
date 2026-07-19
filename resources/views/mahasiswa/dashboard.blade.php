@extends('layouts.app')
@section('title','Dashboard Mahasiswa')

@section('content')
@php $pct = $mahasiswa->progressPersen(); @endphp

<div class="page-header">
  <h1>Halo, {{ $mahasiswa->nama }}! 👋</h1>
  <p>Pantau progress Kerja Praktik Anda di sini.</p>
</div>

@if(!$mahasiswa->sudahAktifKp())
  @include('partials.tahapan-kp', ['mahasiswa' => $mahasiswa])
@endif

<div class="stats-grid stats-2">
  <div class="stat-card c-mhs">
    <div class="stat-label">Progress Laporan</div>
    <div class="stat-val">{{ $pct }}%</div>
    <div class="stat-sub">
      {{ $mahasiswa->progressBabs->where('status','selesai')->count() }}/5 BAB selesai
    </div>
    <div class="stat-icon">📚</div>
  </div>

  <div class="stat-card c-inst">
    <div class="stat-label">Status KP</div>
    <div class="stat-val" style="font-size:20px;padding-top:6px">
      {{ ['proses'=>'Proses ⚙️','seminar'=>'Seminar 🎤','selesai'=>'Selesai ✅'][$mahasiswa->status] }}
    </div>
  </div>
</div>

<div class="grid-2">

  {{-- Progress Timeline --}}
  <div class="card">
    <div class="card-header">
      <h3>Progress Laporan KP</h3>
      <span style="font-weight:700">{{ $pct }}%</span>
    </div>

    <div class="card-body">
      <div style="margin-bottom:20px">
        <div class="prog-wrap" style="height:8px">
          <div class="prog-bar prog-bar-purple" style="width:{{ $pct }}%"></div>
        </div>
      </div>

      @foreach($mahasiswa->progressBabs as $p)
      <div style="display:flex;align-items:flex-start;gap:12px;padding:8px 0;border-bottom:1px solid #eee">
        <div style="width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;
          background:{{ $p->status==='selesai'?'#dcfce7':'#f1f5f9' }};
          color:{{ $p->status==='selesai'?'#16a34a':'#64748b' }}">
          {{ $p->status==='selesai'?'✓':'·' }}
        </div>

        <div style="flex:1">
          <div style="font-size:13px;font-weight:600">{{ $p->bab }}</div>
          <div style="font-size:11px;color:#64748b">
            {{ $p->status==='selesai'
                ? 'Selesai '.($p->tanggal_update ?? '')
                : 'Belum dimulai' }}
            @if($p->catatan) · {{ $p->catatan }} @endif
          </div>
        </div>

        <a href="{{ route('mahasiswa.progress.index') }}" style="font-size:11px">
          Edit
        </a>
      </div>
      @endforeach
    </div>
  </div>

  <div>

    {{-- Info KP --}}
    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><h3>Info KP Saya</h3></div>

      <div class="card-body" style="font-size:13px;display:grid;gap:10px">
        <div>
          <div style="font-size:11px;color:#64748b">NIM</div>
          <code>{{ $mahasiswa->nim }}</code>
        </div>

        <div>
          <div style="font-size:11px;color:#64748b">Instansi</div>
          <strong>{{ $mahasiswa->instansi?->nama ?? '–' }}</strong>
        </div>

        <div>
          <div style="font-size:11px;color:#64748b">Dosen Pembimbing</div>
          <strong>{{ $mahasiswa->dosen?->nama ?? 'Belum ditentukan' }}</strong>
        </div>

        <div>
          <div style="font-size:11px;color:#64748b">Tanggal Mulai</div>
          {{ $mahasiswa->tanggal_mulai ?? '–' }}
        </div>
      </div>
    </div>

    {{-- Seminar --}}
    @if($mahasiswa->seminar)
    <div class="card">
      <div class="card-header">
        <h3>🎤 Info Seminar</h3>
        <span class="badge badge-{{ $mahasiswa->seminar->status }}">
          {{ $mahasiswa->seminar->status }}
        </span>
      </div>

      <div class="card-body" style="font-size:13px;display:grid;gap:8px">
        <div>
          <div style="font-size:11px;color:#64748b">Tanggal & Jam</div>
          <strong>
            {{ $mahasiswa->seminar->tanggal }} ·
            {{ \Carbon\Carbon::parse($mahasiswa->seminar->jam)->format('H:i') }} WIB
          </strong>
        </div>

        <div>
          <div style="font-size:11px;color:#64748b">Ruangan</div>
          {{ $mahasiswa->seminar->ruangan }}
        </div>

        <div>
          <div style="font-size:11px;color:#64748b">Dosen Penguji</div>
          {{ $mahasiswa->seminar->dosen_penguji ?? '–' }}
        </div>

        @if($mahasiswa->seminar->nilai)
        <div>
          <div style="font-size:11px;color:#64748b">Nilai Seminar</div>
          <strong style="font-size:22px">
            {{ $mahasiswa->seminar->nilai }}
          </strong>
        </div>
        @endif
      </div>
    </div>
    @endif

  </div>
</div>
@endsection