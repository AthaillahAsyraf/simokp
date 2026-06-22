@extends('layouts.app')
@section('title','Pesan Baru ke Dosen')
@section('content')

<div class="page-header">
  <h1>📨 Pesan Baru ke Dosen</h1>
  <p>Kirim laporan monitoring atau pengaduan terkait mahasiswa KP Anda.</p>
</div>

<div class="card" style="max-width:640px">
  <div class="card-body">
    <form action="{{ route('instansi.chat.store') }}" method="POST">
      @csrf

      {{-- Pilih Dosen --}}
      <div class="form-group">
        <label class="form-label">Kepada Dosen <span style="color:var(--red-500)">*</span></label>
        <select name="dosen_id" required class="form-control">
          <option value="">-- Pilih Dosen --</option>
          @foreach($dosens as $d)
            <option value="{{ $d->id }}" {{ old('dosen_id')==$d->id ? 'selected' : '' }}>{{ $d->nama }}</option>
          @endforeach
        </select>
        @error('dosen_id')<div style="color:var(--red-500);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
      </div>

      {{-- Pilih Mahasiswa --}}
      <div class="form-group">
        <label class="form-label">Terkait Mahasiswa <span style="color:var(--gray-400);font-weight:400">(opsional)</span></label>
        <select name="mahasiswa_id" class="form-control">
          <option value="">-- Tidak spesifik --</option>
          @foreach($mahasiswas as $m)
            <option value="{{ $m->id }}" {{ old('mahasiswa_id')==$m->id ? 'selected' : '' }}>
              {{ $m->nama }} ({{ $m->nim }}) — Dosen: {{ $m->dosen?->nama ?? '–' }}
            </option>
          @endforeach
        </select>
      </div>

      {{-- Tipe --}}
      <div class="form-group">
        <label class="form-label">Tipe Pesan <span style="color:var(--red-500)">*</span></label>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:4px">

          <label style="flex:1;min-width:140px;border:2px solid {{ old('tipe','monitoring')==='monitoring' ? 'var(--blue-500)' : 'var(--gray-300)' }};border-radius:10px;padding:12px;cursor:pointer" id="label-monitoring">
            <input type="radio" name="tipe" value="monitoring" {{ old('tipe','monitoring')==='monitoring' ? 'checked' : '' }}
              onchange="updateTipeLabel()" style="display:none">
            <div style="font-size:20px;margin-bottom:4px">📋</div>
            <div style="font-size:13px;font-weight:700">Monitoring</div>
            <div style="font-size:11px;color:var(--gray-400)">Laporan kegiatan & perkembangan mahasiswa</div>
          </label>

          <label style="flex:1;min-width:140px;border:2px solid {{ old('tipe')==='pengaduan' ? 'var(--blue-500)' : 'var(--gray-300)' }};border-radius:10px;padding:12px;cursor:pointer" id="label-pengaduan">
            <input type="radio" name="tipe" value="pengaduan" {{ old('tipe')==='pengaduan' ? 'checked' : '' }}
              onchange="updateTipeLabel()" style="display:none">
            <div style="font-size:20px;margin-bottom:4px">⚠️</div>
            <div style="font-size:13px;font-weight:700">Pengaduan</div>
            <div style="font-size:11px;color:var(--gray-400)">Mahasiswa bermasalah atau ada kendala</div>
          </label>

          <label style="flex:1;min-width:140px;border:2px solid {{ old('tipe')==='umum' ? 'var(--blue-500)' : 'var(--gray-300)' }};border-radius:10px;padding:12px;cursor:pointer" id="label-umum">
            <input type="radio" name="tipe" value="umum" {{ old('tipe')==='umum' ? 'checked' : '' }}
              onchange="updateTipeLabel()" style="display:none">
            <div style="font-size:20px;margin-bottom:4px">💬</div>
            <div style="font-size:13px;font-weight:700">Umum</div>
            <div style="font-size:11px;color:var(--gray-400)">Pertanyaan atau info lainnya</div>
          </label>

        </div>
        @error('tipe')<div style="color:var(--red-500);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
      </div>

      {{-- Subjek --}}
      <div class="form-group">
        <label class="form-label">Subjek <span style="color:var(--red-500)">*</span></label>
        <input type="text" name="subjek" value="{{ old('subjek') }}" required
          placeholder="cth: Laporan Minggu ke-3 Mahasiswa Ahmad"
          class="form-control">
        @error('subjek')<div style="color:var(--red-500);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
      </div>

      {{-- Pesan --}}
      <div class="form-group">
        <label class="form-label">Isi Pesan <span style="color:var(--red-500)">*</span></label>
        <textarea name="pesan" rows="5" required class="form-control"
          placeholder="Tuliskan laporan atau pengaduan Anda di sini...">{{ old('pesan') }}</textarea>
        @error('pesan')<div style="color:var(--red-500);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
      </div>

      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary">Kirim Pesan</button>
        <a href="{{ route('instansi.chat.index') }}" class="btn btn-outline">Batal</a>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function updateTipeLabel() {
  const tipes = ['monitoring', 'pengaduan', 'umum'];
  tipes.forEach(t => {
    const radio = document.querySelector('input[name="tipe"][value="' + t + '"]');
    const label = document.getElementById('label-' + t);
    if (radio && label) {
      label.style.borderColor = radio.checked ? 'var(--blue-500)' : 'var(--gray-300)';
    }
  });
}
// init on load
updateTipeLabel();
</script>
@endpush
@endsection