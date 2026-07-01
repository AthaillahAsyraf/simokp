@extends('layouts.app')
@section('title','Surat')

@push('styles')
<style>
.thread-item{display:flex;gap:14px;padding:16px 0;border-bottom:1px solid var(--gray-100)}
.thread-item:last-child{border-bottom:none}
.thread-icon{width:38px;height:38px;border-radius:50%;background:var(--amber-50);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.thread-body{flex:1}
.thread-head{display:flex;justify-content:space-between;gap:10px;margin-bottom:4px;flex-wrap:wrap}
.thread-route{font-size:12px;color:var(--gray-500)}
.thread-date{font-size:11px;color:var(--gray-400);white-space:nowrap}
.file-link{display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--blue-600);font-weight:600;padding:6px 10px;border:1px solid var(--blue-100);border-radius:8px;background:var(--blue-50);margin-top:8px}
.file-link:hover{background:var(--blue-100)}
.err-box{background:var(--red-50);border:1px solid var(--red-100);border-radius:6px;padding:10px 14px;margin-bottom:14px}
.err-box ul{margin:4px 0 0 18px;color:var(--red-600);font-size:.82rem}
</style>
@endpush

@section('content')
<div class="page-header"><h1>Surat</h1><p>Surat pengantar yang diteruskan mahasiswa & balasan dari instansi Anda</p></div>

@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif

<div class="card">
  <div class="card-header"><h3>Surat Masuk</h3><p>{{ $suratMasuk->count() }} surat</p></div>
  <div class="card-body">
    @forelse($suratMasuk as $s)
      <div class="thread-item">
        <div class="thread-icon">📥</div>
        <div class="thread-body">
          <div class="thread-head">
            <strong>{{ $s->perihal }}</strong>
            <span class="thread-date">{{ $s->created_at->translatedFormat('d M Y, H:i') }}</span>
          </div>
          <div class="thread-route">Dari mahasiswa: <strong>{{ $s->mahasiswa?->nama }}</strong> ({{ $s->mahasiswa?->nim }})</div>
          @if($s->keterangan)<p class="text-sm" style="margin-top:8px">{{ $s->keterangan }}</p>@endif
          @if($s->file)<div><a href="{{ $s->file_url }}" target="_blank" class="file-link">📄 Lihat Surat Pengantar</a></div>@endif

          @php $sudahDibalas = $s->balasan->where('jenis', \App\Models\Surat::JENIS_BALASAN)->isNotEmpty(); @endphp
          @if($sudahDibalas)
            <div class="alert alert-success" style="margin-top:10px;font-size:12px">✅ Sudah dibalas</div>
          @else
            <div style="margin-top:10px">
              <button class="btn btn-primary btn-sm" onclick="openBalas({{ $s->id }}, {{ json_encode($s->mahasiswa?->nama) }}, {{ json_encode($s->perihal) }})">Balas Surat</button>
            </div>
          @endif

          @foreach($s->balasan->where('jenis', \App\Models\Surat::JENIS_BALASAN) as $b)
            <div style="margin-top:10px;padding:10px 14px;background:var(--gray-50);border-radius:8px">
              <div class="text-sm" style="font-weight:600;margin-bottom:4px">📤 Balasan Anda — {{ $b->created_at->format('d M Y, H:i') }}</div>
              <p class="text-sm">{{ $b->keterangan }}</p>
              @if($b->file)<a href="{{ $b->file_url }}" target="_blank" class="file-link">📄 Lihat File Balasan</a>@endif
            </div>
          @endforeach
        </div>
      </div>
    @empty
      <div style="text-align:center;padding:32px;color:var(--gray-400)">
        <div style="font-size:36px;margin-bottom:10px">✉️</div>
        <p>Belum ada surat masuk dari mahasiswa.</p>
      </div>
    @endforelse
  </div>
</div>

{{-- MODAL BALAS --}}
<div class="modal-bg" id="modalBalas">
  <div class="modal-box">
    <div class="modal-title" id="bTitle">📤 Balas Surat</div>
    @if($errors->balas->any())
    <div class="err-box"><ul>@foreach($errors->balas->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    <form method="POST" id="balasForm" enctype="multipart/form-data">
      @csrf
      <div class="form-group">
        <label class="form-label">Isi Balasan *</label>
        <textarea name="keterangan" class="form-control" rows="4" placeholder="Tulis isi balasan surat..." required></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Lampiran File (opsional)</label>
        <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx">
        <p class="form-hint">PDF/DOC/DOCX, maks 10MB — misal surat balasan resmi berkop instansi.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalBalas')">Batal</button>
        <button type="submit" class="btn btn-primary">Kirim Balasan</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function openBalas(id, nama, perihal) {
  document.getElementById('balasForm').action = `{{ url('instansi-area/surat') }}/${id}/balas`;
  document.getElementById('bTitle').textContent = '📤 Balas — ' + nama + ' (' + perihal + ')';
  openModal('modalBalas');
}

@if($errors->balas->any())
window.addEventListener('load', function () { openModal('modalBalas'); });
@endif
</script>
@endpush
@endsection