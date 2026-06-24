@extends('layouts.app')
@section('title','Absensi Mahasiswa Bimbingan')
@section('content')

<div class="page-header page-header-row">
  <div>
    <h1>📋 Absensi Mahasiswa Bimbingan</h1>
    <p>Pantau kehadiran mahasiswa bimbingan Anda di tempat KP.</p>
  </div>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:18px">
  <div class="card-body" style="padding:14px 18px">
    <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">
      <div style="flex:1;min-width:180px">
        <label class="form-label">Mahasiswa</label>
        <select name="mahasiswa_id" class="form-control form-select">
          <option value="">Semua Mahasiswa</option>
          @foreach($mahasiswas as $m)
            <option value="{{ $m->id }}" {{ request('mahasiswa_id') == $m->id ? 'selected' : '' }}>
              {{ $m->nama }} ({{ $m->nim }})
            </option>
          @endforeach
        </select>
      </div>
      <div style="min-width:130px">
        <label class="form-label">Dari Tanggal</label>
        <input type="date" name="tanggal_dari" class="form-control" value="{{ request('tanggal_dari') }}">
      </div>
      <div style="min-width:130px">
        <label class="form-label">Sampai Tanggal</label>
        <input type="date" name="tanggal_sampai" class="form-control" value="{{ request('tanggal_sampai') }}">
      </div>
      <div style="min-width:130px">
        <label class="form-label">Status</label>
        <select name="status" class="form-control form-select">
          <option value="">Semua Status</option>
          <option value="valid" {{ request('status') === 'valid' ? 'selected' : '' }}>✅ Valid</option>
          <option value="diluar_radius" {{ request('status') === 'diluar_radius' ? 'selected' : '' }}>⚠️ Luar Radius</option>
          <option value="belum_keluar" {{ request('status') === 'belum_keluar' ? 'selected' : '' }}>🕐 Belum Keluar</option>
        </select>
      </div>
      <div style="display:flex;gap:8px">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('dosen.absensi.index') }}" class="btn btn-outline btn-sm">Reset</a>
      </div>
    </form>
  </div>
</div>

{{-- Tabel --}}
<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Mahasiswa</th>
          <th>Tanggal</th>
          <th style="text-align:center">Absen Masuk</th>
          <th style="text-align:center">Absen Keluar</th>
          <th style="text-align:center">Status</th>
          <th style="text-align:center">Catatan</th>
          <th style="text-align:center">Detail</th>
        </tr>
      </thead>
      <tbody>
        @forelse($absensis as $a)
        @php $perluTinjau = $a->perluDitinjau(); @endphp
        <tr style="{{ $perluTinjau ? 'background:rgba(239,68,68,.04)' : '' }}">
          <td>
            <strong>{{ $a->mahasiswa->nama }}</strong>
            <div class="text-sm text-muted">{{ $a->mahasiswa->nim }}</div>
          </td>
          <td class="text-sm">
            {{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y') }}
            <div class="text-sm text-muted">{{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('l') }}</div>
          </td>
          <td style="text-align:center">
            @if($a->jam_masuk)
              <div style="font-weight:700;font-size:13px">{{ \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') }}</div>
              <div class="text-sm text-muted">{{ $a->jarak_masuk }}m dari instansi</div>
              @if($a->status_masuk === 'diluar_radius')
                <span style="font-size:11px;color:#dc2626;font-weight:600">⚠️ Luar Radius</span>
              @endif
            @else <span class="text-muted">–</span> @endif
          </td>
          <td style="text-align:center">
            @if($a->jam_keluar)
              <div style="font-weight:700;font-size:13px">{{ \Carbon\Carbon::parse($a->jam_keluar)->format('H:i') }}</div>
              <div class="text-sm text-muted">{{ $a->jarak_keluar }}m dari instansi</div>
              @if($a->status_keluar === 'diluar_radius')
                <span style="font-size:11px;color:#dc2626;font-weight:600">⚠️ Luar Radius</span>
              @endif
            @elseif($a->jam_masuk)
              <span style="font-size:12px;color:#f59e0b;font-weight:600">🕐 Belum Pulang</span>
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
          <td style="text-align:center">
            @if($a->catatan_dosen)
              <span title="{{ $a->catatan_dosen }}" style="cursor:help;font-size:16px">📝</span>
            @else <span class="text-muted text-sm">–</span> @endif
          </td>
          <td style="text-align:center">
            <a href="{{ route('dosen.absensi.show', $a->mahasiswa) }}" class="btn btn-ghost btn-sm">Detail</a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" style="text-align:center;padding:36px;color:var(--gray-400)">
            Tidak ada data absensi.
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

  <div style="padding:10px 18px;border-top:1px solid var(--gray-100);display:flex;gap:16px;flex-wrap:wrap;font-size:12px;color:var(--gray-500)">
    <span>✅ Absensi lengkap & valid</span>
    <span>⚠️ Ada absen di luar radius (perlu ditinjau)</span>
    <span>🕐 Sudah masuk, belum absen pulang</span>
  </div>
</div>
@endsection