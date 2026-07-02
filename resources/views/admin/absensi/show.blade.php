@extends('layouts.app')
@section('title','Absensi – ' . $mahasiswa->nama)
@section('content')

<div class="page-header page-header-row">
  <div style="display:flex;align-items:center;gap:12px">
    <a href="{{ route('admin.absensi.index') }}" class="btn btn-outline btn-sm">← Kembali</a>
    <div>
      <h1>📋 Absensi – {{ $mahasiswa->nama }}</h1>
      <p><code>{{ $mahasiswa->nim }}</code> · {{ $mahasiswa->instansi?->nama ?? '–' }}</p>
    </div>
  </div>
</div>

{{-- Statistik Ringkas --}}
<div class="stats-grid stats-3" style="margin-bottom:18px">
  <div class="stat-card c-admin">
    <div class="stat-label">Total Hadir</div>
    <div class="stat-val">{{ $stats['total_hadir'] }}</div>
    <div class="stat-sub">Hari masuk absen</div>
    <div class="stat-icon">📅</div>
  </div>
  <div class="stat-card c-dosen">
    <div class="stat-label">Absen Lengkap</div>
    <div class="stat-val">{{ $stats['total_lengkap'] }}</div>
    <div class="stat-sub">Masuk & pulang tercatat</div>
    <div class="stat-icon">✅</div>
  </div>
  <div class="stat-card" style="border-left:3px solid #dc2626">
    <div class="stat-label">Luar Radius</div>
    <div class="stat-val" style="color:#dc2626">{{ $stats['diluar_radius'] }}</div>
    <div class="stat-sub">Perlu ditinjau</div>
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
      <a href="{{ route('admin.absensi.show', $mahasiswa) }}" class="btn btn-outline btn-sm">Semua</a>
    </form>
  </div>
</div>

{{-- Tabel Riwayat Absensi --}}
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
          <th style="text-align:center">Jam Keluar</th>
          <th style="text-align:center">Jarak Keluar</th>
          <th style="text-align:center">Status</th>
          <th>Catatan Admin</th>
          <th style="text-align:center">Aksi</th>
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
          {{-- Absen Masuk --}}
          <td style="text-align:center">
            @if($a->jam_masuk)
              <span style="font-weight:700">{{ \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') }}</span>
              @if($a->foto_masuk_url)
                <a href="{{ $a->foto_masuk_url }}" target="_blank" style="display:block;font-size:11px;color:var(--blue-600)">📷 Foto</a>
              @endif
            @else <span class="text-muted">–</span> @endif
          </td>
          <td style="text-align:center">
            @if($a->jarak_masuk !== null)
              <span style="font-size:13px;color:{{ $a->status_masuk === 'valid' ? 'var(--green-600)' : '#dc2626' }};font-weight:600">
                {{ $a->jarak_masuk }} m
              </span>
              <div class="text-sm text-muted">akurasi {{ $a->akurasi_gps_masuk }}m</div>
            @else <span class="text-muted">–</span> @endif
          </td>
          {{-- Absen Keluar --}}
          <td style="text-align:center">
            @if($a->jam_keluar)
              <span style="font-weight:700">{{ \Carbon\Carbon::parse($a->jam_keluar)->format('H:i') }}</span>
              @if($a->foto_keluar_url)
                <a href="{{ $a->foto_keluar_url }}" target="_blank" style="display:block;font-size:11px;color:var(--blue-600)">📷 Foto</a>
              @endif
            @elseif($a->jam_masuk)
              <span style="color:#f59e0b;font-size:12px;font-weight:600">🕐 Belum</span>
            @else <span class="text-muted">–</span> @endif
          </td>
          <td style="text-align:center">
            @if($a->jarak_keluar !== null)
              <span style="font-size:13px;color:{{ $a->status_keluar === 'valid' ? 'var(--green-600)' : '#dc2626' }};font-weight:600">
                {{ $a->jarak_keluar }} m
              </span>
              <div class="text-sm text-muted">akurasi {{ $a->akurasi_gps_keluar }}m</div>
            @else <span class="text-muted">–</span> @endif
          </td>
          {{-- Status --}}
          <td style="text-align:center">
            @if($perluTinjau)
              <span class="badge" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca" title="{{ implode(' • ', $a->alasanPerluTinjau()) }}">⚠️ Tinjau</span>
            @elseif($a->isLengkap())
              <span class="badge badge-selesai">✅ Lengkap</span>
            @elseif($a->jam_masuk)
              <span class="badge badge-proses">Hadir</span>
            @else
              <span class="badge badge-belum">–</span>
            @endif
          </td>
          {{-- Catatan --}}
          <td style="font-size:12px;max-width:180px">
            <form action="{{ route('admin.absensi.catatan', $a) }}" method="POST" style="display:flex;gap:6px;align-items:center">
              @csrf @method('PATCH')
              <input type="text" name="catatan_dosen" class="form-control" style="font-size:12px;padding:4px 8px"
                     placeholder="Tulis catatan…" value="{{ $a->catatan_dosen ?? '' }}" maxlength="500">
              <button type="submit" class="btn btn-ghost btn-sm" title="Simpan catatan">💾</button>
            </form>
          </td>
          <td style="text-align:center">
            <span class="text-sm text-muted">IP: {{ $a->ip_masuk ?? '–' }}</span>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" style="text-align:center;padding:36px;color:var(--gray-400)">
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