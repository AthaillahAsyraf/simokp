@extends('layouts.app')
@section('title','Monitoring Progress BAB')
@section('content')

<div class="page-header">
  <h1>Monitoring Progress BAB</h1>
  <p>Pantau dan update progress penulisan laporan per mahasiswa</p>
</div>

@foreach($mahasiswas as $m)
@php $pct = $m->progressPersen(); @endphp
<div class="card">
  <div class="card-header">
    <div>
      <h3>{{ $m->nama }} <span style="color:var(--muted);font-weight:400;font-size:13px">{{ $m->nim }}</span></h3>
      <p>{{ $m->instansi?->nama ?? '–' }} · Pembimbing: {{ $m->dosen?->nama ?? '–' }}</p>
    </div>
    <div style="display:flex;align-items:center;gap:12px">
      <div class="prog-wrap" style="width:120px"><div class="prog-bar" style="width:{{ $pct }}%;background:var(--admin)"></div></div>
      <span style="font-size:13px;font-weight:700">{{ $pct }}%</span>
      <span class="pill pill-{{ $m->status }}">{{ ucfirst($m->status) }}</span>
    </div>
  </div>
  <div style="padding:16px 20px">
    <div class="bab-grid">
      @foreach($m->progressBabs as $p)
      <div class="bab-item {{ $p->status === 'selesai' ? 'done' : ($p->status === 'proses' ? 'proses' : '') }}"
           style="cursor:pointer" onclick="openUpdateModal({{ $p->id }},'{{ $p->bab }}','{{ $p->status }}','{{ $p->tanggal_update }}','{{ addslashes($p->catatan) }}','{{ $m->nama }}')">
        <div class="bab-name">{{ $p->bab }}</div>
        <div class="bab-status">{{ $p->status === 'selesai' ? '✅' : ($p->status === 'proses' ? '🔄' : '⏳') }}</div>
        <div class="bab-label">{{ ucfirst($p->status) }}</div>
        @if($p->tanggal_update)<div style="font-size:9px;color:var(--muted);margin-top:2px">{{ $p->tanggal_update }}</div>@endif
      </div>
      @endforeach
    </div>
  </div>
</div>
@endforeach

{{-- MODAL UPDATE BAB --}}
<div class="modal-bg" id="modalUpdate">
  <div class="modal-box">
    <h3>📝 Update Progress BAB</h3>
    <div style="margin-bottom:16px;font-size:13px">
      <span style="color:var(--muted)">Mahasiswa:</span> <strong id="mUpdateNama"></strong> &nbsp;|&nbsp; <strong id="mUpdateBab"></strong>
    </div>
    <form method="POST" id="updateForm">
      @csrf @method('PUT')
      <div class="form-group">
        <label>Status</label>
        <select name="status" id="uStatus" class="form-control">
          <option value="belum">Belum</option>
          <option value="proses">Proses</option>
          <option value="selesai">Selesai</option>
        </select>
      </div>
      <div class="form-group">
        <label>Tanggal Update</label>
        <input type="date" name="tanggal_update" id="uTgl" class="form-control">
      </div>
      <div class="form-group">
        <label>Catatan</label>
        <textarea name="catatan" id="uCatatan" class="form-control" rows="3" placeholder="Catatan untuk mahasiswa..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modalUpdate')">Batal</button>
        <button type="submit" class="btn btn-success">Simpan Update</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function openUpdateModal(id, bab, status, tgl, catatan, nama) {
  document.getElementById('updateForm').action = `/admin/progress/${id}`;
  document.getElementById('mUpdateNama').textContent = nama;
  document.getElementById('mUpdateBab').textContent = bab;
  document.getElementById('uStatus').value = status;
  document.getElementById('uTgl').value = tgl || '';
  document.getElementById('uCatatan').value = catatan || '';
  openModal('modalUpdate');
}
</script>
@endpush
@endsection