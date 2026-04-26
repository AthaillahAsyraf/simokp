@extends('layouts.app')
@section('title','Pengajuan Surat')
@section('content')
<div class="page-header"><h1>Pengajuan Surat & Dokumen</h1><p>Ajukan surat administrasi KP Anda ke bagian akademik</p></div>

<div class="card" style="margin-bottom:20px">
  <div class="card-header"><h3>Ajukan Surat Baru</h3></div>
  <div class="card-body">
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px">
      @foreach([
        ['permohonan','📝','Surat Permohonan KP','Untuk pengajuan izin KP ke instansi tujuan'],
        ['keterangan','📄','Surat Keterangan KP','Keterangan telah selesai KP dari jurusan'],
        ['pengantar','📨','Surat Pengantar','Surat pengantar ke instansi / perusahaan'],
      ] as [$jenis,$icon,$judul,$desc])
      <div style="border:1.5px solid var(--border);border-radius:12px;padding:18px;cursor:pointer;transition:all .2s"
           onmouseover="this.style.borderColor='var(--mhs)';this.style.background='rgba(236,72,153,.05)'"
           onmouseout="this.style.borderColor='var(--border)';this.style.background=''"
           onclick="openSurat('{{ $jenis }}','{{ $judul }}')">
        <div style="font-size:28px;margin-bottom:8px">{{ $icon }}</div>
        <div style="font-weight:700;font-size:14px;margin-bottom:4px">{{ $judul }}</div>
        <div style="font-size:12px;color:var(--muted)">{{ $desc }}</div>
      </div>
      @endforeach
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header"><h3>Riwayat Pengajuan</h3><p>{{ $surats->count() }} pengajuan</p></div>
  @if($surats->count() > 0)
  <table>
    <thead><tr><th>Jenis Surat</th><th>Keterangan</th><th>Tanggal Ajuan</th><th>Status</th><th>Catatan Admin</th></tr></thead>
    <tbody>
      @foreach($surats as $s)
      <tr>
        <td>
          <span class="pill {{ $s->jenis==='permohonan'?'pill-seminar':($s->jenis==='keterangan'?'pill-disetujui':'pill-proses') }}">
            {{ ucfirst($s->jenis) }}
          </span>
        </td>
        <td style="font-size:12px;color:var(--muted)">{{ $s->keterangan ? Str::limit($s->keterangan,50) : '–' }}</td>
        <td style="font-size:12px;color:var(--muted)">{{ $s->created_at->format('d/m/Y H:i') }}</td>
        <td><span class="pill pill-{{ $s->status }}">{{ ucfirst($s->status) }}</span></td>
        <td style="font-size:12px;color:var(--muted)">{{ $s->catatan_admin ?? '–' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
    <div style="text-align:center;padding:32px;color:var(--muted)">
      <div style="font-size:40px;margin-bottom:12px">📄</div>
      <p>Belum ada pengajuan surat.</p>
    </div>
  @endif
</div>

<div class="modal-bg" id="modalSurat">
  <div class="modal-box">
    <h3>📄 Ajukan <span id="suratJudul"></span></h3>
    <form method="POST" action="{{ route('mahasiswa.surat.store') }}">@csrf
      <input type="hidden" name="jenis" id="suratJenis">
      <div class="alert alert-info" style="margin-bottom:16px">Pengajuan akan diproses oleh admin akademik Ilmu Komputer Unila.</div>
      <div class="form-group"><label>Keterangan Tambahan (opsional)</label>
        <textarea name="keterangan" class="form-control" rows="4" placeholder="Tuliskan keperluan atau keterangan tambahan jika ada..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modalSurat')">Batal</button>
        <button type="submit" class="btn btn-primary" style="background:var(--mhs)">Kirim Permohonan</button>
      </div>
    </form>
  </div>
</div>
@push('scripts')
<script>
function openSurat(jenis,judul){
  document.getElementById('suratJenis').value=jenis;
  document.getElementById('suratJudul').textContent=judul;
  openModal('modalSurat');
}
</script>
@endpush
@endsection