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
<div class="page-header"><h1>Nilai Mahasiswa</h1><p>Input nilai kinerja mahasiswa KP di instansi Anda</p></div>

@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Mahasiswa</th><th>Dosen Pembimbing</th><th>Nilai Instansi</th>
          <th>Nilai Pembimbing</th><th>Nilai Seminar</th><th>Nilai Akhir</th><th>Status</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($mahasiswas as $m)
        @php $n = $m->nilai; @endphp
        <tr>
          <td><strong>{{ $m->nama }}</strong><br><code class="text-sm">{{ $m->nim }}</code></td>
          <td class="text-sm text-muted">{{ $m->dosen?->nama ?? '–' }}</td>
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
            <button class="btn btn-outline btn-xs" onclick="openNilai({{ $m->id }}, {{ json_encode($m->nama) }}, {{ json_encode($n?->nilai_instansi) }}, {{ json_encode($n?->catatan_instansi) }})">Input Nilai</button>
          </td>
        </tr>
        @empty
          <tr><td colspan="8" style="text-align:center;padding:28px;color:var(--gray-400)">Belum ada mahasiswa KP di instansi Anda.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- MODAL INPUT NILAI --}}
<div class="modal-bg" id="modalNilai">
  <div class="modal-box">
    <div class="modal-title" id="nTitle">📝 Nilai Instansi</div>
    @if($errors->instansi->any())
    <div class="err-box"><ul>@foreach($errors->instansi->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    <form method="POST" id="nilaiForm">
      @csrf @method('PUT')
      <div class="form-group">
        <label class="form-label">Nilai (0-100) *</label>
        <input type="number" name="nilai_instansi" id="nNilai" class="form-control" min="0" max="100" step="0.01" required>
      </div>
      <div class="form-group">
        <label class="form-label">Catatan</label>
        <textarea name="catatan_instansi" id="nCatatan" class="form-control" rows="3" placeholder="Masukan/feedback untuk mahasiswa..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalNilai')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function openNilai(id, nama, nilai, catatan) {
  document.getElementById('nilaiForm').action = `{{ url('instansi-area/nilai') }}/${id}`;
  document.getElementById('nTitle').textContent = '📝 Nilai Instansi — ' + nama;
  document.getElementById('nNilai').value   = nilai || '';
  document.getElementById('nCatatan').value = catatan || '';
  openModal('modalNilai');
}
</script>
@endpush
@endsection