@extends('layouts.app')
@section('title','Absensi – ' . $mahasiswa->nama)
@section('content')

<div class="page-header page-header-row">
  <div style="display:flex;align-items:center;gap:12px">
    <a href="{{ route('instansi.absensi.index') }}" class="btn btn-outline btn-sm">← Kembali</a>
    <div>
      <h1>📋 Absensi – {{ $mahasiswa->nama }}</h1>
      <p><code>{{ $mahasiswa->nim }}</code> · Dosen: {{ $mahasiswa->dosen?->nama ?? '–' }}</p>
    </div>
  </div>
</div>

{{-- Statistik --}}
<div class="stats-grid stats-3" style="margin-bottom:18px">
  <div class="stat-card c-inst">
    <div class="stat-label">Total Hadir</div>
    <div class="stat-val">{{ $stats['total_hadir'] }}</div>
    <div class="stat-sub">Hari absen masuk</div>
    <div class="stat-icon">📅</div>
  </div>
  <div class="stat-card c-admin">
    <div class="stat-label">Absen Lengkap</div>
    <div class="stat-val">{{ $stats['total_lengkap'] }}</div>
    <div class="stat-sub">Masuk & pulang</div>
    <div class="stat-icon">✅</div>
  </div>
  <div class="stat-card" style="border-left:3px solid #dc2626">
    <div class="stat-label">Luar Radius</div>
    <div class="stat-val" style="color:#dc2626">{{ $stats['diluar_radius'] }}</div>
    <div class="stat-sub">Absen diluar area instansi</div>
    <div class="stat-icon">⚠️</div>
  </div>
</div>

{{-- Filter Bulan --}}
<div class="card" style="margin-bottom:14px">
  <div class="card-body" style="padding:12px 18px">
    <form method="GET" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
      <div>
        <label class="form-label">Filter Bulan</label>
        <input type="month" name="bulan" class="form-control" value="{{ request('bulan') }}">
      </div>
      <button type="submit" class="btn btn-primary btn-sm">Filter</button>
      <a href="{{ route('instansi.absensi.show', $mahasiswa) }}" class="btn btn-outline btn-sm">Semua</a>
    </form>
  </div>
</div>

{{-- Tabel Detail --}}
<div class="card">
  <div class="card-header">
    <h3>Riwayat Absensi</h3>
    <span class="text-sm text-muted">{{ $absensis->total() }} data</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Tanggal</th>
          <th style="text-align:center">Jam Masuk</th>
          <th style="text-align:center">Jarak Masuk</th>
          <th style="text-align:center">Foto Masuk</th>
          <th style="text-align:center">Jam Keluar</th>
          <th style="text-align:center">Jarak Keluar</th>
          <th style="text-align:center">Foto Keluar</th>
          <th style="text-align:center">Status</th>
          <th>Catatan Dosen</th>
        </tr>
      </thead>
      <tbody>
        @forelse($absensis as $a)
        @php $perluTinjau = $a->perluDitinjau(); @endphp
        <tr style="{{ $perluTinjau ? 'background:rgba(239,68,68,.05)' : '' }}">
          <td>
            <div style="font-weight:600;font-size:13px">{{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y') }}</div>
            <div class="text-sm text-muted">{{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('l') }}</div>
          </td>
          <td style="text-align:center">
            @if($a->jam_masuk)
              <span style="font-weight:700">{{ \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') }}</span>
            @else <span class="text-muted">–</span> @endif
          </td>
          <td style="text-align:center">
            @if($a->jarak_masuk !== null)
              <span style="color:{{ $a->status_masuk === 'valid' ? 'var(--green-600)' : '#dc2626' }};font-weight:600">
                {{ $a->jarak_masuk }} m
              </span>
              @if($a->status_masuk === 'diluar_radius')
                <div style="font-size:11px;color:#dc2626">⚠️ Luar Radius</div>
              @endif
            @else <span class="text-muted">–</span> @endif
          </td>
          <td style="text-align:center">
            @if($a->foto_masuk_url)
              <a href="{{ $a->foto_masuk_url }}" target="_blank" class="btn btn-ghost btn-sm" style="font-size:11px">📷 Lihat</a>
            @else <span class="text-muted">–</span> @endif
          </td>
          <td style="text-align:center">
            @if($a->jam_keluar)
              <span style="font-weight:700">{{ \Carbon\Carbon::parse($a->jam_keluar)->format('H:i') }}</span>
            @elseif($a->jam_masuk)
              <span style="font-size:12px;color:#f59e0b;font-weight:600">🕐 Belum</span>
            @else <span class="text-muted">–</span> @endif
          </td>
          <td style="text-align:center">
            @if($a->jarak_keluar !== null)
              <span style="color:{{ $a->status_keluar === 'valid' ? 'var(--green-600)' : '#dc2626' }};font-weight:600">
                {{ $a->jarak_keluar }} m
              </span>
              @if($a->status_keluar === 'diluar_radius')
                <div style="font-size:11px;color:#dc2626">⚠️ Luar Radius</div>
              @endif
            @else <span class="text-muted">–</span> @endif
          </td>
          <td style="text-align:center">
            @if($a->foto_keluar_url)
              <a href="{{ $a->foto_keluar_url }}" target="_blank" class="btn btn-ghost btn-sm" style="font-size:11px">📷 Lihat</a>
            @else <span class="text-muted">–</span> @endif
          </td>
          <td style="text-align:center">
            @if($perluTinjau)
              <span class="badge" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca">⚠️ Tinjau</span>
            @elseif($a->isLengkap())
              <span class="badge badge-selesai">✅ Lengkap</span>
            @elseif($a->jam_masuk)
              <span class="badge badge-proses">Hadir</span>
            @else
              <span class="badge badge-belum">–</span>
            @endif
          </td>
          <td style="font-size:12px;max-width:200px;color:var(--gray-600)">
            {{ $a->catatan_dosen ?? '–' }}
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="9" style="text-align:center;padding:36px;color:var(--gray-400)">
            Belum ada data absensi.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($absensis->hasPages())
  <div style="padding:12px 18px;border-top:1px solid var(--gray-100)">
    {{ $absensis->links() }}
  </div>
  @endif
</div>
@endsection