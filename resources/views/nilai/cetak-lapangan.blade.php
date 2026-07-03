<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Form Nilai Pembimbing Lapangan — {{ $mahasiswa->nama }}</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Times New Roman',Times,serif;color:#111;background:#e2e8f0;padding:24px}
.toolbar{max-width:760px;margin:0 auto 16px;display:flex;justify-content:flex-end;gap:8px}
.toolbar button,.toolbar a{font-family:'Inter',sans-serif;font-size:13px;font-weight:600;padding:8px 16px;border-radius:7px;border:1px solid #cbd5e1;background:#fff;cursor:pointer;text-decoration:none;color:#334155}
.toolbar .cetak{background:#2563eb;border-color:#2563eb;color:#fff}
.paper{max-width:760px;margin:0 auto;background:#fff;padding:44px 52px}
.judul-lembar{text-align:center;font-weight:700;font-size:15px;margin-bottom:28px}
table.identitas{width:100%;border-collapse:collapse;font-size:13px;margin-bottom:24px}
table.identitas td{border:1px solid #111;padding:8px 10px;vertical-align:top}
table.identitas td:first-child{width:150px;font-weight:700;background:#f7f7f5}
table.komponen{width:100%;border-collapse:collapse;font-size:13px;margin-bottom:32px}
table.komponen th,table.komponen td{border:1px solid #111;padding:6px 10px}
table.komponen th{font-weight:700;text-align:center}
table.komponen td.nilai{text-align:center;width:100px}
table.komponen tr.kategori td{font-weight:700;background:#f4f4f2}
table.komponen tr.total td{font-weight:700}
.bawah{display:flex;justify-content:flex-end;margin-top:24px}
.ttd{font-size:13px;width:230px}
.ttd .ruang-ttd{height:64px}
.ttd .nama{text-decoration:underline;font-weight:700}
@media print{
  body{background:#fff;padding:0}
  .toolbar{display:none}
  .paper{margin:0;max-width:none;padding:20mm}
}
</style>
</head>
<body>

<div class="toolbar">
  <a href="{{ $backUrl }}">&larr; Kembali</a>
  <button class="cetak" onclick="window.print()">Cetak</button>
</div>

<div class="paper">
  <div class="judul-lembar">FORM NILAI PEMBIMBING LAPANGAN</div>

  <table class="identitas">
    <tr><td>Nama Mahasiswa</td><td>{{ $mahasiswa->nama }}</td></tr>
    <tr><td>NPM</td><td>{{ $mahasiswa->nim }}</td></tr>
    <tr><td>Judul KP/PKL</td><td>{{ $mahasiswa->seminar->judul_kp ?? '–' }}</td></tr>
    <tr><td>Tempat KP/PKL</td><td>{{ $mahasiswa->instansi->nama ?? '–' }}</td></tr>
  </table>

  @php
    $n = $mahasiswa->nilai;
    $komponen = [
      ['kategori' => 'A. Kedisiplinan', 'items' => [
        ['1. Jumlah Kehadiran', $n->lapangan_kehadiran],
        ['2. Taat Tata Tertib', $n->lapangan_tata_tertib],
      ]],
      ['kategori' => 'B. Kerjasama', 'items' => [
        ['1. Dengan Anggota Kelompok', $n->lapangan_kerjasama_anggota],
        ['2. Dengan Kelompok Lain', $n->lapangan_kerjasama_kelompok_lain],
        ['3. Pembimbing', $n->lapangan_kerjasama_pembimbing],
      ]],
      ['kategori' => 'C. Prestasi kerja', 'items' => [
        ['1. Inovasi', $n->lapangan_inovasi],
        ['2. Kemampuan Menyelesaikan Tugas', $n->lapangan_tugas],
        ['3. Keseriusan', $n->lapangan_keseriusan],
      ]],
    ];
  @endphp

  <table class="komponen">
    <tr><th style="text-align:left">Komponen Penilaian</th><th style="width:100px">Nilai</th></tr>
    @foreach($komponen as $grup)
      <tr class="kategori"><td colspan="2">{{ $grup['kategori'] }}</td></tr>
      @foreach($grup['items'] as [$label, $nilai])
      <tr>
        <td>{{ $label }}</td>
        <td class="nilai">{{ $nilai }}</td>
      </tr>
      @endforeach
    @endforeach
    <tr class="total">
      <td style="text-align:center">Rata-rata Nilai</td>
      <td class="nilai">{{ number_format($n->nilai_lapangan, 2) }}</td>
    </tr>
  </table>

  <div class="bawah">
    <div class="ttd">
      <p>Bandar Lampung, {{ now()->translatedFormat('d F Y') }}</p>
      <p>Pembimbing Lapangan</p>
      <p>Kerja Praktik,</p>
      <div class="ruang-ttd"></div>
      <p class="nama">{{ $mahasiswa->pembimbing_lapangan_nama ?? '–' }}</p>
      <p>NPP. ________________</p>
    </div>
  </div>
</div>

</body>
</html>