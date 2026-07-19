@extends('layouts.app')
@section('title','Persyaratan KP')

@push('styles')
<style>
.mhs-block{border:1px solid var(--gray-200);border-radius:var(--radius);padding:18px;margin-bottom:16px;background:var(--white)}
.mhs-block-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;flex-wrap:wrap;gap:8px}
.mhs-block-head h4{font-size:14px;font-weight:700;color:var(--gray-900)}
.mhs-block-head p{font-size:12px;color:var(--gray-500);margin-top:2px}
.berkas-mini{display:inline-flex;align-items:center;gap:6px;font-size:12px;padding:6px 10px;border-radius:8px;margin:0 6px 6px 0}
.berkas-mini.ada{background:var(--blue-50);color:var(--blue-600);border:1px solid var(--blue-100)}
.berkas-mini.kosong{background:var(--gray-50);color:var(--gray-400);border:1px solid var(--gray-100)}
.empty-state{text-align:center;padding:50px 20px;color:var(--gray-400)}
.filter-row{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
</style>
@endpush

@section('content')
<div class="page-header"><h1>Persyaratan KP</h1><p>Verifikasi berkas administrasi (Form Pengajuan, Bukti SPP, KRS, Transkrip Nilai) sebelum mahasiswa lanjut ke tahap penempatan instansi.</p></div>

@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">⚠️ {{ session('error') }}</div>@endif

<form method="GET" class="filter-row">
  <input type="text" name="search" class="form-control" placeholder="🔍 Cari nama/NIM..." value="{{ request('search') }}">
  <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
  <a href="{{ route('admin.persyaratan.index') }}" class="btn btn-outline btn-sm">Reset</a>
</form>

<div class="card">
  <div class="card-header"><h3>🕓 Menunggu Verifikasi ({{ $menungguVerifikasi->count() }})</h3></div>
  <div class="card-body">
    @forelse($menungguVerifikasi as $m)
      @include('admin.persyaratan._row', ['m' => $m, 'bisaVerifikasi' => true])
    @empty
      <div class="empty-state">📭 Tidak ada berkas yang menunggu verifikasi.</div>
    @endforelse
  </div>
</div>

<div class="card">
  <div class="card-header"><h3>📋 Belum Lengkap / Direvisi ({{ $belumLengkapOrRevisi->count() }})</h3></div>
  <div class="card-body">
    @forelse($belumLengkapOrRevisi as $m)
      @include('admin.persyaratan._row', ['m' => $m, 'bisaVerifikasi' => false])
    @empty
      <div class="empty-state">📭 Tidak ada data.</div>
    @endforelse
  </div>
</div>

<div class="card">
  <div class="card-header"><h3>✅ Sudah Disetujui ({{ $sudahDisetujui->count() }})</h3></div>
  <div class="card-body">
    @forelse($sudahDisetujui as $m)
      @include('admin.persyaratan._row', ['m' => $m, 'bisaVerifikasi' => false])
    @empty
      <div class="empty-state">📭 Belum ada berkas yang disetujui.</div>
    @endforelse
  </div>
</div>

{{-- MODAL VERIFIKASI --}}
<div class="modal-bg" id="modalVerifikasi">
  <div class="modal-box">
    <div class="modal-title" id="vTitle"></div>
    <form method="POST" id="vForm">
      @csrf
      <div class="form-group">
        <label class="form-label">Keputusan *</label>
        <select name="keputusan" id="vKeputusan" class="form-control" required onchange="toggleCatatan()">
          <option value="disetujui">✅ Setujui — mahasiswa lanjut ke tahap instansi</option>
          <option value="revisi">🔁 Minta Revisi</option>
        </select>
      </div>
      <div class="form-group" id="vCatatanGroup" style="display:none">
        <label class="form-label">Catatan Revisi *</label>
        <textarea name="catatan" class="form-control" rows="3" placeholder="Jelaskan berkas mana yang perlu diperbaiki..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalVerifikasi')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function bukaVerifikasi(id, nama) {
  document.getElementById('vForm').action = `{{ url('admin/persyaratan') }}/${id}/verifikasi`;
  document.getElementById('vTitle').textContent = '🔍 Verifikasi Berkas — ' + nama;
  document.getElementById('vKeputusan').value = 'disetujui';
  toggleCatatan();
  openModal('modalVerifikasi');
}
function toggleCatatan() {
  const isRevisi = document.getElementById('vKeputusan').value === 'revisi';
  document.getElementById('vCatatanGroup').style.display = isRevisi ? '' : 'none';
}
</script>
@endpush
@endsection