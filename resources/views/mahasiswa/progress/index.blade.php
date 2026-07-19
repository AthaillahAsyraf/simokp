@extends('layouts.app')
@section('title','Laporan KP')

@push('styles')
<style>
.bab-card.locked{opacity:.5;cursor:not-allowed;background:var(--gray-50)}
.bab-card.locked:hover{border-color:var(--gray-200);box-shadow:none}
.bab-card.menunggu{background:var(--amber-50);border-color:var(--amber-500)}
.bab-card.menunggu:hover{border-color:var(--amber-600)}
.bab-card.revisi{background:var(--red-50);border-color:var(--red-500)}
.bab-card.revisi:hover{border-color:var(--red-600)}
.file-link{display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--blue-600);font-weight:600;padding:8px 12px;border:1px solid var(--blue-100);border-radius:8px;background:var(--blue-50);margin-bottom:14px}
.file-link:hover{background:var(--blue-100)}
.catatan-box{background:var(--red-50);border:1px solid var(--red-100);border-radius:8px;padding:10px 12px;font-size:13px;color:var(--red-600);margin-bottom:14px}
</style>
@endpush

@section('content')
@php $pct = $mahasiswa->progressPersen(); @endphp

<div class="page-header"><h1>Laporan KP</h1><p>Upload soft file laporan per BAB untuk diverifikasi dosen pembimbing</p></div>

@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">⚠️ {{ session('error') }}</div>@endif
@if(session('db_error'))<div class="alert alert-danger">❌ {{ session('db_error') }}</div>@endif
@if($errors->upload->any())
  <div class="alert alert-danger">
    ❌ <strong>Upload gagal:</strong>
    <ul style="margin:4px 0 0 18px">@foreach($errors->upload->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<div class="card">
  <div class="card-header">
    <div><h3>Progress per BAB</h3><p>{{ $pct }}% selesai</p></div>
    <div class="prog-wrap" style="width:150px;height:8px"><div class="prog-bar prog-bar-purple" style="width:{{ $pct }}%"></div></div>
  </div>
  <div style="padding:20px">
    <div class="bab-grid">
      @foreach($mahasiswa->progressBabs->sortBy('id') as $p)
        @php
          $locked = !$p->bisaDiupload();
          $state  = $locked ? 'locked' : ($p->sudahApproved() ? 'done' : ($p->verifikasi_status === 'revisi' ? 'revisi' : ($p->verifikasi_status === 'menunggu' ? 'menunggu' : '')));
          $icon   = ['done'=>'✅','menunggu'=>'🕓','revisi'=>'🔁','locked'=>'🔒'][$state] ?? '⬜';
          $label  = ['done'=>'Disetujui','menunggu'=>'Menunggu Verifikasi','revisi'=>'Perlu Revisi','locked'=>'Terkunci'][$state] ?? 'Belum Diupload';
        @endphp
        <div class="bab-card {{ $state }}" data-bab-id="{{ $p->id }}"
             @if(!$locked)
             onclick="openUpload({{ $p->id }}, '{{ $p->bab }}', '{{ $state }}', {{ json_encode($p->file_url) }}, {{ json_encode($p->file_asli) }}, {{ json_encode($p->catatan) }}, {{ json_encode($p->file_dosen_url) }}, {{ json_encode($p->file_dosen_asli) }})"
             @endif>
          <div class="bab-icon">{{ $icon }}</div>
          <div class="bab-num">{{ $p->bab }}</div>
          <div class="bab-stat">{{ $label }}</div>
          @if($p->file_uploaded_at)<div class="bab-date">{{ $p->file_uploaded_at->format('d M Y') }}</div>@endif
          @if($p->file_dosen)<div class="bab-date" style="color:var(--green-600);font-weight:600">📎 Ada file dari dosen</div>@endif
        </div>
      @endforeach
    </div>
    <div class="alert alert-info" style="margin-top:20px">💡 Upload BAB secara berurutan — BAB berikutnya terbuka setelah BAB sebelumnya disetujui dosen pembimbing.</div>
  </div>
</div>

{{-- MODAL UPLOAD --}}
<div class="modal-bg" id="modalUpload">
  <div class="modal-box">
    <div class="modal-title" id="upTitle"></div>

    <div id="upCatatanBox" class="catatan-box" style="display:none">
      <strong>📌 Catatan revisi dari dosen:</strong>
      <div id="upCatatanText" style="margin-top:4px"></div>
    </div>

    <div id="upFileExisting" style="display:none">
      <a href="#" id="upFileLink" target="_blank" class="file-link">📄 <span id="upFileName"></span></a>
    </div>

    <div id="upFileDosenExisting" style="display:none">
      <a href="#" id="upFileDosenLink" target="_blank" class="file-link" style="background:var(--green-50);border-color:var(--green-100);color:var(--green-600)">📎 <span id="upFileDosenName"></span></a>
    </div>

    <form method="POST" id="upForm" enctype="multipart/form-data">
      @csrf
      <div class="form-group" id="upFormGroup">
        <label class="form-label" id="upFormLabel">Upload File Laporan *</label>
        <input type="file" name="file" id="upFileInput" class="form-control" accept=".pdf,.doc,.docx">
        <p class="form-hint">Format PDF/DOC/DOCX, maksimal 10MB.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalUpload')">Tutup</button>
        <button type="submit" class="btn btn-primary" id="upSubmitBtn">Upload</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function openUpload(id, bab, state, fileUrl, fileName, catatan, fileDosenUrl, fileDosenName) {
  document.getElementById('upForm').action = `{{ url('mahasiswa/progress') }}/${id}/upload`;
  document.getElementById('upTitle').textContent = '📝 ' + bab;

  const catBox = document.getElementById('upCatatanBox');
  if (state === 'revisi' && catatan) {
    document.getElementById('upCatatanText').textContent = catatan;
    catBox.style.display = 'block';
  } else {
    catBox.style.display = 'none';
  }

  const existingBox = document.getElementById('upFileExisting');
  if (fileUrl) {
    document.getElementById('upFileLink').href = fileUrl;
    document.getElementById('upFileName').textContent = fileName || 'Lihat file terupload';
    existingBox.style.display = 'block';
  } else {
    existingBox.style.display = 'none';
  }

  const fileDosenBox = document.getElementById('upFileDosenExisting');
  if (fileDosenUrl) {
    document.getElementById('upFileDosenLink').href = fileDosenUrl;
    document.getElementById('upFileDosenName').textContent = 'File dari dosen: ' + (fileDosenName || 'lihat file');
    fileDosenBox.style.display = 'block';
  } else {
    fileDosenBox.style.display = 'none';
  }

  const formGroup = document.getElementById('upFormGroup');
  const submitBtn = document.getElementById('upSubmitBtn');
  const formLabel = document.getElementById('upFormLabel');

  if (state === 'done') {
    // sudah disetujui — read only, tidak bisa upload ulang lewat sini
    formGroup.style.display = 'none';
    submitBtn.style.display = 'none';
  } else {
    formGroup.style.display = '';
    submitBtn.style.display = '';
    formLabel.textContent = fileUrl ? 'Upload Ulang (Ganti File) *' : 'Upload File Laporan *';
    submitBtn.textContent = fileUrl ? 'Upload Ulang' : 'Upload';
  }

  openModal('modalUpload');
}

@if($errors->upload->any() && session('upload_bab_id'))
window.addEventListener('load', function () {
  const targetId = {{ session('upload_bab_id') }};
  const card = document.querySelector(`.bab-card[data-bab-id="${targetId}"]`);
  if (card) card.click();
});
@endif
</script>
@endpush
@endsection