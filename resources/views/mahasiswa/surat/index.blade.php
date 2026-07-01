@extends('layouts.app')
@section('title','Surat')

@push('styles')
<style>
.err-box{background:var(--red-50);border:1px solid var(--red-100);border-radius:6px;padding:10px 14px;margin-bottom:14px}
.err-box ul{margin:4px 0 0 18px;color:var(--red-600);font-size:.82rem}
.info-box{background:var(--blue-50);border:1px solid var(--blue-100);border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:var(--blue-700);display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.thread-item{display:flex;gap:14px;padding:16px 0;border-bottom:1px solid var(--gray-100)}
.thread-item:last-child{border-bottom:none}
.thread-icon{width:38px;height:38px;border-radius:50%;background:var(--purple-50);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.thread-body{flex:1}
.thread-head{display:flex;justify-content:space-between;gap:10px;margin-bottom:4px;flex-wrap:wrap}
.thread-route{font-size:12px;color:var(--gray-500)}
.thread-date{font-size:11px;color:var(--gray-400);white-space:nowrap}
.file-link{display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--blue-600);font-weight:600;padding:6px 10px;border:1px solid var(--blue-100);border-radius:8px;background:var(--blue-50);margin-top:8px}
.file-link:hover{background:var(--blue-100)}
</style>
@endpush

@section('content')
<div class="page-header"><h1>Surat</h1><p>Permohonan surat pengantar & korespondensi dengan admin dan instansi</p></div>

@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">⚠️ {{ session('error') }}</div>@endif
@if($errors->permohonan->any())
  <div class="err-box"><strong>⚠️ Terdapat kesalahan:</strong>
    <ul>@foreach($errors->permohonan->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

@if($pengantarBelumDiteruskan)
  <div class="info-box">
    <span>📨 Anda punya surat pengantar dari admin yang belum diteruskan ke <strong>{{ $mahasiswa->instansi?->nama ?? 'instansi' }}</strong>.</span>
    <button class="btn btn-primary btn-sm" onclick="openTeruskan({{ $pengantarBelumDiteruskan->id }}, {{ json_encode($pengantarBelumDiteruskan->perihal) }})">Teruskan Sekarang</button>
  </div>
@endif

<div class="card" style="margin-bottom:20px">
  <div class="card-header">
    <h3>Ajukan Permohonan Surat Pengantar</h3>
  </div>
  <div class="card-body">
    @if($adaPending)
      <div class="alert alert-info">🕓 Permohonan surat Anda sedang menunggu diproses admin.</div>
    @else
      <form method="POST" action="{{ route('mahasiswa.surat.store') }}">
        @csrf
        <div class="form-group">
          <label class="form-label">Perihal *</label>
          <input type="text" name="perihal" class="form-control" placeholder="cth: Permohonan Surat Pengantar KP" value="{{ old('perihal') }}" required>
        </div>
        <div class="form-group">
          <label class="form-label">Keterangan / Keperluan *</label>
          <textarea name="keterangan" class="form-control" rows="3" placeholder="Jelaskan keperluan surat pengantar ini..." required>{{ old('keterangan') }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Kirim Permohonan</button>
      </form>
    @endif
  </div>
</div>

<div class="card">
  <div class="card-header"><h3>Riwayat Korespondensi</h3><p>{{ $surats->count() }} surat</p></div>
  <div class="card-body">
    @forelse($surats as $s)
      <div class="thread-item">
        <div class="thread-icon">{{ $s->pengirim_role === 'mahasiswa' ? '📤' : '📥' }}</div>
        <div class="thread-body">
          <div class="thread-head">
            <strong>{{ $s->perihal ?? $s->jenis_label }}</strong>
            <span class="thread-date">{{ $s->created_at->translatedFormat('d M Y, H:i') }}</span>
          </div>
          <div class="thread-route">{{ $s->pengirim_nama }} ➜ {{ $s->penerima_nama }} &middot; {{ $s->jenis_label }}</div>
          @if($s->keterangan)<p class="text-sm" style="margin-top:8px">{{ $s->keterangan }}</p>@endif
          @if($s->file)<a href="{{ $s->file_url }}" target="_blank" class="file-link">📄 Lihat File</a>@endif
          <div style="margin-top:8px">
            @if($s->jenis === \App\Models\Surat::JENIS_PERMOHONAN)
              <span class="badge {{ $s->status === 'disetujui' ? 'badge-selesai' : ($s->status === 'ditolak' ? '' : 'badge-proses') }}"
                @if($s->status === 'ditolak') style="background:var(--red-100);color:var(--red-600)" @endif>
                {{ ucfirst($s->status) }}
              </span>
              @if($s->status === 'ditolak' && $s->catatan)
                <p class="text-sm" style="color:var(--red-600);margin-top:6px">Alasan: {{ $s->catatan }}</p>
              @endif
            @endif
          </div>
        </div>
      </div>
    @empty
      <div style="text-align:center;padding:32px;color:var(--gray-400)">
        <div style="font-size:36px;margin-bottom:10px">✉️</div>
        <p>Belum ada riwayat surat.</p>
      </div>
    @endforelse
  </div>
</div>

{{-- MODAL TERUSKAN --}}
<div class="modal-bg" id="modalTeruskan">
  <div class="modal-box">
    <div class="modal-title" id="tTitle">📨 Teruskan Surat Pengantar</div>
    <form method="POST" id="teruskanForm">
      @csrf
      <p class="form-hint" style="margin-bottom:14px">Surat pengantar ini akan dikirim ke <strong>{{ $mahasiswa->instansi?->nama ?? '-' }}</strong>.</p>
      <div class="form-group">
        <label class="form-label">Catatan tambahan (opsional)</label>
        <textarea name="catatan_teruskan" class="form-control" rows="3" placeholder="Catatan tambahan untuk instansi..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalTeruskan')">Batal</button>
        <button type="submit" class="btn btn-primary">Teruskan</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function openTeruskan(id, perihal) {
  document.getElementById('teruskanForm').action = `{{ url('mahasiswa/surat') }}/${id}/teruskan`;
  document.getElementById('tTitle').textContent = '📨 Teruskan — ' + perihal;
  openModal('modalTeruskan');
}
</script>
@endpush
@endsection