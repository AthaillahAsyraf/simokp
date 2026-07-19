@php
  $syarat = $m->syaratAdministrasi;
  $statusBadge = [
      'belum_lengkap'       => 'badge-belum',
      'menunggu_verifikasi' => 'badge-proses',
      'revisi'              => 'badge-rejected',
      'disetujui'           => 'badge-selesai',
  ][$syarat?->status ?? 'belum_lengkap'] ?? 'badge-belum';
@endphp
<div class="mhs-block">
  <div class="mhs-block-head">
    <div>
      <h4>{{ $m->nama }} <span style="color:var(--gray-400);font-weight:500">— {{ $m->nim }}</span>
        <span class="badge {{ $statusBadge }}" style="margin-left:6px">{{ $syarat?->status_label ?? 'Belum Lengkap' }}</span>
      </h4>
      <p>Angkatan {{ $m->angkatan }}</p>
    </div>
    @if($bisaVerifikasi)
      <button type="button" class="btn btn-primary btn-sm" onclick="bukaVerifikasi({{ $m->id }}, '{{ $m->nama }}')">🔍 Verifikasi</button>
    @endif
  </div>

  <div>
    @foreach(\App\Models\SyaratAdministrasi::BERKAS as $field => $label)
      @if($syarat && $syarat->$field)
        <a href="{{ $syarat->urlBerkas($field) }}" target="_blank" class="berkas-mini ada">📄 {{ $label }}</a>
      @else
        <span class="berkas-mini kosong">⬜ {{ $label }}</span>
      @endif
    @endforeach
  </div>

  @if($syarat?->status === 'revisi' && $syarat->catatan)
    <p style="font-size:12px;color:var(--red-600);margin-top:10px">📌 Catatan: {{ $syarat->catatan }}</p>
  @endif
</div>