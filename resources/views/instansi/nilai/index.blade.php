@extends('layouts.app')
@section('title','Beri Nilai')
@section('content')
<div class="page-header"><h1>Penilaian Mahasiswa KP</h1><p>Berikan nilai dan penilaian untuk mahasiswa yang KP di instansi Anda</p></div>
<div class="alert alert-info">ℹ️ Nilai instansi memiliki bobot <strong>40%</strong> dari nilai akhir KP mahasiswa.</div>
<div class="card">
  <div class="card-header"><h3>Form Penilaian Instansi</h3></div>
  <table>
    <thead><tr><th>Mahasiswa</th><th>Status KP</th><th>Nilai Instansi</th><th>Catatan</th><th>Aksi</th></tr></thead>
    <tbody>
      @forelse($mahasiswas as $m)
      <tr>
        <td><strong>{{ $m->nama }}</strong><br><code>{{ $m->nim }}</code></td>
        <td><span class="pill pill-{{ $m->status }}">{{ ucfirst($m->status) }}</span></td>
        <td style="text-align:center">
          @if($m->nilai?->nilai_instansi)
            <strong style="color:var(--inst-l);font-size:16px">{{ $m->nilai->nilai_instansi }}</strong>
          @else <span style="color:var(--muted)">Belum dinilai</span> @endif
        </td>
        <td style="font-size:12px;color:var(--muted);max-width:180px">{{ Str::limit($m->nilai?->catatan_instansi, 50) ?? '–' }}</td>
        <td><button class="btn btn-warning btn-sm" onclick="openNilai({{ $m->id }},'{{ $m->nama }}',{{ $m->nilai?->nilai_instansi ?? 'null' }},'{{ addslashes($m->nilai?->catatan_instansi) }}')">Beri Nilai</button></td>
      </tr>
      @empty
        <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:24px">Belum ada mahasiswa KP.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="modal-bg" id="modalNilai">
  <div class="modal-box">
    <h3>⭐ Nilai Instansi — <span id="nNama"></span></h3>
    <form method="POST" id="nilaiForm">@csrf @method('PUT')
      <div class="form-group"><label>Nilai (0–100) *</label>
        <input type="number" name="nilai_instansi" id="nNilai" class="form-control" min="0" max="100" step="0.01" required>
      </div>
      <div class="form-group"><label>Catatan Penilaian</label>
        <textarea name="catatan_instansi" id="nCat" class="form-control" rows="4" placeholder="Kedisiplinan, kemampuan teknis, sikap kerja, dll..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modalNilai')">Batal</button>
        <button type="submit" class="btn btn-warning">Simpan Nilai</button>
      </div>
    </form>
  </div>
</div>
@push('scripts')
<script>
function openNilai(id,nama,nilai,catatan){
  document.getElementById('nNama').textContent=nama;
  document.getElementById('nilaiForm').action=`/instansi/nilai/${id}`;
  document.getElementById('nNilai').value=nilai||'';
  document.getElementById('nCat').value=catatan||'';
  openModal('modalNilai');
}
</script>
@endpush
@endsection