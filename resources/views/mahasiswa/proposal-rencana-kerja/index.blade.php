@extends('layouts.app')
@section('title','Proposal Rencana Kerja')
@section('content')
<div class="page-header"><h1>Proposal Rencana Kerja</h1><p>Unduh form, isi dalam DOCX, lalu kirim untuk diverifikasi dosen pembimbing.</p></div>
@if($errors->proposal->any())<div class="alert alert-danger">@foreach($errors->proposal->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
@php
  $proposal = $mahasiswa->proposalRencanaKerja;
@endphp
<div class="card"><div class="card-body">
  <div class="alert alert-info">Proposal tidak mengunci progres laporan. Setelah mengirim proposal, Anda tetap dapat melanjutkan upload BAB I.</div>
  <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;margin:16px 0"><div><strong>1. Unduh Form Proposal</strong><div class="form-hint">Gunakan template DOCX resmi ini.</div></div><a class="btn btn-outline" href="{{ route('mahasiswa.proposal-rencana-kerja.template') }}">Unduh Form DOCX</a></div><hr>
  <div style="margin:16px 0"><strong>2. Kirim Proposal</strong></div>
  @if($proposal)
    <div class="alert {{ $proposal->status === 'revisi' ? 'alert-danger' : 'alert-info' }}">Status: <span class="badge {{ ['menunggu'=>'badge-proses','disetujui'=>'badge-selesai','revisi'=>'badge-rejected'][$proposal->status] }}">{{ ucfirst($proposal->status) }}</span><br>File: <a href="{{ $proposal->file_url }}" target="_blank">{{ $proposal->file_asli }}</a>@if($proposal->uploaded_at)<br>Dikirim: {{ $proposal->uploaded_at->format('d M Y H:i') }}@endif @if($proposal->catatan)<br><strong>Catatan dosen:</strong> {{ $proposal->catatan }}@endif</div>
  @endif
  @if(!$proposal || $proposal->status === 'revisi')
    <form method="POST" action="{{ route('mahasiswa.proposal-rencana-kerja.upload') }}" enctype="multipart/form-data">@csrf<div class="form-group"><label class="form-label">File Proposal DOCX *</label><input class="form-control" type="file" name="file" accept=".docx" required><div class="form-hint">Maksimal 10MB.</div></div><button class="btn btn-primary" type="submit">{{ $proposal ? 'Kirim Revisi Proposal' : 'Kirim Proposal' }}</button></form>
  @endif
</div></div>
@endsection
