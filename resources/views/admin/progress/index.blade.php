@extends('layouts.app')
@section('title','Laporan KP')

@push('styles')
<style>
.mhs-block{border:1px solid var(--gray-200);border-radius:var(--radius);padding:18px;margin-bottom:16px;background:var(--white)}
.mhs-block-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;flex-wrap:wrap;gap:8px}
.mhs-block-head h4{font-size:14px;font-weight:700;color:var(--gray-900)}
.mhs-block-head p{font-size:12px;color:var(--gray-500);margin-top:2px}
.bab-card{cursor:default}
.bab-card.clickable{cursor:pointer}
.bab-card.locked{opacity:.5;background:var(--gray-50)}
.bab-card.menunggu{background:var(--amber-50);border-color:var(--amber-500)}
.bab-card.revisi{background:var(--red-50);border-color:var(--red-500)}
.file-link{display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--blue-600);font-weight:600;padding:8px 12px;border:1px solid var(--blue-100);border-radius:8px;background:var(--blue-50);margin-bottom:14px}
.file-link:hover{background:var(--blue-100)}
.catatan-box{background:var(--red-50);border:1px solid var(--red-100);border-radius:8px;padding:10px 12px;font-size:13px;color:var(--red-600)}
.empty-state{text-align:center;padding:50px 20px;color:var(--gray-400)}
.filter-row{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.filter-row .form-control{width:auto;min-width:160px}
</style>
@endpush

@section('content')
<div class="page-header"><h1>Laporan KP</h1><p>Monitoring progress laporan per BAB seluruh mahasiswa (read-only — verifikasi dilakukan oleh Dosen Pembimbing)</p></div>

<form method="GET" class="filter-row">
  <input type="text" name="search" class="form-control" placeholder="🔍 Cari nama/NIM..." value="{{ request('search') }}">
  <select name="status" class="form-control">
    <option value="">Semua Status</option>
    @foreach(['proses'=>'Proses','seminar'=>'Seminar','selesai'=>'Selesai'] as $val=>$lbl)
      <option value="{{ $val }}" {{ request('status')==$val?'selected':'' }}>{{ $lbl }}</option>
    @endforeach
  </select>
  <select name="instansi" class="form-control">
    <option value="">Semua Instansi</option>
    @foreach($instansis as $i)
      <option value="{{ $i->id }}" {{ request('instansi')==$i->id?'selected':'' }}>{{ $i->nama }}</option>
    @endforeach
  </select>
  <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
  <a href="{{ route('admin.progress.index') }}" class="btn btn-outline btn-sm">Reset</a>
</form>

@forelse($mahasiswas as $m)
  @php
    $pct = $m->progressPersen();
    $adaMenunggu = $m->progressBabs->contains('verifikasi_status','menunggu');
    $adaRevisi   = $m->progressBabs->contains('verifikasi_status','revisi');
  @endphp
  <div class="mhs-block">
    <div class="mhs-block-head">
      <div>
        <h4>{{ $m->nama }} <span style="color:var(--gray-400);font-weight:500">— {{ $m->nim }}</span>
          @if($adaMenunggu)<span class="badge badge-proses" style="margin-left:6px">🕓 Menunggu Verifikasi</span>@endif
          @if($adaRevisi)<span class="badge" style="background:var(--red-100);color:var(--red-600);margin-left:6px">🔁 Ada Revisi</span>@endif
        </h4>
        <p>Pembimbing: {{ $m->dosen->nama ?? '—' }} &middot; Instansi: {{ $m->instansi->nama ?? 'Belum ditempatkan' }}</p>
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        <div class="prog-wrap" style="width:120px;height:7px"><div class="prog-bar prog-bar-blue" style="width:{{ $pct }}%"></div></div>
        <span style="font-size:12px;font-weight:700;color:var(--blue-600)">{{ $pct }}%</span>
      </div>
    </div>

    <div class="bab-grid">
      @foreach($m->progressBabs->sortBy('id') as $p)
        @php
          $locked = !$p->bisaDiupload();
          $state  = $locked ? 'locked' : ($p->sudahApproved() ? 'done' : ($p->verifikasi_status === 'revisi' ? 'revisi' : ($p->verifikasi_status === 'menunggu' ? 'menunggu' : '')));
          $icon   = ['done'=>'✅','menunggu'=>'🕓','revisi'=>'🔁','locked'=>'🔒'][$state] ?? '⬜';
          $label  = ['done'=>'Disetujui','menunggu'=>'Menunggu Verifikasi','revisi'=>'Revisi','locked'=>'Belum Diupload'][$state] ?? 'Belum Diupload';
        @endphp
        <div class="bab-card {{ $state }} {{ $p->file ? 'clickable' : '' }}"
             @if($p->file)
             onclick="lihatFile({{ $p->id }}, '{{ $p->bab }}', {{ json_encode($p->file_url) }}, {{ json_encode($p->file_asli) }}, {{ json_encode($p->catatan) }})"
             @endif>
          <div class="bab-icon">{{ $icon }}</div>
          <div class="bab-num">{{ $p->bab }}</div>
          <div class="bab-stat">{{ $label }}</div>
        </div>
      @endforeach
    </div>
  </div>
@empty
  <div class="card"><div class="empty-state">📭 Tidak ada data mahasiswa yang cocok dengan filter.</div></div>
@endforelse

{{-- MODAL LIHAT FILE (read-only) --}}
<div class="modal-bg" id="modalLihat">
  <div class="modal-box">
    <div class="modal-title" id="lTitle"></div>
    <a href="#" id="lFileLink" target="_blank" class="file-link">📄 <span id="lFileName"></span></a>
    <div id="lCatatanBox" class="catatan-box" style="display:none;margin-top:4px">
      <strong>📌 Catatan dosen:</strong>
      <div id="lCatatanText" style="margin-top:4px"></div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-outline" onclick="closeModal('modalLihat')">Tutup</button>
    </div>
  </div>
</div>

@push('scripts')
<script>
function lihatFile(id, bab, fileUrl, fileName, catatan) {
  document.getElementById('lTitle').textContent = '📄 ' + bab;
  document.getElementById('lFileLink').href = fileUrl || '#';
  document.getElementById('lFileName').textContent = fileName || 'Lihat file laporan';

  const catBox = document.getElementById('lCatatanBox');
  if (catatan) {
    document.getElementById('lCatatanText').textContent = catatan;
    catBox.style.display = 'block';
  } else {
    catBox.style.display = 'none';
  }
  openModal('modalLihat');
}
</script>
@endpush
@endsection