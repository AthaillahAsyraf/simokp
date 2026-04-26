@extends('layouts.app')
@section('title','Verifikasi Logbook')
@section('content')
<div class="page-header"><h1>Verifikasi Logbook Harian</h1><p>Periksa dan setujui kegiatan harian mahasiswa KP</p></div>
<div class="card">
  <div class="card-header">
    <div><h3>Logbook Mahasiswa</h3><p>{{ $logbooks->total() }} entri · {{ $logbooks->where('status_instansi','pending')->count() }} pending</p></div>
  </div>
  <table>
    <thead><tr><th>Tanggal</th><th>Mahasiswa</th><th>Kegiatan</th><th>Jam</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
      @forelse($logbooks as $l)
      <tr>
        <td style="font-size:11px;color:var(--muted);font-family:'JetBrains Mono',monospace;white-space:nowrap">{{ $l->tanggal }}</td>
        <td><strong>{{ $l->mahasiswa?->nama }}</strong></td>
        <td style="font-size:13px">{{ Str::limit($l->kegiatan, 70) }}</td>
        <td style="font-size:12px;color:var(--muted);white-space:nowrap">
          @if($l->jam_mulai){{ \Carbon\Carbon::parse($l->jam_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($l->jam_selesai)->format('H:i') }}@else–@endif
        </td>
        <td><span class="pill pill-{{ $l->status_instansi }}">{{ $l->status_instansi }}</span></td>
        <td>
          @if($l->status_instansi === 'pending')
            <form method="POST" action="{{ route('instansi.logbook.approve',$l) }}" style="display:inline">@csrf
              <button type="submit" class="btn btn-success btn-xs">✅ Setujui</button>
            </form>
            <button class="btn btn-danger btn-xs" style="margin-left:4px" onclick="openTolak({{ $l->id }})">❌ Tolak</button>
          @else
            <span style="font-size:12px;color:var(--muted)">{{ $l->catatan_instansi ? Str::limit($l->catatan_instansi,30) : 'Diproses' }}</span>
          @endif
        </td>
      </tr>
      @empty
        <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:24px">Belum ada logbook.</td></tr>
      @endforelse
    </tbody>
  </table>
  <div style="padding:14px 20px">{{ $logbooks->links() }}</div>
</div>

<div class="modal-bg" id="modalTolak">
  <div class="modal-box">
    <h3>❌ Tolak Logbook</h3>
    <form method="POST" id="tolakForm">@csrf
      <div class="form-group"><label>Alasan Penolakan</label>
        <textarea name="catatan_instansi" class="form-control" rows="3" placeholder="Jelaskan alasan penolakan..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modalTolak')">Batal</button>
        <button type="submit" class="btn btn-danger">Tolak Logbook</button>
      </div>
    </form>
  </div>
</div>
@push('scripts')
<script>
function openTolak(id){
  document.getElementById('tolakForm').action=`/instansi/logbook/${id}/reject`;
  openModal('modalTolak');
}
</script>
@endpush
@endsection