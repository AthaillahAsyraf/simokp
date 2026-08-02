@extends('layouts.app')
@section('title','Berkas KP')

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
<div class="page-header"><h1>Berkas KP</h1><p>Verifikasi berkas administrasi (Form Pengajuan, Bukti SPP, KRS, Transkrip Nilai) sebelum mahasiswa lanjut ke tahap penempatan instansi.</p></div>

@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">⚠️ {{ session('error') }}</div>@endif

<form method="GET" class="filter-row">
  <input type="text" name="search" class="form-control" placeholder="🔍 Cari nama/NIM..." value="{{ request('search') }}">
  <select name="tahap" class="form-control" style="min-width:260px">
    <option value="">Semua Tahap KP</option>
    @foreach($tahapans as $kodeTahap => $labelTahap)
      <option value="{{ $kodeTahap }}" @selected($tahapDipilih === $kodeTahap)>{{ $labelTahap }}</option>
    @endforeach
  </select>
  <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
  <a href="{{ route('admin.persyaratan.index') }}" class="btn btn-outline btn-sm">Reset</a>
</form>

@php $tahapIkon = [
  \App\Models\Mahasiswa::TAHAP_LENGKAPI_BERKAS => '📋',
  \App\Models\Mahasiswa::TAHAP_MENUNGGU_VERIFIKASI => '🕓',
  \App\Models\Mahasiswa::TAHAP_REVISI_BERKAS => '🔁',
  \App\Models\Mahasiswa::TAHAP_UNGGAH_SURAT_BALASAN => '📮',
  \App\Models\Mahasiswa::TAHAP_MENUNGGU_VERIFIKASI_SURAT_BALASAN => '📨',
  \App\Models\Mahasiswa::TAHAP_MENUNGGU_INSTANSI => '🏢',
  \App\Models\Mahasiswa::TAHAP_AKTIF_KP => '🚀',
  \App\Models\Mahasiswa::TAHAP_SELESAI_KP => '✅',
] @endphp

{{-- Kalau ada tahap yang difilter, cuma tahap itu yang ditampilkan; kalau tidak, semua tahap ditampilkan berurutan. --}}
@foreach($tahapans as $kodeTahap => $labelTahap)
  @continue($tahapDipilih && $tahapDipilih !== $kodeTahap)
  @php($grup = $mahasiswaPerTahap[$kodeTahap])
  <div class="card">
    <div class="card-header"><h3>{{ $tahapIkon[$kodeTahap] ?? '•' }} {{ $labelTahap }} ({{ $grup->count() }})</h3></div>
    <div class="card-body">
      @if($kodeTahap === \App\Models\Mahasiswa::TAHAP_MENUNGGU_VERIFIKASI_SURAT_BALASAN)
        @forelse($grup as $m)
          @php($suratBalasan = $m->syaratAdministrasi)
          <div class="mhs-block"><div class="mhs-block-head"><div><h4>{{ $m->nama }} <span style="color:var(--gray-400);font-weight:500">— {{ $m->nim }}</span></h4><p>Periksa keaslian surat sebelum menyetujui.</p></div><a href="{{ $suratBalasan->urlBerkas('file_surat_balasan') }}" target="_blank" class="btn btn-outline btn-sm">Lihat Berkas</a></div>
            <form method="POST" action="{{ route('admin.persyaratan.verifikasiSuratBalasan', $m) }}" style="display:flex;gap:8px;align-items:end;flex-wrap:wrap">@csrf<div class="form-group" style="margin:0"><label class="form-label">Keputusan</label><select name="keputusan" class="form-control"><option value="disetujui">Setujui</option><option value="ditolak">Tolak</option></select></div><div class="form-group" style="margin:0;min-width:260px;flex:1"><label class="form-label">Catatan penolakan (opsional)</label><input name="catatan" class="form-control" placeholder="Contoh: Surat tidak mencantumkan identitas instansi"></div><button class="btn btn-primary btn-sm">Simpan</button></form>
          </div>
        @empty
          <div class="empty-state">Tidak ada surat balasan yang menunggu verifikasi.</div>
        @endforelse
      @elseif($kodeTahap === \App\Models\Mahasiswa::TAHAP_MENUNGGU_INSTANSI)
        @forelse($grup as $m)
          <div class="mhs-block">
            <div class="mhs-block-head">
              <div>
                <h4>{{ $m->nama }} <span style="color:var(--gray-400);font-weight:500">— {{ $m->nim }}</span></h4>
                @if($m->instansi)
                  <p>Instansi: {{ $m->instansi->nama }} — Pembimbing Lapangan: {{ $m->instansi->kontak_person ?? '-' }} ({{ $m->instansi->user?->email ?? '-' }})</p>
                @else
                  <p style="color:var(--red-600)">Belum mendaftarkan instansi & pembimbing lapangan.</p>
                @endif
              </div>
            </div>

            @if($m->instansi)
              @if($m->instansi->user?->wajib_ganti_password)
                <form method="POST" action="{{ route('admin.instansi.kirimUndangan', $m->instansi) }}" style="display:inline-block;margin-bottom:8px">
                  @csrf
                  <button class="btn btn-outline btn-sm">📨 {{ $m->instansi->user->activation_token ? 'Kirim Ulang Undangan' : 'Kirim Undangan' }} ke Pembimbing Lapangan</button>
                </form>
              @else
                <span class="badge badge-selesai" style="margin-bottom:8px;display:inline-block">✅ Akun Pembimbing Lapangan sudah aktif</span>
              @endif
            @endif

            <form method="POST" action="{{ route('admin.mahasiswa.tetapkanDosen', $m) }}" style="display:flex;gap:8px;align-items:end;flex-wrap:wrap">
              @csrf @method('PATCH')
              <div class="form-group" style="margin:0;min-width:260px;flex:1">
                <label class="form-label">Dosen Pembimbing</label>
                <select name="dosen_id" class="form-control" required>
                  <option value="">-- Pilih Dosen --</option>
                  @foreach($dosens as $d)
                    <option value="{{ $d->id }}" @selected($m->dosen_id === $d->id)>{{ $d->nama }}</option>
                  @endforeach
                </select>
              </div>
              <button class="btn btn-primary btn-sm">Tetapkan Dosen</button>
            </form>
          </div>
        @empty
          <div class="empty-state">📭 Tidak ada mahasiswa pada tahap ini.</div>
        @endforelse
      @else
        @forelse($grup as $m)
          @include('admin.persyaratan._row', ['m' => $m, 'bisaVerifikasi' => $kodeTahap === \App\Models\Mahasiswa::TAHAP_MENUNGGU_VERIFIKASI])
        @empty
          <div class="empty-state">📭 Tidak ada mahasiswa pada tahap ini.</div>
        @endforelse
      @endif
    </div>
  </div>
@endforeach

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