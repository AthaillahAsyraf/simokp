@extends('layouts.app')
@section('title','Proposal Rencana Kerja')
@section('content')
<div class="page-header"><h1>Proposal Rencana Kerja</h1><p>Monitoring proposal mahasiswa. Verifikasi dilakukan oleh dosen pembimbing.</p></div>
<div class="card"><div class="table-wrap"><table><thead><tr><th>Mahasiswa</th><th>Instansi</th><th>Dosen Pembimbing</th><th>File</th><th>Status</th><th>Dikirim</th></tr></thead><tbody>
@forelse($proposals as $proposal)<tr><td><strong>{{ $proposal->mahasiswa->nama }}</strong><br><span class="text-muted">{{ $proposal->mahasiswa->nim }}</span></td><td>{{ $proposal->mahasiswa->instansi?->nama ?? '-' }}</td><td>{{ $proposal->mahasiswa->dosen?->nama ?? '-' }}</td><td><a href="{{ $proposal->file_url }}" target="_blank">{{ $proposal->file_asli }}</a></td><td><span class="badge {{ ['menunggu'=>'badge-proses','disetujui'=>'badge-selesai','revisi'=>'badge-rejected'][$proposal->status] }}">{{ ucfirst($proposal->status) }}</span>@if($proposal->catatan)<br><small>{{ $proposal->catatan }}</small>@endif</td><td>{{ $proposal->uploaded_at?->format('d M Y H:i') }}</td></tr>@empty <tr><td colspan="6" style="text-align:center;padding:28px">Belum ada proposal yang dikirim.</td></tr>@endforelse
</tbody></table></div></div>
@endsection
