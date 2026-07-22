{{--
    Partial: stepper tahapan KP sesuai Prosedur KP jurusan.
    Wajib passing variable $mahasiswa (with syaratAdministrasi loaded).
--}}
@php
    $urutanTahap = \App\Models\Mahasiswa::URUTAN_TAHAP[$mahasiswa->tahap] ?? 0;
    $steps = [
        ['key' => 0, 'label' => 'Lengkapi Berkas', 'icon' => '📄'],
        ['key' => 1, 'label' => 'Verifikasi Admin', 'icon' => '🔍'],
        ['key' => 2, 'label' => 'Surat Balasan', 'icon' => '✉️'],
        ['key' => 3, 'label' => 'Instansi & Dosen', 'icon' => '🏢'],
        ['key' => 4, 'label' => 'Kesediaan Dosen', 'icon' => '📋'],
        ['key' => 5, 'label' => 'Aktif KP', 'icon' => '🚀'],
    ];
@endphp
<div class="card">
  <div class="card-header">
    <div>
      <h3>Tahapan Kerja Praktik</h3>
      <p>Status Anda saat ini: <strong>{{ $mahasiswa->tahapLabel() }}</strong></p>
    </div>
    @if($mahasiswa->tahap === \App\Models\Mahasiswa::TAHAP_REVISI_BERKAS)
      <span class="badge badge-rejected">Perlu Revisi</span>
    @elseif($mahasiswa->tahap === \App\Models\Mahasiswa::TAHAP_AKTIF_KP)
      <span class="badge badge-selesai">Aktif</span>
    @else
      <span class="badge badge-proses">Berjalan</span>
    @endif
  </div>
  <div class="card-body">
    <div style="display:flex;align-items:flex-start;gap:6px">
      @foreach($steps as $i => $step)
        @php
          $done = $urutanTahap > $step['key'];
          $current = $urutanTahap === $step['key'];
        @endphp
        <div style="flex:1;text-align:center">
          <div style="width:34px;height:34px;border-radius:50%;margin:0 auto 6px;display:flex;align-items:center;justify-content:center;font-size:15px;
            background:{{ $done ? 'var(--green-100)' : ($current ? 'var(--amber-100)' : 'var(--gray-100)') }};
            border:2px solid {{ $done ? 'var(--green-500)' : ($current ? 'var(--amber-500)' : 'var(--gray-200)') }};">
            {{ $done ? '✓' : $step['icon'] }}
          </div>
          <div style="font-size:11px;font-weight:600;color:{{ $current ? 'var(--gray-800)' : 'var(--gray-500)' }}">{{ $step['label'] }}</div>
        </div>
        @if(!$loop->last)
          <div style="flex:0 0 20px;height:2px;background:{{ $urutanTahap > $step['key'] ? 'var(--green-500)' : 'var(--gray-200)' }};margin-top:16px"></div>
        @endif
      @endforeach
    </div>

    <div class="alert alert-info" style="margin-top:18px">
      @switch($mahasiswa->tahap)
        @case(\App\Models\Mahasiswa::TAHAP_LENGKAPI_BERKAS)
          💡 Upload 4 berkas persyaratan (Form Pengajuan, Bukti SPP, KRS, Transkrip Nilai) di halaman <a href="{{ route('mahasiswa.persyaratan.index') }}"><strong>Persyaratan KP</strong></a> untuk memulai proses.
          @break
        @case(\App\Models\Mahasiswa::TAHAP_MENUNGGU_VERIFIKASI)
          🕓 Berkas Anda sedang diverifikasi oleh admin jurusan. Mohon tunggu.
          @break
        @case(\App\Models\Mahasiswa::TAHAP_REVISI_BERKAS)
          🔁 Ada berkas yang perlu diperbaiki. Cek catatan admin dan upload ulang di halaman <a href="{{ route('mahasiswa.persyaratan.index') }}"><strong>Persyaratan KP</strong></a>.
          @break
        @case(\App\Models\Mahasiswa::TAHAP_UNGGAH_SURAT_BALASAN)
          ✉️ Berkas disetujui! Buat surat permohonan KP melalui <strong>SAIDATA</strong>, kirimkan ke instansi tujuan, lalu unggah <strong>surat balasan</strong> dari instansi tersebut di halaman <a href="{{ route('mahasiswa.surat-balasan.index') }}"><strong>Surat Balasan Instansi</strong></a>.
          @break
       @case(\App\Models\Mahasiswa::TAHAP_MENUNGGU_INSTANSI)
          @if($mahasiswa->instansi_id)
            📨 Instansi & Pembimbing Lapangan sudah didaftarkan. Menunggu admin jurusan menentukan Dosen Pembimbing Anda.
          @else
            🏢 Surat balasan diterima! Sekarang daftarkan instansi tempat KP dan Pembimbing Lapangan Anda di halaman <a href="{{ route('mahasiswa.instansi.index') }}"><strong>Daftarkan Instansi</strong></a>.
          @endif
          @break
        @case(\App\Models\Mahasiswa::TAHAP_MENUNGGU_KESEDIAAN_PEMBIMBING)
          📋 Admin telah menetapkan dosen pembimbing. Silakan buka <a href="{{ route('mahasiswa.form-kesediaan-pembimbing.index') }}"><strong>Form Kesediaan Pembimbing</strong></a>, lalu teruskan kepada dosen untuk disetujui.
          @break
        @case(\App\Models\Mahasiswa::TAHAP_AKTIF_KP)
          🚀 Anda resmi memulai KP di <strong>{{ $mahasiswa->instansi->nama ?? '-' }}</strong> dengan pembimbing <strong>{{ $mahasiswa->dosen->nama ?? '-' }}</strong>. Absensi, laporan, dan seminar sudah bisa diakses.
          @break
      @endswitch
    </div>
  </div>
</div>
