@extends('layouts.app')
@section('title','Progress Bimbingan')
@section('content')
<div class="page-header"><h1>Progress BAB Mahasiswa Bimbingan</h1><p>Klik tiap BAB untuk update status</p></div>
@forelse($mahasiswas as $m)
@php $pct = $m->progressPersen(); @endphp
<div class="card">
  <div class="card-header">
    <div><h3>{{ $m->nama }} <span style="color:var(--muted);font-weight:400;font-size:13px">{{ $m->nim }}</span></h3><p>{{ $m->instansi?->nama ?? '–' }}</p></div>
    <div style="display:flex;align-items:center;gap:10px">
      <div class="prog-wrap" style="width:100px"><div class="prog-bar" style="width:{{ $pct }}%;background:var(--dosen)"></div></div>
      <span style="font-size:13px;font-weight:700">{{ $pct }}%</span>
      <span class="pill pill-{{ $m->status }}">{{ ucfirst($m->status) }}</span>
    </div>
  </div>
  <div style="padding:16px 20px">
    <div class="bab-grid">
      @foreach($m->progressBabs as $p)
      <div class="bab-item {{ $p->status==='selesai'?'done':($p->status==='proses'?'proses':'') }}"
           style="cursor:pointer" onclick="openUpd({{ $p->id }},'{{ $p->bab }}','{{ $p->status }}','{{ $p->tanggal_update }}','{{ addslashes($p->catatan) }}','{{ $m->nama }}')">
        <div class="bab-name">{{ $p->bab }}</div>
        <div class="bab-status">{{ $p->status==='selesai'?'✅':($p->status==='proses'?'🔄':'⏳') }}</div>
        <div class="bab-label">{{ ucfirst($p->status) }}</div>
        @if($p->tanggal_update)<div style="font-size:9px;color:var(--muted)">{{ $p->tanggal_update }}</div>@endif
      </div>
      @endforeach
    </div>
  </div>
</div>
@empty<div class="card"><div class="card-body" style="text-align:center;color:var(--muted)">Tidak ada mahasiswa bimbingan.</div></div>@endforelse

<div class="modal-bg" id="modalUpd">
  <div class="modal-box">
    <h3>📝 Update Progress BAB</h3>
    <div style="margin-bottom:14px;font-size:13px"><span style="color:var(--muted)">Mahasiswa:</span> <strong id="updNama"></strong> — <strong id="updBab"></strong></div>
    <form method="POST" id="updForm">@csrf @method('PUT')
      <div class="form-group"><label>Status</label>
        <select name="status" id="updStatus" class="form-control">
          <option value="belum">Belum</option><option value="proses">Proses</option><option value="selesai">Selesai</option>
        </select>
      </div>
      <div class="form-group"><label>Tanggal Update</label><input type="date" name="tanggal_update" id="updTgl" class="form-control"></div>
      <div class="form-group"><label>Catatan</label><textarea name="catatan" id="updCat" class="form-control" rows="3"></textarea></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modalUpd')">Batal</button>
        <button type="submit" class="btn btn-success">Simpan</button>
      </div>
    </form>
  </div>
</div>
@push('scripts')
<script>
function openUpd(id,bab,status,tgl,cat,nama){
  document.getElementById('updForm').action=`/dosen/progress/${id}`;
  document.getElementById('updNama').textContent=nama;
  document.getElementById('updBab').textContent=bab;
  document.getElementById('updStatus').value=status;
  document.getElementById('updTgl').value=tgl||'';
  document.getElementById('updCat').value=cat||'';
  openModal('modalUpd');
}
</script>
@endpush
@endsection