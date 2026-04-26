@extends('layouts.app')
@section('title','Dashboard Admin')
@section('content')

<div class="page-header">
  <h1>Dashboard Admin</h1>
  <p>Selamat datang kembali! Berikut ringkasan monitoring KP hari ini.</p>
</div>

<div class="stats-grid stats-4">
  <div class="stat-card c-admin">
    <div class="stat-label">Total Mahasiswa KP</div>
    <div class="stat-val">{{ $stats['total_mahasiswa'] }}</div>
    <div class="stat-sub">Terdaftar semester ini</div>
    <div class="stat-icon">🎓</div>
  </div>
  <div class="stat-card c-inst">
    <div class="stat-label">Sedang Proses</div>
    <div class="stat-val">{{ $stats['proses'] }}</div>
    <div class="stat-sub">Masih berjalan</div>
    <div class="stat-icon">⚙️</div>
  </div>
  <div class="stat-card c-mhs">
    <div class="stat-label">Tahap Seminar</div>
    <div class="stat-val">{{ $stats['seminar'] }}</div>
    <div class="stat-sub">Siap seminar</div>
    <div class="stat-icon">🎤</div>
  </div>
  <div class="stat-card c-dosen">
    <div class="stat-label">Telah Selesai</div>
    <div class="stat-val">{{ $stats['selesai'] }}</div>
    <div class="stat-sub">KP tuntas</div>
    <div class="stat-icon">✅</div>
  </div>
</div>

@if($stats['surat_pending'] > 0)
  <div class="alert alert-warning">⚠️ Ada <strong>{{ $stats['surat_pending'] }} pengajuan surat</strong> yang menunggu persetujuan Anda. <a href="{{ route('admin.surat.index') }}" style="color:inherit;font-weight:700;margin-left:8px">Lihat →</a></div>
@endif

<div class="grid-2">
  {{-- Tabel Mahasiswa --}}
  <div class="card">
    <div class="card-header">
      <div><h3>Status Mahasiswa KP</h3><p>5 mahasiswa terakhir</p></div>
      <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-ghost btn-sm">Lihat Semua</a>
    </div>
    <table>
      <thead><tr><th>Mahasiswa</th><th>Instansi</th><th>Progress</th><th>Status</th></tr></thead>
      <tbody>
        @foreach($mahasiswas as $m)
        @php $pct = $m->progressPersen(); @endphp
        <tr>
          <td>
            <strong>{{ $m->nama }}</strong><br>
            <code>{{ $m->nim }}</code>
          </td>
          <td style="font-size:12px;color:var(--muted)">{{ $m->instansi?->nama ?? '–' }}</td>
          <td style="min-width:100px">
            <div class="prog-wrap">
              <div class="prog-bar" style="width:{{ $pct }}%;background:{{ $pct==100?'var(--dosen)':($pct>50?'var(--inst)':'var(--admin)') }}"></div>
            </div>
            <div class="prog-txt">{{ $pct }}%</div>
          </td>
          <td><span class="pill pill-{{ $m->status }}">{{ ucfirst($m->status) }}</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Seminar --}}
  <div class="card">
    <div class="card-header">
      <div><h3>Jadwal Seminar Terdekat</h3></div>
      <a href="{{ route('admin.seminar.index') }}" class="btn btn-ghost btn-sm">Kelola</a>
    </div>
    <div class="card-body">
      @forelse($seminars as $s)
      <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border)">
        <div style="background:rgba(99,102,241,.15);border-radius:8px;padding:8px 12px;text-align:center;min-width:50px">
          <div style="font-size:18px;font-weight:800;color:var(--admin-l)">{{ \Carbon\Carbon::parse($s->tanggal)->format('d') }}</div>
          <div style="font-size:10px;color:var(--muted)">{{ \Carbon\Carbon::parse($s->tanggal)->format('M') }}</div>
        </div>
        <div>
          <div style="font-size:13px;font-weight:600">{{ $s->mahasiswa?->nama }}</div>
          <div style="font-size:11px;color:var(--muted)">{{ \Carbon\Carbon::parse($s->jam)->format('H:i') }} WIB · {{ $s->ruangan }}</div>
        </div>
        <span class="pill pill-{{ $s->status }}" style="margin-left:auto">{{ $s->status }}</span>
      </div>
      @empty
        <p style="color:var(--muted);font-size:13px;text-align:center;padding:20px">Belum ada jadwal seminar.</p>
      @endforelse
    </div>
  </div>
</div>

@endsection