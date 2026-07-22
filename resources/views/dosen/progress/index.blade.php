@extends('layouts.app')
@section('title','Manajemen Bimbingan')

@push('styles')
<style>
.bim-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:20px}.bim-stat{background:var(--white);border:1px solid var(--gray-200);border-radius:12px;padding:16px}.bim-stat-label{font-size:12px;color:var(--gray-500);font-weight:600}.bim-stat-val{font-size:26px;font-weight:800;color:var(--gray-900);margin-top:4px}.bim-stat.pending .bim-stat-val{color:var(--amber-600)}.bim-stat.acc .bim-stat-val{color:var(--purple-600)}
.bim-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap}.filter-group{display:flex;gap:7px;flex-wrap:wrap}.filter-btn{border:1px solid var(--gray-200);background:var(--white);color:var(--gray-600);padding:7px 11px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer}.filter-btn.active,.filter-btn:hover{background:var(--green-50);color:var(--green-700);border-color:var(--green-200)}.search-box{width:260px;max-width:100%;padding:8px 11px;border:1px solid var(--gray-200);border-radius:8px}
.student-card{border:1px solid var(--gray-200);border-radius:12px;background:var(--white);margin-bottom:12px;overflow:hidden}.student-top{padding:16px 18px;display:flex;gap:12px;align-items:flex-start}.student-avatar{width:38px;height:38px;border-radius:50%;background:var(--green-50);color:var(--green-700);display:flex;align-items:center;justify-content:center;font-weight:800;flex:none}.student-main{min-width:0;flex:1}.student-name{font-size:14px;font-weight:800;color:var(--gray-900)}.student-meta{font-size:12px;color:var(--gray-500);margin-top:3px}.student-tags{display:flex;gap:6px;flex-wrap:wrap;margin-top:9px}.tag{font-size:11px;font-weight:700;padding:4px 8px;border-radius:99px}.tag.pending{background:var(--amber-50);color:var(--amber-600)}.tag.revisi{background:var(--red-50);color:var(--red-600)}.tag.done{background:var(--green-50);color:var(--green-600)}.tag.acc{background:var(--purple-50);color:var(--purple-600)}
.student-detail{border-top:1px solid var(--gray-100)}.student-detail>summary{cursor:pointer;list-style:none;padding:11px 18px;color:var(--green-700);font-size:12px;font-weight:700}.student-detail>summary::-webkit-details-marker{display:none}.student-detail>summary:after{content:'Lihat riwayat dan tindak lanjuti';float:right;color:var(--gray-400);font-weight:500}.student-detail[open]>summary:after{content:'Tutup'}
.entry{padding:15px 18px;border-top:1px solid var(--gray-100)}.entry-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start}.entry-title{font-size:13px;font-weight:800}.entry-info{font-size:12px;color:var(--gray-500);margin-top:4px}.entry-desc{font-size:13px;line-height:1.55;margin:10px 0;color:var(--gray-700)}.dosen-note{font-size:12px;background:var(--gray-50);padding:9px 11px;border-radius:7px;margin-top:9px}.review-form{margin-top:12px;padding:12px;background:var(--amber-50);border-radius:9px}.review-form textarea{background:var(--white)}.status{font-size:11px;font-weight:700;padding:4px 8px;border-radius:99px;white-space:nowrap}.status.menunggu{background:var(--amber-50);color:var(--amber-600)}.status.revisi{background:var(--red-50);color:var(--red-600)}.status.disetujui{background:var(--green-50);color:var(--green-600)}.empty-panel{padding:30px;text-align:center;color:var(--gray-500);border:1px dashed var(--gray-200);border-radius:12px;background:var(--white)}
@media(max-width:700px){.bim-summary{grid-template-columns:1fr}.search-box{width:100%}.student-top{padding:14px}.entry{padding:14px}.student-detail>summary{padding:11px 14px}}
</style>
@endpush

@section('content')
@php
  $semuaBimbingan = $mahasiswas->flatMap->bimbingans;
  $menunggu = $semuaBimbingan->where('status', \App\Models\Bimbingan::STATUS_MENUNGGU);
  $accMenunggu = $menunggu->filter(fn ($b) => $b->isPermintaanAccSeminar());
  $mahasiswaAktif = $mahasiswas->filter(fn ($m) => $m->bimbingans->isNotEmpty());
@endphp
<div class="page-header"><h1>Manajemen Bimbingan</h1><p>Prioritaskan unggahan yang perlu ditinjau, lalu lihat riwayat mahasiswa bila diperlukan.</p></div>
@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">⚠️ {{ session('error') }}</div>@endif

<div class="bim-summary">
  <div class="bim-stat pending"><div class="bim-stat-label">Perlu ditinjau</div><div class="bim-stat-val">{{ $menunggu->count() }}</div><div class="text-sm text-muted">unggahan menunggu keputusan</div></div>
  <div class="bim-stat acc"><div class="bim-stat-label">Permintaan ACC seminar</div><div class="bim-stat-val">{{ $accMenunggu->count() }}</div><div class="text-sm text-muted">perlu persetujuan Anda</div></div>
  <div class="bim-stat"><div class="bim-stat-label">Mahasiswa aktif</div><div class="bim-stat-val">{{ $mahasiswaAktif->count() }}</div><div class="text-sm text-muted">dari {{ $mahasiswas->count() }} mahasiswa bimbingan</div></div>
</div>

