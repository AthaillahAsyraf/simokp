@extends('layouts.app')
@section('title', 'Dashboard Admin')

@push('styles')
<style>
.command-banner{display:flex;justify-content:space-between;gap:20px;align-items:center;padding:22px 24px;border:1px solid var(--blue-100);border-radius:14px;background:linear-gradient(115deg,var(--blue-50),#fff 68%);margin-bottom:20px}
.command-banner h2{font-size:18px;margin:0 0 5px;color:var(--gray-900)}.command-banner p{margin:0;color:var(--gray-600);font-size:13px}.command-total{text-align:right;white-space:nowrap}.command-total strong{display:block;font-size:28px;line-height:1;color:var(--blue-700)}.command-total span{font-size:11px;color:var(--gray-500)}
.dashboard-grid{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(300px,1fr);gap:18px;align-items:start}.dashboard-stack{display:grid;gap:18px}.section-title{font-size:15px;font-weight:700;color:var(--gray-800);margin:0}.section-subtitle{font-size:12px;color:var(--gray-500);margin:3px 0 0}
.action-summary{display:grid;grid-template-columns:repeat(3,1fr);border-top:1px solid var(--gray-100);margin-top:14px}.action-summary a{display:block;padding:14px 12px;border-right:1px solid var(--gray-100);text-decoration:none;color:inherit}.action-summary a:last-child{border-right:0}.action-summary a:hover{background:var(--gray-50)}.action-summary strong{display:block;font-size:21px;line-height:1;color:var(--gray-900);margin-bottom:5px}.action-summary span{font-size:11px;color:var(--gray-600);line-height:1.35;display:block}
.queue-list{padding:0}.queue-row{display:flex;gap:12px;align-items:center;padding:12px 16px;border-bottom:1px solid var(--gray-100)}.queue-row:last-child{border-bottom:0}.queue-mark{width:8px;height:34px;border-radius:8px;flex-shrink:0}.queue-mark.amber{background:#f59e0b}.queue-mark.blue{background:#3b82f6}.queue-mark.purple{background:#8b5cf6}.queue-copy{min-width:0;flex:1}.queue-copy strong{font-size:13px;color:var(--gray-800);display:block}.queue-copy span{font-size:11px;color:var(--gray-500);display:block;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.queue-row .btn{flex-shrink:0}
.stage-list{padding:4px 16px 12px}.stage-row{display:grid;grid-template-columns:136px 1fr 32px;gap:10px;align-items:center;padding:8px 0}.stage-label{font-size:12px;color:var(--gray-600)}.stage-track{height:7px;background:var(--gray-100);border-radius:10px;overflow:hidden}.stage-fill{height:100%;border-radius:inherit}.stage-fill.slate{background:#94a3b8}.stage-fill.amber{background:#f59e0b}.stage-fill.blue{background:#3b82f6}.stage-fill.purple{background:#8b5cf6}.stage-fill.green{background:#22c55e}.stage-count{text-align:right;font-size:12px;font-weight:700;color:var(--gray-700)}
.monitoring-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:16px}.monitoring-item{border:1px solid var(--gray-100);border-radius:10px;padding:12px}.monitoring-item strong{font-size:20px;display:block;color:var(--gray-800)}.monitoring-item span{font-size:11px;color:var(--gray-500);line-height:1.35;display:block;margin-top:4px}
.schedule-row{display:grid;grid-template-columns:48px 1fr auto;gap:10px;align-items:center;padding:11px 16px;border-bottom:1px solid var(--gray-100)}.schedule-row:last-child{border-bottom:0}.schedule-date{background:var(--blue-50);border-radius:8px;text-align:center;padding:5px 2px;color:var(--blue-700)}.schedule-date strong{display:block;font-size:16px;line-height:1}.schedule-date span{font-size:10px;text-transform:uppercase}.schedule-copy strong{font-size:12px;display:block}.schedule-copy span{font-size:11px;color:var(--gray-500)}.schedule-room{font-size:11px;color:var(--gray-500);text-align:right}
.progress-row{padding:11px 16px;border-bottom:1px solid var(--gray-100)}.progress-row:last-child{border-bottom:0}.progress-copy{display:flex;justify-content:space-between;gap:10px;font-size:12px}.progress-copy strong{color:var(--gray-800)}.progress-copy span{color:var(--gray-500)}
@media(max-width:900px){.dashboard-grid{grid-template-columns:1fr}.command-banner{align-items:flex-start}.action-summary{grid-template-columns:1fr}.action-summary a{border-right:0;border-bottom:1px solid var(--gray-100)}.action-summary a:last-child{border-bottom:0}}@media(max-width:560px){.command-banner{display:block}.command-total{text-align:left;margin-top:16px}.stage-row{grid-template-columns:112px 1fr 26px}.monitoring-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="page-header">
  <h1>Pusat Kendali KP</h1>
  <p>Prioritas operasional dan pemantauan progres Kerja Praktik.</p>
</div>

<section class="command-banner">
  <div>
    <h2>Fokus kerja admin hari ini</h2>
    <p>Mulai dari antrian yang membutuhkan keputusan admin agar proses mahasiswa tidak tertahan.</p>
  </div>
  <div class="command-total"><strong>{{ $jumlahTindakanAdmin }}</strong><span>tindakan admin menunggu</span></div>
</section>

<div class="stats-grid" style="grid-template-columns:repeat(3,1fr)">
  <div class="stat-card c-blue"><div class="stat-label">Mahasiswa Aktif</div><div class="stat-val">{{ $stats['proses'] }}</div><div class="stat-sub">dari {{ $stats['total'] }} mahasiswa</div><div class="stat-icon">👥</div></div>
  <div class="stat-card c-amber"><div class="stat-label">Menjelang Seminar</div><div class="stat-val">{{ $stats['seminar'] }}</div><div class="stat-sub">perlu penjadwalan atau pelaksanaan</div><div class="stat-icon">🎓</div></div>
  <div class="stat-card c-green"><div class="stat-label">KP Selesai</div><div class="stat-val">{{ $stats['selesai'] }}</div><div class="stat-sub">mahasiswa telah menuntaskan KP</div><div class="stat-icon">✓</div></div>
</div>

<div class="dashboard-grid">
  <div class="dashboard-stack">
    <section class="card">
      <div class="card-header"><div><h3 class="section-title">Antrian tindakan admin</h3><p class="section-subtitle">Item ini memerlukan verifikasi, keputusan, atau penjadwalan dari admin.</p></div></div>
      <div class="action-summary">
        <a href="{{ route('admin.mahasiswa.index', ['tahap' => \App\Models\Mahasiswa::TAHAP_MENUNGGU_VERIFIKASI]) }}"><strong>{{ $jumlahMenungguBerkas }}</strong><span>Berkas menunggu verifikasi</span></a>
        <a href="{{ route('admin.seminar.index') }}"><strong>{{ $jumlahSiapBelumJadwal }}</strong><span>Siap seminar, belum dijadwalkan</span></a>
        <a href="{{ route('admin.seminar.index', ['status' => \App\Models\Seminar::STATUS_MENUNGGU]) }}"><strong>{{ $jumlahSeminarMenunggu }}</strong><span>Pengajuan seminar perlu ditinjau</span></a>
      </div>
      <div class="queue-list">
        @forelse($antrianAdmin as $item)
          <div class="queue-row"><span class="queue-mark {{ $item['tone'] }}"></span><div class="queue-copy"><strong>{{ $item['nama'] }}</strong><span>{{ $item['detail'] }}</span></div><a href="{{ $item['url'] }}" class="btn btn-outline btn-sm">{{ $item['label'] }}</a></div>
        @empty
          <div class="empty-state" style="padding:28px"><div class="icon">✓</div><p>Tidak ada tindakan admin yang tertunda.</p></div>
        @endforelse
      </div>
    </section>

    <section class="card">
      <div class="card-header"><div><h3 class="section-title">Mahasiswa yang perlu dipantau</h3><p class="section-subtitle">Progress terendah dari mahasiswa yang sedang menjalani KP.</p></div><a href="{{ route('admin.progress.index') }}" class="btn btn-outline btn-sm">Lihat progres</a></div>
      @forelse($perluPerhatian as $m)
        @php($persen = $m->progressPersen())
        <div class="progress-row"><div class="progress-copy"><strong>{{ $m->nama }} <span>· {{ $m->nim }}</span></strong><span>{{ $persen }}%</span></div><div class="prog-wrap" style="height:6px;margin-top:7px"><div class="prog-bar prog-bar-{{ $persen === 0 ? 'red' : 'amber' }}" style="width:{{ max($persen, 3) }}%"></div></div></div>
      @empty
        <div class="empty-state" style="padding:28px"><div class="icon">✓</div><p>Belum ada mahasiswa berstatus proses untuk dipantau.</p></div>
      @endforelse
    </section>
  </div>

  <div class="dashboard-stack">
    <section class="card">
      <div class="card-header"><div><h3 class="section-title">Alur administrasi mahasiswa</h3><p class="section-subtitle">Posisi mahasiswa pada setiap tahap pra-KP.</p></div><a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline btn-sm">Data mahasiswa</a></div>
      @php($maksTahap = max(1, $tahapDistribusi->max('jumlah')))
      <div class="stage-list">
        @foreach($tahapDistribusi as $tahap)
          <div class="stage-row"><span class="stage-label">{{ $tahap['label'] }}</span><div class="stage-track"><div class="stage-fill {{ $tahap['warna'] }}" style="width:{{ ($tahap['jumlah'] / $maksTahap) * 100 }}%"></div></div><span class="stage-count">{{ $tahap['jumlah'] }}</span></div>
        @endforeach
      </div>
    </section>

    <section class="card">
      <div class="card-header"><div><h3 class="section-title">Menunggu aksi pihak lain</h3><p class="section-subtitle">Dipantau admin, tetapi tidak memerlukan keputusan admin saat ini.</p></div></div>
      <div class="monitoring-grid"><div class="monitoring-item"><strong>{{ $menungguAksiMahasiswa }}</strong><span>mahasiswa masih perlu mengunggah surat balasan atau mendaftarkan instansi</span></div><div class="monitoring-item"><strong>{{ $menungguAksiDosen }}</strong><span>menunggu persetujuan kesediaan dari dosen pembimbing</span></div></div>
    </section>
  </div>
</div>
@endsection
