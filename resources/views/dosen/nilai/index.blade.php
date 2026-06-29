@extends('layouts.app')
@section('title','Nilai Mahasiswa')

@push('styles')
<style>
.predikat{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;font-weight:700;font-size:13px}
.predikat-A{background:var(--green-100);color:var(--green-700)}
.predikat-B{background:var(--blue-100);color:var(--blue-700)}
.predikat-C{background:var(--amber-100);color:var(--amber-600)}
.predikat-D, .predikat-E{background:var(--red-100);color:var(--red-600)}
.status-lulus{color:var(--green-600);font-weight:600}
.status-tidak-lulus{color:var(--red-600);font-weight:600}
.status-belum{color:var(--gray-400)}
.err-box{background:var(--red-50);border:1px solid var(--red-100);border-radius:6px;padding:10px 14px;margin-bottom:14px}
.err-box ul{margin:4px 0 0 18px;color:var(--red-600);font-size:.82rem}
</style>
@endpush

@section('content')
<div class="page-header"><h1>Nilai Mahasiswa</h1><p>Input nilai pembimbingan & nilai seminar mahasiswa bimbingan Anda</p></div>

@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">⚠️ {{ session('error') }}</div>@endif

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Mahasiswa</th><th>Nilai Instansi</th><th>Nilai Pembimbing</th><th>Nilai Seminar</th>
          <th>Nilai Akhir</th><th>Status</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($mahasiswas as $m)
        @php $n = $m->nilai; $s = $m->seminar; @endphp
        <tr>
          <td><strong>{{ $m->nama }}</strong><br><code class="text-sm">{{ $m->nim }}</code><br><span class="text-sm text-muted">{{ $m->instansi?->nama }}</span></td>
          <td class="text-sm">{{ $n?->nilai_instansi ?? '–' }}</td>
          <td class="text-sm">{{ $n?->nilai_pembimbing ?? '–' }}</td>
          <td class="text-sm">{{ $n?->nilai_seminar ?? '–' }}</td>
          <td><strong>{{ $n?->nilai_akhir ?? '–' }}</strong>
            @if($n?->predikat)<span class="predikat predikat-{{ $n->predikat }}" style="margin-left:6px">{{ $n->predikat }}</span>@endif
          </td>
          <td>
            @php $status = $n?->status_kelulusan ?? 'Belum Lengkap'; @endphp
            <span class="{{ $status === 'Lulus' ? 'status-lulus' : ($status === 'Tidak Lulus' ? 'status-tidak-lulus' : 'status-belum') }}">{{ $status }}</span>
          </td>
          <td>
            <div style="display:flex;gap:4px;flex-wrap:wrap">
              <button class="btn btn-outline btn-xs" onclick="openPembimbing({{ $m->id }}, {{ json_encode($m->nama) }}, {{ json_encode($n?->nilai_pembimbing) }}, {{ json_encode($n?->catatan_pembimbing) }})">Nilai Pembimbing</button>
              @if($s && ($s->isTerjadwal() || $s->isSelesai()))
                <button class="btn btn-outline btn-xs" onclick="openSeminar({{ $m->id }}, {{ json_encode($m->nama) }}, {{ json_encode($n?->nilai_seminar) }})">Nilai Seminar</button>
              @else
                <button class="btn btn-outline btn-xs" disabled title="Seminar belum dijadwalkan">Nilai Seminar</button>
              @endif
            </div>
          </td>
        </tr>
        @empty
          <tr><td colspan="7" style="text-align:center;padding:28px;color:var(--gray-400)">Belum ada mahasiswa bimbingan.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- MODAL NILAI PEMBIMBING --}}
<div class="modal-bg" id="modalPembimbing">
  <div class="modal-box">
    <div class="modal-title" id="pTitle">📝 Nilai Pembimbingan</div>
    @if($errors->pembimbing->any())
    <div class="err-box"><ul>@foreach($errors->pembimbing->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    <form method="POST" id="pembimbingForm">
      @csrf @method('PUT')
      <div class="form-group">
        <label class="form-label">Nilai (0-100) *</label>
        <input type="number" name="nilai_pembimbing" id="pNilai" class="form-control" min="0" max="100" step="0.01" required>
      </div>
      <div class="form-group">
        <label class="form-label">Catatan</label>
        <textarea name="catatan_pembimbing" id="pCatatan" class="form-control" rows="3" placeholder="Masukan/feedback untuk mahasiswa..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalPembimbing')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL NILAI SEMINAR --}}
<div class="modal-bg" id="modalSeminar">
  <div class="modal-box">
    <div class="modal-title" id="sTitle">🎤 Nilai Seminar</div>
    @if($errors->seminar->any())
    <div class="err-box"><ul>@foreach($errors->seminar->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    <form method="POST" id="seminarForm">
      @csrf @method('PUT')
      <div class="form-group">
        <label class="form-label">Nilai Seminar (0-100) *</label>
        <input type="number" name="nilai_seminar" id="sNilai" class="form-control" min="0" max="100" step="0.01" required>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalSeminar')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function openPembimbing(id, nama, nilai, catatan) {
  document.getElementById('pembimbingForm').action = `{{ url('dosen-area/nilai') }}/${id}`;
  document.getElementById('pTitle').textContent = '📝 Nilai Pembimbingan — ' + nama;
  document.getElementById('pNilai').value   = nilai || '';
  document.getElementById('pCatatan').value = catatan || '';
  openModal('modalPembimbing');
}
function openSeminar(id, nama, nilai) {
  document.getElementById('seminarForm').action = `{{ url('dosen-area/nilai') }}/${id}/seminar`;
  document.getElementById('sTitle').textContent = '🎤 Nilai Seminar — ' + nama;
  document.getElementById('sNilai').value = nilai || '';
  openModal('modalSeminar');
}
</script>
@endpush
@endsection