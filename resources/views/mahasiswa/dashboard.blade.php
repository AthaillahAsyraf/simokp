@extends('layouts.app')
@section('title','Dashboard Mahasiswa')

@section('content')
@php
  $form = $mahasiswa->formKesediaanPembimbing;
  $proposal = $mahasiswa->proposalRencanaKerja;
  $laporan = $mahasiswa->bimbingans->firstWhere('jenis', \App\Models\Bimbingan::JENIS_LAPORAN);
  $accSeminar = $mahasiswa->bimbingans->firstWhere('jenis', \App\Models\Bimbingan::JENIS_ACC_SEMINAR);
  $seminar = $mahasiswa->seminar;
  $aktif = $mahasiswa->sudahAktifKp();
  $statusBadge = ['disetujui' => 'badge-selesai', 'selesai' => 'badge-selesai', 'menunggu' => 'badge-proses', 'diteruskan' => 'badge-proses', 'terjadwal' => 'badge-proses', 'revisi' => 'badge-rejected'];
  $labelStatus = ['diterbitkan' => 'Belum diteruskan', 'diteruskan' => 'Menunggu persetujuan', 'menunggu' => 'Menunggu verifikasi', 'revisi' => 'Perlu revisi', 'disetujui' => 'Disetujui', 'terjadwal' => 'Terjadwal', 'selesai' => 'Selesai', 'menunggu_acc_dospem' => 'Menunggu ACC dosen'];
@endphp

<div class="page-header">
  <h1>Halo, {{ $mahasiswa->nama }}! 👋</h1>
  <p>Pantau tahapan dan dokumen Kerja Praktik Anda di sini.</p>
</div>

@if(!$aktif)
  @include('partials.tahapan-kp', ['mahasiswa' => $mahasiswa])
@endif

<div class="stats-grid stats-2">
  <div class="stat-card c-mhs">
    <div class="stat-label">Tahap KP Saat Ini</div>
    <div class="stat-val" style="font-size:18px;padding-top:8px">{{ $mahasiswa->tahapLabel() }}</div>
    <div class="stat-sub">Ikuti tindakan pada checklist di bawah.</div>
    <div class="stat-icon">📋</div>
  </div>
  <div class="stat-card c-inst">
    <div class="stat-label">Status KP</div>
    <div class="stat-val" style="font-size:20px;padding-top:6px">{{ ['proses'=>'Proses ⚙️','seminar'=>'Seminar 🎤','selesai'=>'Selesai ✅'][$mahasiswa->status] ?? ucfirst($mahasiswa->status) }}</div>
    <div class="stat-sub">{{ $mahasiswa->tanggal_mulai ? 'Dimulai '.$mahasiswa->tanggal_mulai : 'Belum aktif KP' }}</div>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-header"><div><h3>Checklist Penyelesaian KP</h3><p>Ikuti status dokumen dan tahapan berikut.</p></div></div>
    <div class="card-body">
      @php
        $items = [
          ['icon'=>'📋','judul'=>'Kesediaan Pembimbing','status'=>$form?->status, 'kosong'=>'Belum diterbitkan admin', 'url'=>$form ? route('mahasiswa.form-kesediaan-pembimbing.index') : null, 'aksi'=>'Lihat Form'],
          ['icon'=>'📄','judul'=>'Proposal Rencana Kerja','status'=>$proposal?->status, 'kosong'=>$aktif ? 'Belum dikirim' : 'Menunggu aktivasi KP', 'url'=>$aktif ? route('mahasiswa.proposal-rencana-kerja.index') : null, 'aksi'=>'Kelola Proposal'],
          ['icon'=>'📝','judul'=>'Laporan KP','status'=>$laporan?->status, 'kosong'=>$aktif ? 'Belum dikirim' : 'Menunggu aktivasi KP', 'url'=>$aktif ? route('mahasiswa.progress.index') : null, 'aksi'=>'Kelola Laporan'],
          ['icon'=>'🎤','judul'=>'Seminar KP','status'=>$seminar?->status ?? $accSeminar?->status, 'kosong'=>'Belum diajukan', 'url'=>$aktif ? route('mahasiswa.seminar.index') : null, 'aksi'=>'Lihat Seminar'],
        ];
      @endphp
      @foreach($items as $item)
        @php
          $status = $item['status'];
          $selesai = in_array($status, ['disetujui', 'selesai']);
          $label = $status ? ($labelStatus[$status] ?? ucfirst(str_replace('_', ' ', $status))) : $item['kosong'];
        @endphp
        <div style="display:flex;align-items:center;gap:12px;padding:13px 0;border-bottom:1px solid var(--gray-100)">
          <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:{{ $selesai ? 'var(--green-100)' : ($status ? 'var(--amber-100)' : 'var(--gray-100)') }};font-size:15px">{{ $selesai ? '✓' : $item['icon'] }}</div>
          <div style="flex:1"><div style="font-size:13px;font-weight:700">{{ $item['judul'] }}</div><div style="font-size:11px;color:var(--gray-500);margin-top:2px">{{ $label }}</div></div>
          @if($status)<span class="badge {{ $statusBadge[$status] ?? 'badge-belum' }}">{{ $label }}</span>@endif
          @if($item['url'])<a href="{{ $item['url'] }}" class="btn btn-outline btn-xs">{{ $item['aksi'] }}</a>@endif
        </div>
      @endforeach
    </div>
  </div>

  <div>
    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><h3>Info KP Saya</h3></div>
      <div class="card-body" style="font-size:13px;display:grid;gap:10px">
        <div><div style="font-size:11px;color:#64748b">NIM</div><code>{{ $mahasiswa->nim }}</code></div>
        <div><div style="font-size:11px;color:#64748b">Instansi</div><strong>{{ $mahasiswa->instansi?->nama ?? '–' }}</strong></div>
        <div><div style="font-size:11px;color:#64748b">Dosen Pembimbing</div><strong>{{ $mahasiswa->dosen?->nama ?? 'Belum ditentukan' }}</strong></div>
        <div><div style="font-size:11px;color:#64748b">Periode KP</div>{{ $mahasiswa->tanggal_mulai ?? '–' }} @if($mahasiswa->tanggal_selesai) s/d {{ $mahasiswa->tanggal_selesai }} @endif</div>
      </div>
    </div>

    @if($seminar)
    <div class="card"><div class="card-header"><h3>🎤 Info Seminar</h3><span class="badge {{ $statusBadge[$seminar->status] ?? 'badge-belum' }}">{{ $labelStatus[$seminar->status] ?? $seminar->status }}</span></div>
      <div class="card-body" style="font-size:13px;display:grid;gap:8px">
        <div><div style="font-size:11px;color:#64748b">Tanggal & Jam</div><strong>{{ $seminar->tanggal }} · {{ \Carbon\Carbon::parse($seminar->jam_mulai)->format('H:i') }} WIB</strong></div>
        <div><div style="font-size:11px;color:#64748b">Ruangan</div>{{ $seminar->ruangan ?? '–' }}</div>
      </div>
    </div>
    @endif
  </div>
</div>
@endsection
