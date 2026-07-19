@extends('layouts.app')
@section('title','Verifikasi Laporan')

@push('styles')
<style>
.mhs-block{border:1px solid var(--gray-200);border-radius:var(--radius);padding:18px;margin-bottom:16px;background:var(--white)}
.mhs-block-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;flex-wrap:wrap;gap:8px}
.mhs-block-head h4{font-size:14px;font-weight:700;color:var(--gray-900)}
.mhs-block-head p{font-size:12px;color:var(--gray-500);margin-top:2px}
.bab-card.locked{opacity:.5;cursor:not-allowed;background:var(--gray-50)}
.bab-card.locked:hover{border-color:var(--gray-200);box-shadow:none}
.bab-card.menunggu{background:var(--amber-50);border-color:var(--amber-500)}
.bab-card.menunggu:hover{border-color:var(--amber-600)}
.bab-card.revisi{background:var(--red-50);border-color:var(--red-500)}
.bab-card.revisi:hover{border-color:var(--red-600)}
.file-link{display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--blue-600);font-weight:600;padding:8px 12px;border:1px solid var(--blue-100);border-radius:8px;background:var(--blue-50);margin-bottom:14px}
.file-link:hover{background:var(--blue-100)}
.catatan-box{background:var(--red-50);border:1px solid var(--red-100);border-radius:8px;padding:10px 12px;font-size:13px;color:var(--red-600);margin-bottom:14px}
.empty-state{text-align:center;padding:50px 20px;color:var(--gray-400)}
</style>
@endpush

@section('content')
<div class="page-header"><h1>Verifikasi Laporan</h1><p>Periksa & verifikasi soft file laporan per BAB yang diupload mahasiswa bimbingan</p></div>

@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">⚠️ {{ session('error') }}</div>@endif

@forelse($mahasiswas as $m)
  @php
    $pct = $m->progressPersen();
    $adaMenunggu = $m->progressBabs->contains('verifikasi_status','menunggu');
  @endphp
  <div class="mhs-block">
    <div class="mhs-block-head">
      <div>
        <h4>{{ $m->nama }} <span style="color:var(--gray-400);font-weight:500">— {{ $m->nim }}</span>
          @if($adaMenunggu)<span class="badge badge-proses" style="margin-left:6px">🕓 Ada yang perlu diverifikasi</span>@endif
        </h4>
        <p>{{ $m->instansi->nama ?? 'Belum ditempatkan di instansi' }}</p>
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        <div class="prog-wrap" style="width:120px;height:7px"><div class="prog-bar prog-bar-green" style="width:{{ $pct }}%"></div></div>
        <span style="font-size:12px;font-weight:700;color:var(--green-600)">{{ $pct }}%</span>
      </div>
    </div>

    <div class="bab-grid">
      @foreach($m->progressBabs->sortBy('id') as $p)
        @php
          $locked = !$p->bisaDiupload();
          $state  = $locked ? 'locked' : ($p->sudahApproved() ? 'done' : ($p->verifikasi_status === 'revisi' ? 'revisi' : ($p->verifikasi_status === 'menunggu' ? 'menunggu' : '')));
          $icon   = ['done'=>'✅','menunggu'=>'🕓','revisi'=>'🔁','locked'=>'🔒'][$state] ?? '⬜';
          $label  = ['done'=>'Disetujui','menunggu'=>'Perlu Verifikasi','revisi'=>'Revisi Diminta','locked'=>'Belum Diupload'][$state] ?? 'Belum Diupload';
          $clickable = $p->file ? true : false;
        @endphp
        <div class="bab-card {{ $state }}"
             @if($clickable)
             onclick="openVerifikasi({{ $p->id }}, '{{ $p->bab }}', '{{ $state }}', {{ json_encode($p->file_url) }}, {{ json_encode($p->file_asli) }}, {{ json_encode($p->catatan) }}, {{ json_encode($p->file_dosen_url) }}, {{ json_encode($p->file_dosen_asli) }})"
             @endif>
          <div class="bab-icon">{{ $icon }}</div>
          <div class="bab-num">{{ $p->bab }}</div>
          <div class="bab-stat">{{ $label }}</div>
          @if($p->file_uploaded_at)<div class="bab-date">{{ $p->file_uploaded_at->format('d M Y') }}</div>@endif
        </div>
      @endforeach
    </div>
  </div>
@empty
  <div class="card"><div class="empty-state">📭 Belum ada mahasiswa bimbingan.</div></div>
