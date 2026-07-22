@extends('layouts.app')
@section('title','Laporan KP')
@section('content')
<div class="page-header"><h1>Laporan KP</h1><p>Upload file dan tuliskan keterangan singkat untuk dosen pembimbing.</p></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">{{ session('error') }}</div>@endif
<div class="card"><div class="card-header"><h3>Kirim Bimbingan</h3></div><div class="card-body"><form method="POST" action="{{ route('mahasiswa.progress.upload') }}" enctype="multipart/form-data">@csrf
<div class="form-group"><label class="form-label">File laporan</label><input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx" required></div>
<div class="form-group"><label class="form-label">Keterangan</label><textarea name="keterangan" class="form-control" rows="3" required placeholder="Contoh: Laporan final sudah direvisi sesuai arahan."></textarea></div><button class="btn btn-primary">Kirim</button></form></div></div>
<div class="card" style="margin-top:20px"><div class="card-header"><h3>Riwayat Bimbingan</h3></div><div class="table-wrap"><table><thead><tr><th>File</th><th>Keterangan</th><th>Status</th><th>Catatan Dosen</th></tr></thead><tbody>
@forelse($mahasiswa->bimbingans->where('jenis','laporan') as $b)<tr><td><a href="{{ $b->file_url }}" target="_blank">{{ $b->file_asli }}</a></td><td>{{ $b->keterangan }}</td><td>{{ ucfirst($b->status) }}</td><td>{{ $b->catatan_dosen ?? '-' }}</td></tr>@empty<tr><td colspan="4" style="text-align:center;padding:24px">Belum ada bimbingan.</td></tr>@endforelse
</tbody></table></div></div>
@endsection
