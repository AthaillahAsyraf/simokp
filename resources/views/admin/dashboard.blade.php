@extends('layouts.app')
@section('title','Dashboard')
@section('content')
<div class="page-header">
  <h1>Dashboard Admin</h1>
  <p>Ringkasan monitoring Kerja Praktik — Ilmu Komputer Unila · {{ $stats['dosen'] }} dosen · {{ $stats['instansi'] }} instansi</p>
</div>

<div class="stats-grid" style="grid-template-columns:repeat(3,1fr)">
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
</div>

<div class="grid-2">
  <div>
    <!-- Insight #0: Berkas menunggu verifikasi -->
    @if($menungguBerkas->count() || $siapDitempatkan->count())
    <div class="card" style="margin-bottom:16px">
      <div class="card-header">
        <div>
          <h3>🗂️ Persyaratan KP Butuh Tindakan</h3>
          <p>{{ $menungguBerkas->count() }} menunggu verifikasi &middot; {{ $siapDitempatkan->count() }} siap ditempatkan</p>
        </div>
        <a href="{{ route('admin.mahasiswa.index', ['tahap' => 'menunggu_verifikasi']) }}" class="btn btn-outline btn-sm">Kelola</a>
      </div>
      <div class="card-body">
        @forelse($menungguBerkas as $m)
          <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--gray-100)">
            <div>
              <div style="font-size:13px;font-weight:600">{{ $m->nama }}</div>
              <div style="font-size:11px;color:var(--gray-500)">{{ $m->nim }} &middot; menunggu verifikasi berkas</div>
            </div>
            <span class="badge badge-proses">🕓 Verifikasi</span>
          </div>
        @empty
        @endforelse
        @foreach($siapDitempatkan as $m)
          <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--gray-100)">
            <div>
              <div style="font-size:13px;font-weight:600">{{ $m->nama }}</div>
              <div style="font-size:11px;color:var(--gray-500)">{{ $m->nim }} &middot; berkas disetujui, belum ada instansi/dosen</div>
            </div>
            <span class="badge badge-selesai">🏢 Tempatkan</span>
          </div>
        @endforeach
      </div>
    </div>
    @endif

    <!-- Insight #1: Siap seminar tapi belum dijadwalkan -->
    <div class="card" style="margin-bottom:16px">
      <div class="card-header">
        <div>
          <h3>⚠️ Siap Seminar, Belum Dijadwalkan</h3>
          <p>Progress 100% tapi belum ada jadwal seminar</p>
        </div>
        <a href="{{ route('admin.seminar.index') }}" class="btn btn-outline btn-sm">Kelola</a>
      </div>
      <div class="card-body" style="padding:0">
        @forelse($siapBelumJadwal as $m)
        <div style="display:flex;align-items:center;gap:12px;padding:10px 16px;border-bottom:1px solid #f1f5f9">
          <div style="flex:1">
            <div class="fw-bold text-sm">{{ $m->nama }}</div>
            <div class="text-sm text-muted">{{ $m->nim }} · {{ $m->instansi?->nama ?? '–' }}</div>
          </div>
          <a href="{{ route('admin.seminar.index', ['mahasiswa' => $m->id]) }}" class="btn btn-sm" style="background:#fef3c7;color:#92400e">Jadwalkan</a>
        </div>
        @empty
          <div class="empty-state" style="padding:20px"><div class="icon">✅</div><p>Semua mahasiswa siap-seminar sudah punya jadwal</p></div>
        @endforelse
      </div>
    </div>

    <!-- Insight #2: Seminar menunggu persetujuan -->
    <div class="card">
      <div class="card-header">
        <div>
          <h3>🔔 Menunggu Persetujuan</h3>
          <p>Pengajuan seminar yang butuh aksi admin</p>
        </div>
        <a href="{{ route('admin.seminar.index') }}" class="btn btn-outline btn-sm">Kelola</a>
      </div>
      <div class="card-body" style="padding:0">
        @forelse($seminarMenunggu as $s)
        <div style="display:flex;align-items:center;gap:12px;padding:10px 16px;border-bottom:1px solid #f1f5f9">
          <div style="background:#eff6ff;border-radius:8px;padding:6px 10px;text-align:center;min-width:46px;flex-shrink:0">
            <div style="font-size:16px;font-weight:800;color:#2563eb">{{ \Carbon\Carbon::parse($s->tanggal)->format('d') }}</div>
            <div style="font-size:10px;color:#64748b">{{ \Carbon\Carbon::parse($s->tanggal)->format('M') }}</div>
          </div>
          <div style="flex:1">
            <div class="fw-bold text-sm">{{ $s->mahasiswa?->nama }}</div>
            <div class="text-sm text-muted">{{ $s->ruangan }} · {{ substr($s->jam_mulai,0,5) }}-{{ substr($s->jam_selesai,0,5) }}</div>
          </div>
          <a href="{{ route('admin.seminar.index', ['id' => $s->id]) }}" class="btn btn-sm" style="background:#dbeafe;color:#1e40af">Review</a>
        </div>
        @empty
          <div class="empty-state" style="padding:20px"><div class="icon">🗓️</div><p>Tidak ada pengajuan yang menunggu</p></div>
        @endforelse
      </div>
    </div>
  </div>

  <div>
    <!-- Chart distribusi status -->
    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><h3>Distribusi Status Mahasiswa</h3></div>
      <div class="card-body" style="display:flex;justify-content:center;padding:16px">
        <canvas id="statusChart" style="max-height:220px"></canvas>
      </div>
    </div>

    <!-- Insight #3: Mahasiswa perlu perhatian (progress paling lambat) -->
    <div class="card">
      <div class="card-header">
        <div>
          <h3>🐢 Perlu Ditindaklanjuti</h3>
          <p>Progress paling lambat di antara yang masih proses</p>
        </div>
        <a href="{{ route('admin.progress.index') }}" class="btn btn-outline btn-sm">Lihat Semua</a>
      </div>
      <div class="card-body" style="padding:0">
        @forelse($perluPerhatian as $m)
        @php $pct = $m->progressPersen(); @endphp
        <div style="display:flex;align-items:center;gap:12px;padding:10px 16px;border-bottom:1px solid #f1f5f9">
          <div style="flex:1">
            <div class="fw-bold text-sm">{{ $m->nama }}</div>
            <div class="prog-wrap" style="height:6px;margin:4px 0 2px">
              <div class="prog-bar prog-bar-{{ $pct==0?'red':'amber' }}" style="width:{{ max($pct,3) }}%"></div>
            </div>
            <div class="text-sm text-muted">{{ $pct }}% selesai</div>
          </div>
        </div>
        @empty
          <div class="empty-state" style="padding:20px"><div class="icon">🎉</div><p>Tidak ada mahasiswa dalam status proses</p></div>
        @endforelse
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
  const ctx = document.getElementById('statusChart');
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: @json($statusChart['labels']),
      datasets: [{
        data: @json($statusChart['data']),
        backgroundColor: ['#3b82f6', '#f59e0b', '#22c55e'],
        borderWidth: 0,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom', labels: { padding: 16, font: { size: 12 } } }
      }
    }
  });
</script>
@endpush