@endforelse

{{-- MODAL VERIFIKASI --}}
<div class="modal-bg" id="modalVerifikasi">
  <div class="modal-box">
    <div class="modal-title" id="vTitle"></div>

    <div id="vCatatanBox" class="catatan-box" style="display:none">
      <strong>📌 Catatan revisi sebelumnya:</strong>
      <div id="vCatatanText" style="margin-top:4px"></div>
    </div>

    <a href="#" id="vFileLink" target="_blank" class="file-link">📄 <span id="vFileName"></span></a>

    <div id="vFileDosenBox" style="display:none">
      <a href="#" id="vFileDosenLink" target="_blank" class="file-link" style="background:var(--green-50);border-color:var(--green-100);color:var(--green-600)">📎 <span id="vFileDosenName"></span></a>
    </div>

    <form method="POST" id="vForm" enctype="multipart/form-data">
      @csrf
      <div class="form-group" id="vCatatanGroup">
        <label class="form-label">Catatan <small class="text-muted" id="vCatatanHint">(wajib diisi jika minta revisi)</small></label>
        <textarea name="catatan" id="vCatatanInput" class="form-control" rows="3" placeholder="Tulis masukan untuk mahasiswa..."></textarea>
      </div>
      <div class="form-group" id="vFileDosenGroup">
        <label class="form-label">Kirim File ke Mahasiswa <small class="text-muted">(opsional)</small></label>
        <input type="file" name="file_dosen" id="vFileDosenInput" class="form-control" accept=".pdf,.doc,.docx,.zip">
        <p class="form-hint">Opsional — misalnya file koreksi/lampiran tambahan. Format PDF/DOC/DOCX/ZIP, maksimal 10MB.</p>
      </div>
      <div class="modal-footer" id="vFooter">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalVerifikasi')">Tutup</button>
        <button type="submit" name="keputusan" value="revisi" class="btn btn-danger" id="vBtnRevisi" onclick="return cekCatatanRevisi()">🔁 Minta Revisi</button>
        <button type="submit" name="keputusan" value="approved" class="btn btn-success" id="vBtnApprove">✅ Setujui</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function openVerifikasi(id, bab, state, fileUrl, fileName, catatan, fileDosenUrl, fileDosenName) {
  document.getElementById('vForm').action = `{{ url('dosen-area/progress') }}/${id}/verifikasi`;
  document.getElementById('vTitle').textContent = '🔎 ' + bab;
  document.getElementById('vFileLink').href = fileUrl || '#';
  document.getElementById('vFileName').textContent = fileName || 'Lihat file laporan';

  const catBox = document.getElementById('vCatatanBox');
  if (state === 'revisi' && catatan) {
    document.getElementById('vCatatanText').textContent = catatan;
    catBox.style.display = 'block';
  } else {
    catBox.style.display = 'none';
  }

  const fileDosenBox = document.getElementById('vFileDosenBox');
  if (fileDosenUrl) {
    document.getElementById('vFileDosenLink').href = fileDosenUrl;
    document.getElementById('vFileDosenName').textContent = 'File terkirim: ' + (fileDosenName || 'lihat file');
    fileDosenBox.style.display = 'block';
  } else {
    fileDosenBox.style.display = 'none';
  }

  const footer  = document.getElementById('vFooter');
  const catGroup = document.getElementById('vCatatanGroup');
  const fileDosenGroup = document.getElementById('vFileDosenGroup');
  document.getElementById('vCatatanInput').value = '';
  document.getElementById('vFileDosenInput').value = '';

  if (state === 'done') {
    // sudah final, cuma lihat-lihat
    document.getElementById('vBtnRevisi').style.display = 'none';
    document.getElementById('vBtnApprove').style.display = 'none';
    catGroup.style.display = 'none';
    fileDosenGroup.style.display = 'none';
  } else {
    document.getElementById('vBtnRevisi').style.display = '';
    document.getElementById('vBtnApprove').style.display = '';
    catGroup.style.display = '';
    fileDosenGroup.style.display = '';
  }

  openModal('modalVerifikasi');
}

function cekCatatanRevisi() {
  const catatan = document.getElementById('vCatatanInput').value.trim();
  if (!catatan) {
    alert('Catatan wajib diisi supaya mahasiswa tahu apa yang harus diperbaiki.');
    return false;
  }
  return true;
}
</script>
@endpush
@endsection