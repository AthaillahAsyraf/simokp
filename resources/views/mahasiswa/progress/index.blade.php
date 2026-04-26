@extends('layouts.app')
@section('title','Progress BAB')
@section('content')
@php $pct = $mahasiswa->progressPersen(); @endphp
<div class="page-header"><h1>Progress BAB Laporan KP</h1><p>Update status pengerjaan laporan Anda</p></div>
<div class="card">
  <div class="card-header">
    <div><h3>Status per BAB</h3><p>{{ $pct }}% selesai</p></div>
    <div class="prog-wrap" style="width:150px"><div class="prog-bar" style="width:{{ $pct }}%;background:var(--mhs)"></div></div>
  </div>
  <div style="padding:20px">
    <div class="bab-grid">
      @foreach($mahasiswa->progressBabs as $p)
      <div class="bab-item {{ $p->status==='selesai'?'done':($p->status==='proses'?'proses':'') }}"
           style="cursor:pointer" onclick="openUpd({{ $p->id }},'{{ $p->bab }}','{{ $p->status }}','{{ $p->tanggal_update }}','{{ addslashes($p->catatan) }}')">
        <div class="bab-name">{{ $p->bab }}</div>
        <div class="bab-status">{{ $p->status==='selesai'?'✅':($p->status==='proses'?'🔄':'⏳') }}</div>
        <div class="bab-label">{{ ucfirst($p->status) }}</div>
        @if($p->tanggal_update)<div style="font-size:9px;color:var(--muted)">{{ $p->tanggal_update }}</div>@endif
        @if($p->catatan)<div style="font-size:9px;color:var(--muted)">{{ Str::limit($p->catatan,18) }}</div>@endif
      </div>
      @endforeach
    </div>
    <div class="alert alert-info" style="margin-top:20px">💡 Klik tiap BAB untuk update progress. Dosen pembimbing dapat melihat perkembangan ini.</div>
  </div>
</div>

<div class="modal-bg" id="modalUpd">
  <div class="modal-box">
    <h3>📝 Update <span id="updBab"></span></h3>
    <form method="POST" id="updForm">@csrf @method('PUT')
      <div class="form-group"><label>Status</label>
        <select name="status" id="updStat" class="form-control">
          <option value="belum">Belum Dimulai</option>
          <option value="proses">Sedang Dikerjakan</option>
          <option value="selesai">Selesai</option>
        </select>
      </div>
      <div class="form-group"><label>Tanggal Update</label><input type="date" name="tanggal_update" id="updTgl" class="form-control"></div>
      <div class="form-group"><label>Catatan</label><textarea name="catatan" id="updCat" class="form-control" rows="3" placeholder="Misal: Sudah direvisi sesuai arahan dosen"></textarea></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modalUpd')">Batal</button>
        <button type="submit" class="btn btn-primary" style="background:var(--mhs)">Update Progress</button>
      </div>
    </form>
  </div>
</div>
@push('scripts')
<script>
function openUpd(id,bab,status,tgl,cat){
  document.getElementById('updForm').action=`/mahasiswa/progress/${id}`;
  document.getElementById('updBab').textContent=bab;
  document.getElementById('updStat').value=status;
  document.getElementById('updTgl').value=tgl||new Date().toISOString().split('T')[0];
  document.getElementById('updCat').value=cat||'';
  openModal('modalUpd');
}
</script>
@endpush
@endsection