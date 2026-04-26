@extends('layouts.app')
@section('title','Logbook Harian')
@section('content')
<div class="page-header"><h1>Logbook Harian KP</h1><p>Catat kegiatan harian Anda selama Kerja Praktik</p></div>

<div class="card" style="margin-bottom:20px">
  <div class="card-header"><h3>➕ Tambah Kegiatan</h3></div>
  <div class="card-body">
    <form method="POST" action="{{ route('mahasiswa.logbook.store') }}">
      @csrf
      <div class="form-grid">
        <div class="form-group"><label>Tanggal *</label><input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required></div>
        <div class="form-group"><label>Jam Mulai</label><input type="time" name="jam_mulai" class="form-control" value="08:00"></div>
      </div>
      <div class="form-group"><label>Kegiatan yang Dilakukan *</label>
        <textarea name="kegiatan" class="form-control" rows="3" placeholder="Deskripsikan kegiatan KP Anda hari ini secara jelas..." required></textarea>
      </div>
      <div class="form-grid">
        <div class="form-group"><label>Jam Selesai</label><input type="time" name="jam_selesai" class="form-control" value="16:00"></div>
        <div style="display:flex;align-items:flex-end;padding-bottom:14px">
          <button type="submit" class="btn btn-primary" style="background:var(--mhs);width:100%">➕ Tambah Logbook</button>
        </div>
      </div>
    </form>
    @if($errors->any())<div class="alert alert-danger" style="margin-top:12px">{{ $errors->first() }}</div>@endif
  </div>
</div>

<div class="card">
  <div class="card-header"><h3>Riwayat Logbook</h3><p>{{ $logbooks->total() }} entri</p></div>
  <table>
    <thead><tr><th>Tanggal</th><th>Kegiatan</th><th>Jam</th><th>Status Verifikasi</th><th>Catatan Instansi</th><th>Aksi</th></tr></thead>
    <tbody>
      @forelse($logbooks as $l)
      <tr>
        <td style="font-size:11px;color:var(--muted);font-family:'JetBrains Mono',monospace;white-space:nowrap">{{ $l->tanggal }}</td>
        <td style="font-size:13px">{{ Str::limit($l->kegiatan, 70) }}</td>
        <td style="font-size:12px;color:var(--muted);white-space:nowrap">
          @if($l->jam_mulai){{ \Carbon\Carbon::parse($l->jam_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($l->jam_selesai)->format('H:i') }}@else–@endif
        </td>
        <td><span class="pill pill-{{ $l->status_instansi }}">{{ $l->status_instansi }}</span></td>
        <td style="font-size:12px;color:var(--muted)">{{ $l->catatan_instansi ?? '–' }}</td>
        <td>
          @if($l->status_instansi === 'pending')
            <form method="POST" action="{{ route('mahasiswa.logbook.destroy',$l) }}" onsubmit="return confirm('Hapus logbook ini?')">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
            </form>
          @else <span style="color:var(--muted);font-size:12px">–</span> @endif
        </td>
      </tr>
      @empty
        <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:28px">Belum ada logbook. Mulai catat kegiatan Anda!</td></tr>
      @endforelse
    </tbody>
  </table>
  <div style="padding:14px 20px">{{ $logbooks->links() }}</div>
</div>
@endsection