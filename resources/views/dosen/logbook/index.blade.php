@extends('layouts.app')
@section('title','Logbook Mahasiswa')
@section('content')
<div class="page-header"><h1>Logbook Mahasiswa Bimbingan</h1><p>Pantau kegiatan harian mahasiswa di instansi</p></div>
<div class="card">
  <div class="card-header"><div><h3>Semua Logbook</h3><p>{{ $logbooks->total() }} entri</p></div></div>
  <table>
    <thead><tr><th>Tanggal</th><th>Mahasiswa</th><th>Kegiatan</th><th>Jam</th><th>Status Instansi</th></tr></thead>
    <tbody>
      @forelse($logbooks as $l)
      <tr>
        <td style="font-size:11px;color:var(--muted);font-family:'JetBrains Mono',monospace;white-space:nowrap">{{ $l->tanggal }}</td>
        <td><strong>{{ $l->mahasiswa?->nama }}</strong></td>
        <td style="font-size:13px">{{ Str::limit($l->kegiatan, 80) }}</td>
        <td style="font-size:12px;color:var(--muted);white-space:nowrap">
          @if($l->jam_mulai){{ \Carbon\Carbon::parse($l->jam_mulai)->format('H:i') }} – {{ \Carbon\Carbon::parse($l->jam_selesai)->format('H:i') }}@else–@endif
        </td>
        <td><span class="pill pill-{{ $l->status_instansi }}">{{ $l->status_instansi }}</span></td>
      </tr>
      @empty
        <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:24px">Belum ada logbook dari mahasiswa bimbingan.</td></tr>
      @endforelse
    </tbody>
  </table>
  <div style="padding:14px 20px">{{ $logbooks->links() }}</div>
</div>
@endsection