<div class="bim-toolbar">
  <div class="filter-group">
    <button class="filter-btn active" data-filter="menunggu">Perlu ditinjau ({{ $menunggu->count() }})</button>
    <button class="filter-btn" data-filter="semua">Semua aktivitas</button>
    <button class="filter-btn" data-filter="revisi">Perlu revisi</button>
    <button class="filter-btn" data-filter="kosong">Belum ada unggahan</button>
  </div>
  <input type="search" id="searchMahasiswa" class="search-box" placeholder="Cari nama atau NIM mahasiswa...">
</div>

<div id="studentList">
@forelse($mahasiswas as $m)
  @php
    $entries = $m->bimbingans;
    $jumlahMenunggu = $entries->where('status', \App\Models\Bimbingan::STATUS_MENUNGGU)->count();
    $jumlahRevisi = $entries->where('status', \App\Models\Bimbingan::STATUS_REVISI)->count();
    $adaAccMenunggu = $entries->contains(fn ($b) => $b->isPermintaanAccSeminar() && $b->status === \App\Models\Bimbingan::STATUS_MENUNGGU);
    $filterStatus = $entries->isEmpty() ? 'kosong' : ($jumlahMenunggu ? 'menunggu' : ($jumlahRevisi ? 'revisi' : 'selesai'));
  @endphp
  <article class="student-card" data-status="{{ $filterStatus }}" data-search="{{ strtolower($m->nama.' '.$m->nim) }}">
    <div class="student-top">
      <div class="student-avatar">{{ $m->inisial() }}</div>
      <div class="student-main">
        <div class="student-name">{{ $m->nama }} <span class="text-muted" style="font-weight:500">— {{ $m->nim }}</span></div>
        <div class="student-meta">{{ $m->instansi?->nama ?? 'Instansi belum ditentukan' }}</div>
        <div class="student-tags">
          @if($jumlahMenunggu)<span class="tag pending">{{ $jumlahMenunggu }} perlu ditinjau</span>@endif
          @if($adaAccMenunggu)<span class="tag acc">ACC seminar</span>@endif
          @if($jumlahRevisi)<span class="tag revisi">{{ $jumlahRevisi }} revisi</span>@endif
          @if($entries->isEmpty())<span class="tag done">Belum ada unggahan</span>@endif
          @if($entries->isNotEmpty() && !$jumlahMenunggu && !$jumlahRevisi)<span class="tag done">Bimbingan terakhir disetujui</span>@endif
        </div>
      </div>
    </div>
    @if($entries->isNotEmpty())
    <details class="student-detail" @if($jumlahMenunggu) open @endif>
      <summary>{{ $jumlahMenunggu ? 'Tinjau unggahan yang menunggu' : 'Lihat riwayat bimbingan' }}</summary>
      @foreach($entries as $b)
        <div class="entry">
          <div class="entry-head"><div><div class="entry-title">{{ $b->isPermintaanAccSeminar() ? '🎓 Permintaan ACC Seminar' : '📄 '.$b->file_asli }}</div><div class="entry-info">Dikirim {{ $b->created_at->translatedFormat('d M Y, H:i') }} @if($b->file) · <a href="{{ $b->file_url }}" target="_blank">Buka file</a>@endif</div></div><span class="status {{ $b->status }}">{{ ucfirst($b->status) }}</span></div>
          <p class="entry-desc">{{ $b->keterangan }}</p>
          @if($b->catatan_dosen)<div class="dosen-note"><strong>Catatan Anda:</strong> {{ $b->catatan_dosen }}</div>@endif
          @if($b->status === \App\Models\Bimbingan::STATUS_MENUNGGU)
            <form method="POST" action="{{ route('dosen.progress.verifikasi', $b) }}" class="review-form">@csrf
              <label class="form-label">Tanggapan untuk mahasiswa <span class="text-muted">(wajib untuk revisi)</span></label>
              <textarea name="catatan" class="form-control" rows="2" placeholder="Tuliskan arahan atau catatan perbaikan..."></textarea>
              <div style="display:flex;gap:8px;margin-top:9px;flex-wrap:wrap"><button class="btn btn-danger btn-xs" name="keputusan" value="revisi">Minta Revisi</button><button class="btn btn-success btn-xs" name="keputusan" value="approved">{{ $b->isPermintaanAccSeminar() ? 'Setujui ACC Seminar' : 'Setujui Bimbingan' }}</button></div>
            </form>
          @endif
        </div>
      @endforeach
    </details>
    @endif
  </article>
@empty
  <div class="empty-panel">Belum ada mahasiswa bimbingan.</div>
@endforelse
</div>
<div id="noResult" class="empty-panel" style="display:none">Tidak ada mahasiswa yang sesuai dengan filter ini.</div>

@push('scripts')
<script>
const filterButtons = document.querySelectorAll('[data-filter]');
const cards = document.querySelectorAll('.student-card');
const searchInput = document.getElementById('searchMahasiswa');
let activeFilter = 'menunggu';
function applyFilter() {
  const query = searchInput.value.toLowerCase().trim(); let visible = 0;
  cards.forEach(card => { const matchesFilter = activeFilter === 'semua' || card.dataset.status === activeFilter; const matchesSearch = !query || card.dataset.search.includes(query); const show = matchesFilter && matchesSearch; card.style.display = show ? '' : 'none'; if (show) visible++; });
  document.getElementById('noResult').style.display = visible ? 'none' : '';
}
filterButtons.forEach(button => button.addEventListener('click', () => { activeFilter = button.dataset.filter; filterButtons.forEach(item => item.classList.toggle('active', item === button)); applyFilter(); }));
searchInput.addEventListener('input', applyFilter); applyFilter();
</script>
@endpush
@endsection
