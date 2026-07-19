@extends('layouts.app')
@section('title','Persyaratan KP')

@push('styles')
<style>
.berkas-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 0;border-bottom:1px solid var(--gray-100)}
.berkas-row:last-child{border-bottom:none}
.berkas-name{font-size:13px;font-weight:600;color:var(--gray-800)}
.berkas-file{font-size:12px;color:var(--gray-500);margin-top:2px}
.catatan-box{background:var(--red-50);border:1px solid var(--red-100);border-radius:8px;padding:10px 12px;font-size:13px;color:var(--red-600);margin-bottom:16px}
</style>
@endpush

@section('content')
<div class="page-header">
  <h1>Persyaratan KP</h1>
  <p>Lengkapi berkas administrasi sebelum melanjutkan ke tahap berikutnya (penempatan instansi & dosen pembimbing).</p>
</div>

@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">⚠️ {{ session('error') }}</div>@endif
@if(session('db_error'))<div class="alert alert-danger">❌ {{ session('db_error') }}</div>@endif
@if($errors->upload->any())
  <div class="alert alert-danger">
    ❌ <strong>Upload gagal:</strong>
    <ul style="margin:4px 0 0 18px">@foreach($errors->upload->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

@include('partials.tahapan-kp', ['mahasiswa' => $mahasiswa])

<div class="card">
  <div class="card-header">
    <div><h3>Berkas Persyaratan</h3><p>Format PDF/JPG/PNG, maksimal 5MB per berkas.</p></div>
    <span class="badge {{ [
        'belum_lengkap'        => 'badge-belum',
        'menunggu_verifikasi'  => 'badge-proses',
        'revisi'               => 'badge-rejected',
        'disetujui'            => 'badge-selesai',
      ][$syarat->status] ?? 'badge-belum' }}">{{ $syarat->status_label }}</span>
  </div>
  <div class="card-body">

    @if($syarat->status === 'revisi' && $syarat->catatan)
      <div class="catatan-box">
        <strong>📌 Catatan revisi dari admin:</strong>
        <div style="margin-top:4px">{{ $syarat->catatan }}</div>
      </div>
    @endif

    @php $bisaEdit = in_array($mahasiswa->tahap, ['lengkapi_berkas','revisi_berkas']); @endphp

    <form method="POST" action="{{ route('mahasiswa.persyaratan.upload') }}" enctype="multipart/form-data">
      @csrf
      @foreach(\App\Models\SyaratAdministrasi::BERKAS as $field => $label)
        <div class="berkas-row">
          <div style="flex:1">
            <div class="berkas-name">{{ $label }}</div>
            @if($syarat->$field)
              <a href="{{ $syarat->urlBerkas($field) }}" target="_blank" class="berkas-file">📄 {{ $syarat->{$field.'_asli'} }}</a>
            @else
              <div class="berkas-file">Belum diupload</div>
            @endif
          </div>
          @if($bisaEdit)
            <div>
              <input type="file" name="{{ $field }}" class="form-control" accept=".pdf,.jpg,.jpeg,.png" style="max-width:220px">
            </div>
          @endif
        </div>
      @endforeach

      @if($bisaEdit)
        <div style="margin-top:18px;text-align:right">
          <button type="submit" class="btn btn-primary">{{ $syarat->exists ? 'Simpan / Kirim Ulang' : 'Kirim Berkas' }}</button>
        </div>
      @else
        <div class="alert alert-info" style="margin-top:16px">
          Berkas sudah terkirim{{ $mahasiswa->tahap === 'aktif_kp' || $mahasiswa->tahap === 'menunggu_instansi' ? ' dan disetujui' : '' }}, tidak bisa diubah lagi di halaman ini.
        </div>
      @endif
    </form>
  </div>
</div>
@endsection