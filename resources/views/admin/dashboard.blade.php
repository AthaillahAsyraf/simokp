@extends('layouts.app')
@section('title','Dashboard')
@section('content')
<div class="page-header">
  <h1>Dashboard Admin</h1>
  <p>Ringkasan monitoring Kerja Praktik — Ilmu Komputer Unila</p>
</div>

<div class="stats-grid stats-4" style="grid-template-columns:repeat(3,1fr) 1fr">
  <div class="stat-card c-blue">
    <div class="stat-label">Sedang Proses</div>
    <div class="stat-val">{{ $stats['proses'] }}</div>
    <div class="stat-sub">dari {{ $stats['total'] }} mahasiswa</div>
    <div class="stat-icon">⚙️</div>
  </div>
  <div class="stat-card c-amber">
    <div class="stat-label">Tahap Seminar</div>
    <div class="stat-val">{{ $stats['seminar'] }}</div>
    <div class="stat-sub">Semua BAB selesai</div>
    <div class="stat-icon">🎤</div>
  </div>
  <div class="stat-card c-green">
    <div class="stat-label">KP Selesai</div>
    <div class="stat-val">{{ $stats['selesai'] }}</div>
    <div class="stat-sub">Seminar sudah dilaksanakan</div>
    <div class="stat-icon">✅</div>
  </div>
  <div class="stat-card c-purple">
    <div class="stat-label">Total</div>
    <div class="stat-val">{{ $stats['total'] }}</div>
    <div class="stat-sub">{{ $stats['dosen'] }} dosen · {{ $stats['instansi'] }} instansi</div>
    <div class="stat-icon">📊</div>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-header">
      <div><h3>Progress Mahasiswa</h3><p>Terbaru {{ $mahasiswas->count() }} mahasiswa</p></div>
      <a href="{{ route('admin.progress.index') }}" class="btn btn-outline btn-sm">Lihat Semua</a>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Mahasiswa</th><th>Instansi</th><th>Progress</th><th>Status</th></tr></thead>
        <tbody>
          @foreach($mahasiswas as $m)
          @php $pct = $m->progressPersen(); @endphp
          <tr>
            <td>
              <div class="fw-bold">{{ $m->nama }}</div>
              <code>{{ $m->nim }}</code>
            </td>
            <td class="text-sm text-muted">{{ $m->instansi?->nama ?? '–' }}</td>
            <td style="min-width:120px">
              <div class="prog-wrap" style="height:6px;margin-bottom:3px">
                <div class="prog-bar prog-bar-{{ $pct==100?'green':($pct>40?'amber':'blue') }}" style="width:{{ $pct }}%"></div>
              </div>
              <div class="text-sm text-muted">{{ $m->progressBabs->where('status','selesai')->count() }}/5 BAB · {{ $pct }}%</div>
            </td>
            <td><span class="badge badge-{{ $m->status }}">{{ ucfirst($m->status) }}</span></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div>
    <div class="card" style="margin-bottom:16px">
      <div class="card-header">
        <div><h3>Jadwal Seminar</h3></div>
        <a href="{{ route('admin.seminar.index') }}" class="btn btn-outline btn-sm">Kelola</a>
      </div>
      <div class="card-body" style="padding:12px 16px">
        @forelse($seminars as $s)
        <div style="display:flex;align-items:center;gap:12px;padding:9px 0;border-bottom:1px solid #f1f5f9">
          <div style="background:#eff6ff;border-radius:8px;padding:6px 10px;text-align:center;min-width:46px;flex-shrink:0">
            <div style="font-size:16px;font-weight:800;color:#2563eb">{{ \Carbon\Carbon::parse($s->tanggal)->format('d') }}</div>
            <div style="font-size:10px;color:#64748b">{{ \Carbon\Carbon::parse($s->tanggal)->format('M') }}</div>
          </div>
          <div style="flex:1">
            <div class="fw-bold text-sm">{{ $s->mahasiswa?->nama }}</div>
            <div class="text-sm text-muted">{{ \Carbon\Carbon::parse($s->jam)->format('H:i') }} · {{ $s->ruangan }}</div>
          </div>
          <span class="badge badge-{{ $s->status }}">{{ $s->status }}</span>
        </div>
        @empty
          <div class="empty-state" style="padding:20px"><div class="icon">🗓️</div><p>Belum ada jadwal</p></div>
        @endforelse
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3>Ringkasan Data</h3></div>
      <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <a href="{{ route('admin.dosen.index') }}" style="display:flex;align-items:center;gap:10px;padding:12px;background:#f0fdf4;border-radius:8px;border:1px solid #dcfce7">
            <span style="font-size:22px">👨‍🏫</span>
            <div><div class="fw-bold" style="color:#15803d">{{ $stats['dosen'] }}</div><div class="text-sm" style="color:#16a34a">Dosen</div></div>
          </a>
          <a href="{{ route('admin.instansi.index') }}" style="display:flex;align-items:center;gap:10px;padding:12px;background:#fffbeb;border-radius:8px;border:1px solid #fef3c7">
            <span style="font-size:22px">🏢</span>
            <div><div class="fw-bold" style="color:#92400e">{{ $stats['instansi'] }}</div><div class="text-sm" style="color:#d97706">Instansi</div></div>
          </a>
          <a href="{{ route('admin.mahasiswa.index') }}" style="display:flex;align-items:center;gap:10px;padding:12px;background:#eff6ff;border-radius:8px;border:1px solid #dbeafe;grid-column:span 2">
            <span style="font-size:22px">🎓</span>
            <div><div class="fw-bold" style="color:#1e40af">{{ $stats['total'] }}</div><div class="text-sm" style="color:#2563eb">Total Mahasiswa KP</div></div>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection