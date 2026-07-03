@extends('layouts.app')
@section('title','Nilai Pembimbing Lapangan')

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
.pic-box{background:var(--blue-50);border:1px solid var(--blue-100);border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:var(--blue-700)}
.belum-lapangan{color:var(--amber-600);font-size:12px;font-style:italic}
</style>
@endpush

@section('content')
<div class="page-header"><h1>Nilai Pembimbing Lapangan</h1><p>Penilaian performa kerja mahasiswa KP selama di instansi Anda, diisi atas nama Pembimbing Lapangan masing-masing mahasiswa</p></div>

@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">⚠️ {{ session('error') }}</div>@endif

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Mahasiswa</th><th>Pembimbing Lapangan</th><th>Dosen Pembimbing</th><th>Nilai Lapangan</th>
          <th>Nilai Pembimbing</th><th>Nilai Akhir</th><th>Status</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($mahasiswas as $m)
        @php $n = $m->nilai; @endphp
        <tr>
          <td><strong>{{ $m->nama }}</strong><br><code class="text-sm">{{ $m->nim }}</code></td>
          <td class="text-sm">
            @if($m->pembimbing_lapangan_nama)
              {{ $m->pembimbing_lapangan_nama }}
              @if($m->pembimbing_lapangan_jabatan)<br><span class="text-muted">{{ $m->pembimbing_lapangan_jabatan }}</span>@endif
            @else
              <span class="belum-lapangan">Belum diisi admin</span>
            @endif
          </td>
          <td class="text-sm text-muted">{{ $m->dosen?->nama ?? '–' }}</td>
          <td class="text-sm">{{ $n?->nilai_lapangan ?? '–' }}</td>
          <td class="text-sm">{{ $n?->nilai_seminar ?? '–' }}</td>
          <td><strong>{{ $n?->nilai_akhir ?? '–' }}</strong>
            @if($n?->predikat)<span class="predikat predikat-{{ $n->predikat }}" style="margin-left:6px">{{ $n->predikat }}</span>@endif
          </td>
          <td>
            @php $status = $n?->status_kelulusan ?? 'Belum Lengkap'; @endphp
            <span class="{{ $status === 'Lulus' ? 'status-lulus' : ($status === 'Tidak Lulus' ? 'status-tidak-lulus' : 'status-belum') }}">{{ $status }}</span>
          </td>
          <td>
            @if($m->pembimbing_lapangan_nama)
              <button class="btn btn-outline btn-xs" onclick="openNilai({{ $m->id }}, {{ json_encode($m->nama) }}, {{ json_encode($m->pembimbing_lapangan_nama) }}, {{ json_encode($n) }})">Input Nilai</button>
            @else
              <button class="btn btn-outline btn-xs" disabled title="Minta admin lengkapi data Pembimbing Lapangan dulu">Input Nilai</button>
            @endif
            @if($n?->nilai_lapangan !== null)
              <a class="btn btn-outline btn-xs" href="{{ route('instansi.nilai.cetak', $m->id) }}" target="_blank">Cetak Nilai</a>
            @endif
          </td>
        </tr>
        @empty
          <tr><td colspan="8" style="text-align:center;padding:28px;color:var(--gray-400)">Belum ada mahasiswa KP di instansi Anda.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- MODAL INPUT NILAI — 8 komponen sama rata sesuai Form Nilai Pembimbing Lapangan --}}
<div class="modal-bg" id="modalNilai">
  <div class="modal-box" style="width:560px">
    <div class="modal-title" id="nTitle">📝 Nilai Pembimbing Lapangan</div>
    @if($errors->instansi->any())
    <div class="err-box"><ul>@foreach($errors->instansi->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    <div class="pic-box" id="nPicBox">👤 Nilai ini diisi atas nama Pembimbing Lapangan: <strong id="nPicName"></strong></div>
    <form method="POST" id="nilaiForm" oninput="hitungTotalLapangan()">
      @csrf @method('PUT')

      <p class="text-sm" style="font-weight:700;color:var(--gray-700);margin-bottom:6px">A. Kedisiplinan</p>
      <div class="form-group"><label class="form-label">1. Jumlah Kehadiran *</label><input type="number" name="lapangan_kehadiran" id="lA1" class="form-control" min="0" max="100" step="0.01" required></div>
      <div class="form-group"><label class="form-label">2. Taat Tata Tertib *</label><input type="number" name="lapangan_tata_tertib" id="lA2" class="form-control" min="0" max="100" step="0.01" required></div>

      <p class="text-sm" style="font-weight:700;color:var(--gray-700);margin:14px 0 6px">B. Kerjasama</p>
      <div class="form-group"><label class="form-label">1. Dengan Anggota Kelompok *</label><input type="number" name="lapangan_kerjasama_anggota" id="lB1" class="form-control" min="0" max="100" step="0.01" required></div>
      <div class="form-group"><label class="form-label">2. Dengan Kelompok Lain *</label><input type="number" name="lapangan_kerjasama_kelompok_lain" id="lB2" class="form-control" min="0" max="100" step="0.01" required></div>
      <div class="form-group"><label class="form-label">3. Pembimbing *</label><input type="number" name="lapangan_kerjasama_pembimbing" id="lB3" class="form-control" min="0" max="100" step="0.01" required></div>

      <p class="text-sm" style="font-weight:700;color:var(--gray-700);margin:14px 0 6px">C. Prestasi kerja</p>
      <div class="form-group"><label class="form-label">1. Inovasi *</label><input type="number" name="lapangan_inovasi" id="lC1" class="form-control" min="0" max="100" step="0.01" required></div>
      <div class="form-group"><label class="form-label">2. Kemampuan Menyelesaikan Tugas *</label><input type="number" name="lapangan_tugas" id="lC2" class="form-control" min="0" max="100" step="0.01" required></div>
      <div class="form-group"><label class="form-label">3. Keseriusan *</label><input type="number" name="lapangan_keseriusan" id="lC3" class="form-control" min="0" max="100" step="0.01" required></div>

      <div style="display:flex;justify-content:space-between;align-items:center;background:var(--gray-50);border-radius:8px;padding:10px 14px;margin:10px 0">
        <span class="text-sm text-muted">Rata-rata Nilai (otomatis)</span>
        <strong id="lTotalOut" style="font-size:18px">–</strong>
      </div>

      <div class="form-group">
        <label class="form-label">Catatan</label>
        <textarea name="catatan_lapangan" id="nCatatan" class="form-control" rows="2" placeholder="Masukan/feedback untuk mahasiswa..."></textarea>
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
const KOMPONEN_LAPANGAN_IDS = ['lA1','lA2','lB1','lB2','lB3','lC1','lC2','lC3'];

function openNilai(id, nama, namaPic, n) {
  n = n || {};
  document.getElementById('nilaiForm').action = `{{ url('instansi-area/nilai') }}/${id}`;
  document.getElementById('nTitle').textContent = '📝 Nilai Pembimbing Lapangan — ' + nama;
  document.getElementById('nPicName').textContent = namaPic || '–';
  document.getElementById('lA1').value = n.lapangan_kehadiran ?? '';
  document.getElementById('lA2').value = n.lapangan_tata_tertib ?? '';
  document.getElementById('lB1').value = n.lapangan_kerjasama_anggota ?? '';
  document.getElementById('lB2').value = n.lapangan_kerjasama_kelompok_lain ?? '';
  document.getElementById('lB3').value = n.lapangan_kerjasama_pembimbing ?? '';
  document.getElementById('lC1').value = n.lapangan_inovasi ?? '';
  document.getElementById('lC2').value = n.lapangan_tugas ?? '';
  document.getElementById('lC3').value = n.lapangan_keseriusan ?? '';
  document.getElementById('nCatatan').value = n.catatan_lapangan ?? '';
  hitungTotalLapangan();
  openModal('modalNilai');
}

function hitungTotalLapangan() {
  let total = 0, lengkap = true;
  KOMPONEN_LAPANGAN_IDS.forEach(id => {
    const v = document.getElementById(id).value;
    if (v === '') { lengkap = false; return; }
    total += parseFloat(v);
  });
  document.getElementById('lTotalOut').textContent = lengkap ? (total / KOMPONEN_LAPANGAN_IDS.length).toFixed(2) : '–';
}
</script>
@endpush
@endsection