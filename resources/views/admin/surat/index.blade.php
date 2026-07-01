@extends('layouts.app')
@section('title','Surat')

@push('styles')
<style>
.tab-bar{display:flex;gap:4px;margin-bottom:18px;border-bottom:1.5px solid var(--gray-200)}
.tab-btn{padding:10px 18px;font-size:13px;font-weight:600;color:var(--gray-500);background:none;border:none;cursor:pointer;border-bottom:2.5px solid transparent;margin-bottom:-1.5px}
.tab-btn:hover{color:var(--gray-800)}
.tab-btn.active{color:var(--blue-600);border-bottom-color:var(--blue-600)}
.tab-panel{display:none}
.tab-panel.active{display:block}
.err-box{background:var(--red-50);border:1px solid var(--red-100);border-radius:6px;padding:10px 14px;margin-bottom:14px}
.err-box ul{margin:4px 0 0 18px;color:var(--red-600);font-size:.82rem}
.file-link{display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--blue-600);font-weight:600;padding:6px 10px;border:1px solid var(--blue-100);border-radius:8px;background:var(--blue-50)}
</style>
@endpush

@section('content')
<div class="page-header"><h1>Surat</h1><p>Kelola permohonan surat pengantar dari mahasiswa & pantau korespondensi lintas pihak</p></div>

@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif

<div class="tab-bar">
  <button type="button" class="tab-btn" id="tabBtnMasuk" onclick="switchTab('masuk')">📥 Permohonan Masuk</button>
  <button type="button" class="tab-btn" id="tabBtnRiwayat" onclick="switchTab('riwayat')">🗂️ Semua Riwayat</button>
</div>

{{-- TAB: PERMOHONAN MASUK --}}
<div class="tab-panel" id="panelMasuk">
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Mahasiswa</th><th>Perihal</th><th>Keterangan</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
          @forelse($permohonanMasuk as $s)
          <tr>
            <td><strong>{{ $s->mahasiswa?->nama }}</strong><br><code class="text-sm">{{ $s->mahasiswa?->nim }}</code></td>
            <td class="text-sm">{{ $s->perihal }}</td>
            <td class="text-sm text-muted">{{ \Illuminate\Support\Str::limit($s->keterangan, 60) }}</td>
            <td class="text-sm text-muted">{{ $s->created_at->format('d M Y') }}</td>
            <td><span class="badge {{ $s->status === 'disetujui' ? 'badge-selesai' : ($s->status === 'ditolak' ? '' : 'badge-proses') }}"
                  @if($s->status === 'ditolak') style="background:var(--red-100);color:var(--red-600)" @endif>{{ ucfirst($s->status) }}</span></td>
            <td>
              @if($s->status === 'pending')
              <div style="display:flex;gap:4px">
                <button class="btn btn-success btn-xs" onclick="openApprove({{ $s->id }}, {{ json_encode($s->mahasiswa?->nama) }}, {{ json_encode($s->perihal) }})">✅ Setujui</button>
                <button class="btn btn-danger btn-xs" onclick="openReject({{ $s->id }}, {{ json_encode($s->mahasiswa?->nama) }})">✖️ Tolak</button>
              </div>
              @else
                <span class="text-sm text-muted">Sudah diproses</span>
              @endif
            </td>
          </tr>
          @empty
            <tr><td colspan="6" style="text-align:center;padding:28px;color:var(--gray-400)">Belum ada permohonan surat masuk.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- TAB: SEMUA RIWAYAT --}}
<div class="tab-panel" id="panelRiwayat">
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Mahasiswa</th><th>Perihal</th><th>Dari</th><th>Ke</th><th>Jenis</th><th>File</th><th>Tanggal</th></tr></thead>
        <tbody>
          @forelse($semuaRiwayat as $s)
          <tr>
            <td><strong>{{ $s->mahasiswa?->nama }}</strong></td>
            <td class="text-sm">{{ $s->perihal }}</td>
            <td class="text-sm text-muted">{{ $s->pengirim_nama }}</td>
            <td class="text-sm text-muted">{{ $s->penerima_nama }}</td>
            <td class="text-sm">{{ $s->jenis_label }}</td>
            <td>@if($s->file)<a href="{{ $s->file_url }}" target="_blank" class="file-link">📄 Lihat</a>@else<span class="text-muted">–</span>@endif</td>
            <td class="text-sm text-muted">{{ $s->created_at->format('d M Y H:i') }}</td>
          </tr>
          @empty
            <tr><td colspan="7" style="text-align:center;padding:28px;color:var(--gray-400)">Belum ada riwayat surat.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- MODAL APPROVE --}}
<div class="modal-bg" id="modalApprove">
  <div class="modal-box">
    <div class="modal-title" id="apTitle">✅ Setujui & Upload Surat Pengantar</div>
    @if($errors->approve->any())
    <div class="err-box"><ul>@foreach($errors->approve->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    <form method="POST" id="approveForm" enctype="multipart/form-data">
      @csrf
      <div class="form-group">
        <label class="form-label">File Surat Pengantar *</label>
        <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx" required>
        <p class="form-hint">Upload file surat pengantar resmi (PDF/DOC/DOCX, maks 10MB) yang akan dikirim ke mahasiswa.</p>
      </div>
      <div class="form-group">
        <label class="form-label">Catatan (opsional)</label>
        <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan tambahan untuk mahasiswa..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalApprove')">Batal</button>
        <button type="submit" class="btn btn-success">Setujui & Kirim</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL REJECT --}}
<div class="modal-bg" id="modalReject">
  <div class="modal-box">
    <div class="modal-title" id="rjTitle">✖️ Tolak Permohonan</div>
    <form method="POST" id="rejectForm">
      @csrf
      <div class="form-group">
        <label class="form-label">Alasan Penolakan *</label>
        <textarea name="catatan" class="form-control" rows="3" required></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalReject')">Batal</button>
        <button type="submit" class="btn btn-danger">Tolak</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function switchTab(tab) {
  const isRiwayat = tab === 'riwayat';
  document.getElementById('panelMasuk').classList.toggle('active', !isRiwayat);
  document.getElementById('panelRiwayat').classList.toggle('active', isRiwayat);
  document.getElementById('tabBtnMasuk').classList.toggle('active', !isRiwayat);
  document.getElementById('tabBtnRiwayat').classList.toggle('active', isRiwayat);
}
switchTab('masuk');

function openApprove(id, nama, perihal) {
  document.getElementById('approveForm').action = `{{ url('admin/surat') }}/${id}/approve`;
  document.getElementById('apTitle').textContent = '✅ Setujui — ' + nama + ' (' + perihal + ')';
  openModal('modalApprove');
}
function openReject(id, nama) {
  document.getElementById('rejectForm').action = `{{ url('admin/surat') }}/${id}/reject`;
  document.getElementById('rjTitle').textContent = '✖️ Tolak — ' + nama;
  openModal('modalReject');
}

@if($errors->approve->any())
window.addEventListener('load', function () { openModal('modalApprove'); });
@endif
</script>
@endpush
@endsection