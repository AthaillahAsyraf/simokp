@extends('layouts.app')
@section('title','Surat Balasan Instansi')

@push('styles')
<style>
.berkas-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 0;border-bottom:1px solid var(--gray-100)}
.berkas-row:last-child{border-bottom:none}
.berkas-name{font-size:13px;font-weight:600;color:var(--gray-800)}
.berkas-file{font-size:12px;color:var(--gray-500);margin-top:2px}
.info-box{background:var(--blue-50,#eff6ff);border:1px solid var(--blue-100,#dbeafe);border-radius:8px;padding:10px 12px;font-size:13px;color:var(--blue-700,#1d4ed8);margin-bottom:16px}
</style>
@endpush

@section('content')
<div class="page-header">
  <h1>Surat Balasan Instansi</h1>
  <p>Unggah surat balasan dari instansi tempat Anda mengajukan Kerja Praktik.</p>
</div>

@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">⚠️ {{ session('error') }}</div>@endif
@if(session('db_error'))<div class="alert alert-danger">❌ {{ session('db_error') }}</div>@endif
@if($errors->suratBalasan->any())
  <div class="alert alert-danger">
    ❌ <strong>Upload gagal:</strong>
    <ul style="margin:4px 0 0 18px">@foreach($errors->suratBalasan->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

@include('partials.tahapan-kp', ['mahasiswa' => $mahasiswa])

<div class="info-box">
  💡 Surat <strong>permohonan</strong> Kerja Praktik dibuat dan dikirim melalui <strong>SAIDATA</strong> (di luar SIMOKP).
  Setelah instansi membalas, unggah <strong>surat balasannya saja</strong> di halaman ini.
</div>

<div class="card">
  <div class="card-header">
    <div><h3>Berkas Surat Balasan</h3><p>Format PDF/JPG/PNG, maksimal 5MB.</p></div>
    @if($syarat->sudahUploadSuratBalasan())
      <span class="badge badge-selesai">Terkirim</span>
    @else
      <span class="badge badge-belum">Belum Diupload</span>
    @endif
  </div>
  <div class="card-body">

    @php $bisaEdit = $mahasiswa->tahap === \App\Models\Mahasiswa::TAHAP_UNGGAH_SURAT_BALASAN; @endphp

    <form method="POST" action="{{ route('mahasiswa.surat-balasan.upload') }}" enctype="multipart/form-data">
      @csrf
      <div class="berkas-row">
        <div style="flex:1">
          <div class="berkas-name">Surat Balasan Instansi</div>
          @if($syarat->file_surat_balasan)
            <a href="{{ $syarat->urlBerkas('file_surat_balasan') }}" target="_blank" class="berkas-file">📄 {{ $syarat->file_surat_balasan_asli }}</a>
          @else
            <div class="berkas-file">Belum diupload</div>
          @endif
        </div>
        @if($bisaEdit)
          <div>
            <input type="file" name="file_surat_balasan" class="form-control" accept=".pdf,.jpg,.jpeg,.png" style="max-width:220px" required>
          </div>
        @endif
      </div>

      @if($bisaEdit)
        <div style="margin-top:18px;text-align:right">
          <button type="submit" class="btn btn-primary">Kirim Surat Balasan</button>
        </div>
      @else
        <div class="alert alert-info" style="margin-top:16px">
          @if($syarat->sudahUploadSuratBalasan())
            Surat balasan sudah terkirim, tidak bisa diubah lagi di halaman ini.
          @else
            Halaman ini baru bisa diakses setelah berkas persyaratan Anda disetujui admin.
          @endif
        </div>
      @endif
    </form>
  </div>
</div>
@endsection