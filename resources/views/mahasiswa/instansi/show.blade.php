@extends('layouts.app')
@section('title', 'Informasi Instansi')

@section('content')
<div class="page-header page-header-row">
  <div>
    <h1>Informasi Instansi KP</h1>
    <p>Data instansi dan Pembimbing Lapangan yang telah Anda daftarkan.</p>
  </div>
  <a href="{{ route('mahasiswa.dashboard') }}" class="btn btn-outline btn-sm">← Kembali ke Dashboard</a>
</div>

<div class="alert alert-info" style="margin-bottom:16px">
  Data instansi sudah terdaftar dan ditampilkan sebagai informasi saja. Perubahan data dapat diajukan melalui admin jurusan.
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-header"><h3>Profil Instansi</h3></div>
    <div class="card-body" style="font-size:13px;display:grid;gap:12px">
      <div><div class="text-sm text-muted">Nama Instansi</div><strong>{{ $instansi->nama }}</strong></div>
      <div><div class="text-sm text-muted">Bidang</div>{{ $instansi->bidang ?? '–' }}</div>
      <div><div class="text-sm text-muted">Alamat</div>{{ $instansi->alamat ?? '–' }}</div>
      @if($instansi->latitude !== null && $instansi->longitude !== null)
        <div><div class="text-sm text-muted">Lokasi</div><a href="https://www.google.com/maps?q={{ $instansi->latitude }},{{ $instansi->longitude }}" target="_blank" rel="noopener" style="color:var(--blue-600)">Lihat lokasi di Google Maps</a></div>
      @endif
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3>Pembimbing Lapangan</h3></div>
    <div class="card-body" style="font-size:13px;display:grid;gap:12px">
      <div><div class="text-sm text-muted">Nama</div><strong>{{ $mahasiswa->pembimbing_lapangan_nama ?? $instansi->kontak_person ?? '–' }}</strong></div>
      <div><div class="text-sm text-muted">Jabatan / Bidang</div>{{ $mahasiswa->pembimbing_lapangan_jabatan ?? $instansi->bidang ?? '–' }}</div>
      <div><div class="text-sm text-muted">No. HP</div>{{ $mahasiswa->pembimbing_lapangan_no_hp ?? $instansi->no_hp ?? '–' }}</div>
      <div><div class="text-sm text-muted">Email</div>{{ $instansi->user?->email ?? '–' }}</div>
    </div>
  </div>
</div>
@endsection
