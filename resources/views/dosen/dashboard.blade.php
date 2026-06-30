@extends('layouts.app')
@section('title','Dashboard Dosen')
@section('content')

<div class="page-header">
  <h1>Dashboard Dosen Pembimbing</h1>
  <p>Selamat datang, {{ $dosen->nama }}!</p>
</div>

<div class="stats-grid stats-4">
  <div class="stat-card c-dosen"><div class="stat-label">Total Bimbingan</div><div class="stat-val">{{ $stats['total'] }}</div><div class="stat-sub">Mahasiswa KP Anda</div><div class="stat-icon">🎓</div></div>
  <div class="stat-card c-inst"><div class="stat-label">Sedang Proses</div><div class="stat-val">{{ $stats['proses'] }}</div><div class="stat-icon">⚙️</div></div>
  <div class="stat-card c-mhs"><div class="stat-label">Tahap Seminar</div><div class="stat-val">{{ $stats['seminar'] }}</div><div class="stat-icon">🎤</div></div>
  <div class="stat-card c-admin"><div class="stat-label">Selesai</div><div class="stat-val">{{ $stats['selesai'] }}</div><div class="stat-icon">✅</div></div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-header"><div><h3>Mahasiswa Bimbingan</h3></div><a href="{{ route('dosen.progress.index') }}" class="btn btn-ghost btn-sm">Update Progress</a></div>
    <div class="card-body">
      @forelse($mahasiswas as $m)
      @php $pct = $m->progressPersen(); @endphp
      <div style="padding:12px 0;border-bottom:1px solid var(--gray-100)">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
          @if($m->foto_profil)
            <img src="{{ $m->fotoUrl() }}" alt="{{ $m->nama }}"
                 style="width:36px;height:36px;border-radius:50%;object-fit:cover;
                        border:2px solid var(--green-200);flex-shrink:0">
          @else
            <div style="width:36px;height:36px;border-radius:50%;flex-shrink:0;
                        background:var(--green-50);border:2px solid var(--green-200);
                        display:flex;align-items:center;justify-content:center;
                        font-size:14px;font-weight:700;color:var(--green-600)">
              {{ $m->inisial() }}
            </div>
          @endif
          <div style="flex:1;min-width:0">
            <div style="font-size:13px;font-weight:700;color:var(--gray-900)">{{ $m->nama }}</div>
            <div style="font-size:11px;color:var(--gray-500)">{{ $m->nim }} · {{ $m->instansi?->nama ?? '–' }}</div>
          </div>
          <span class="badge badge-{{ $m->status }}">{{ ucfirst($m->status) }}</span>
        </div>
        <div class="prog-wrap" style="height:5px">
          <div class="prog-bar prog-bar-green" style="width:{{ $pct }}%"></div>
        </div>
        <div style="font-size:11px;color:var(--gray-400);margin-top:3px">
          {{ $pct }}% · {{ $m->progressBabs->where('status','selesai')->count() }}/5 BAB selesai
        </div>
      </div>
      @empty
        <p style="color:var(--gray-400);font-size:13px;text-align:center;padding:20px">Belum ada mahasiswa bimbingan.</p>
      @endforelse
    </div>
  </div>

  <div>
    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><h3>Profil Dosen</h3></div>
      <div class="card-body" style="font-size:13px">
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px">
          <div class="avatar" style="width:56px;height:56px;font-size:28px;background:rgba(16,185,129,.2)">👨‍🏫</div>
          <div>
            <div style="font-size:16px;font-weight:700">{{ $dosen->nama }}</div>
            <div style="color:var(--muted)">{{ $dosen->bidang ?? 'Dosen Pembimbing' }}</div>
            <div style="color:var(--muted);font-size:11px">NIP: {{ $dosen->nip }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3>Jadwal Seminar Bimbingan</h3></div>
      <div class="card-body">
        @forelse($seminars as $s)
        <div style="padding:9px 0;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px">
          <div style="background:rgba(16,185,129,.15);border-radius:8px;padding:7px 11px;text-align:center;min-width:46px">
            <div style="font-size:17px;font-weight:800;color:var(--dosen-l)">{{ \Carbon\Carbon::parse($s->tanggal)->format('d') }}</div>
            <div style="font-size:10px;color:var(--muted)">{{ \Carbon\Carbon::parse($s->tanggal)->format('M') }}</div>
          </div>
          <div>
            <div style="font-size:13px;font-weight:600">{{ $s->mahasiswa?->nama }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ \Carbon\Carbon::parse($s->jam)->format('H:i') }} · {{ $s->ruangan }}</div>
          </div>
          <span class="pill pill-{{ $s->status }}" style="margin-left:auto">{{ $s->status }}</span>
        </div>
        @empty
          <p style="color:var(--muted);font-size:13px;text-align:center;padding:16px">Belum ada jadwal seminar.</p>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection