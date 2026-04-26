@extends('layouts.app')
@section('title','Input Nilai')
@section('content')
<div class="page-header"><h1>Input Nilai KP</h1><p>Berikan penilaian pembimbingan dan seminar</p></div>
<div class="card">
  <div class="card-header"><h3>Penilaian Dosen Pembimbing</h3><p>Bobot: Instansi 40% · Pembimbing 30% · Seminar 30%</p></div>
  <table>
    <thead><tr><th>Mahasiswa</th><th>Status</th><th>Nilai Instansi</th><th>Nilai Pembimbing</th><th>Nilai Seminar</th><th>Nilai Akhir</th><th>Aksi</th></tr></thead>
    <tbody>
      @forelse($mahasiswas as $m)
      <tr>
        <td><strong>{{ $m->nama }}</strong><br><code>{{ $m->nim }}</code></td>
        <td><span class="pill pill-{{ $m->status }}">{{ ucfirst($m->status) }}</span></td>
        <td style="text-align:center"><strong>{{ $m->nilai?->nilai_instansi ?? '–' }}</strong></td>
        <td style="text-align:center">
          <strong style="color:var(--dosen-l)">{{ $m->nilai?->nilai_pembimbing ?? '–' }}</strong>
        </td>
        <td style="text-align:center">
          <strong style="color:var(--admin-l)">{{ $m->nilai?->nilai_seminar ?? ($m->seminar?->nilai ?? '–') }}</strong>
        </td>
        <td style="text-align:center">
          @if($m->nilai?->nilai_akhir)
            <strong style="color:var(--dosen-l);font-size:16px">{{ $m->nilai->nilai_akhir }}</strong>
          @else <span style="color:var(--muted)">–</span> @endif
        </td>
        <td>
          <button class="btn btn-success btn-xs" onclick="openNilai({{ $m->id }},'{{ $m->nama }}',{{ $m->nilai?->nilai_pembimbing ?? 'null' }},'{{ $m->nilai?->catatan_pembimbing }}',{{ $m->nilai?->nilai_seminar ?? ($m->seminar?->nilai ?? 'null') }})">Input Nilai</button>
        </td>
      </tr>
      @empty
        <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:24px">Tidak ada mahasiswa bimbingan.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- MODAL NILAI --}}
<div class="modal-bg" id="modalNilai">
  <div class="modal-box">
    <h3>⭐ Input Nilai — <span id="nNama"></span></h3>
    <div class="alert alert-info" style="margin-bottom:18px">Bobot: Instansi 40% + Pembimbing 30% + Seminar 30% = Nilai Akhir</div>
    <form method="POST" id="nilaiForm">@csrf @method('PUT')
      <div class="form-group"><label>Nilai Pembimbingan (0–100) *</label>
        <input type="number" name="nilai_pembimbing" id="nPembimbing" class="form-control" min="0" max="100" step="0.01" required>
      </div>
      <div class="form-group"><label>Catatan Pembimbingan</label>
        <textarea name="catatan_pembimbing" id="nCatatan" class="form-control" rows="3" placeholder="Catatan penilaian..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modalNilai')">Batal</button>
        <button type="submit" class="btn btn-success">Simpan Nilai Pembimbing</button>
      </div>
    </form>
    <div style="border-top:1px solid var(--border);margin-top:20px;padding-top:20px">
      <form method="POST" id="nilaiSeminarForm">@csrf @method('PUT')
        <div class="form-group"><label>Nilai Seminar (0–100)</label>
          <input type="number" name="nilai_seminar" id="nSeminar" class="form-control" min="0" max="100" step="0.01">
        </div>
        <div class="modal-footer" style="padding:0;border:none">
          <button type="submit" class="btn btn-primary btn-sm">Simpan Nilai Seminar</button>
        </div>
      </form>
    </div>
  </div>
</div>
@push('scripts')
<script>
function openNilai(id,nama,pembimbing,catatan,seminar){
  document.getElementById('nNama').textContent=nama;
  document.getElementById('nilaiForm').action=`/dosen/nilai/${id}`;
  document.getElementById('nilaiSeminarForm').action=`/dosen/nilai/${id}/seminar`;
  document.getElementById('nPembimbing').value=pembimbing||'';
  document.getElementById('nCatatan').value=catatan||'';
  document.getElementById('nSeminar').value=seminar||'';
  openModal('modalNilai');
}
</script>
@endpush
@endsection