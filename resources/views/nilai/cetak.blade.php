<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Lembar Penilaian Seminar KP — {{ $mahasiswa->nama }}</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Times New Roman',Times,serif;color:#111;background:#e2e8f0;padding:24px}
.toolbar{max-width:760px;margin:0 auto 16px;display:flex;justify-content:flex-end;gap:8px}
.toolbar button,.toolbar a{font-family:'Inter',sans-serif;font-size:13px;font-weight:600;padding:8px 16px;border-radius:7px;border:1px solid #cbd5e1;background:#fff;cursor:pointer;text-decoration:none;color:#334155}
.toolbar .cetak{background:#2563eb;border-color:#2563eb;color:#fff}
.paper{max-width:760px;margin:0 auto;background:#fff;padding:40px 48px;box-shadow:0 1px 3px rgba(0,0,0,.15)}
.kop{display:flex;align-items:center;gap:16px;border-bottom:3px solid #111;padding-bottom:10px;margin-bottom:18px}
.kop img{width:72px;height:72px;object-fit:contain}
.kop .teks{flex:1;text-align:center}
.kop h1{font-size:15px;font-weight:700;line-height:1.35}
.kop h2{font-size:15px;font-weight:700;line-height:1.35}
.kop p{font-size:11px;margin-top:2px}
.judul-lembar{text-align:center;border:1px solid #111;padding:8px;font-weight:700;font-size:13px;margin-bottom:20px}
.identitas{font-size:13px;margin-bottom:16px}
.identitas tr td:first-child{width:110px;vertical-align:top}
.identitas tr td:nth-child(2){width:14px}
table.aspek{width:100%;border-collapse:collapse;font-size:13px;margin-bottom:20px}
table.aspek th,table.aspek td{border:1px solid #111;padding:6px 8px}
table.aspek th{font-weight:700;text-align:center}
table.aspek td.nilai,table.aspek td.persen,table.aspek td.na{text-align:center;width:80px}
table.aspek tr.kategori td{font-weight:700;background:#f4f4f2}
table.aspek tr.total td{font-weight:700}
.bawah{display:flex;justify-content:space-between;align-items:flex-start;margin-top:24px}
table.mutu{border-collapse:collapse;font-size:12px}
table.mutu th,table.mutu td{border:1px solid #111;padding:4px 10px;text-align:center}
.ttd{font-size:13px;width:230px}
.ttd .ruang-ttd{height:64px}
.ttd .nama{text-decoration:underline;font-weight:700}
.footer-kode{font-size:11px;margin-top:32px}
@media print{
  body{background:#fff;padding:0}
  .toolbar{display:none}
  .paper{box-shadow:none;margin:0;max-width:none;padding:20mm}
}
</style>
</head>
<body>

<div class="toolbar">
  <a href="{{ $backUrl }}">&larr; Kembali</a>
  <button class="cetak" onclick="window.print()">Cetak</button>
</div>

<div class="paper">
  <div class="kop">
    <img src="{{ asset('images/logo-unila.png') }}" alt="Logo Unila" onerror="this.style.display='none'">
    <div class="teks">
      <h1>KEMENTERIAN PENDIDIKAN, KEBUDAYAAN,<br>RISET DAN TEKNOLOGI</h1>
      <h2>UNIVERSITAS LAMPUNG<br>FAKULTAS MATEMATIKA DAN ILMU PENGETAHUAN ALAM<br>JURUSAN ILMU KOMPUTER</h2>
      <p>Jl. Prof. Dr. Sumantri Brojonegoro No. 1 Bandar Lampung 35145<br>
      Telp/Fax (0721) 704625 Email: ilmu.komputer@fmipa.unila.ac.id Web: http://ilkom.unila.ac.id</p>
    </div>
  </div>

  <div class="judul-lembar">LEMBAR PENILAIAN SEMINAR KERJA PRAKTIK<br>DOSEN PEMBIMBING</div>

  <table class="identitas">
    <tr><td>Nama / NPM</td><td>:</td><td>{{ $mahasiswa->nama }} / {{ $mahasiswa->nim }}</td></tr>
    <tr><td>Judul KP/PKL</td><td>:</td><td>{{ $mahasiswa->seminar->judul_kp ?? '–' }}</td></tr>
    <tr><td>Tempat KP/PKL</td><td>:</td><td>{{ $mahasiswa->instansi->nama ?? '–' }}</td></tr>
  </table>

  @php
    $n = $mahasiswa->nilai;
    $aspek = [
      ['kategori' => '1. Seminar', 'items' => [
        ['a. Penguasaan materi / metode', $n->seminar_penguasaan_materi, '20 %'],
        ['b. Sikap ilmiah dan argumentasi', $n->seminar_sikap_ilmiah, '10 %'],
        ['c. Teknik penyajian dan kebahasaan', $n->seminar_teknik_penyajian, '10 %'],
      ]],
      ['kategori' => '2. Laporan', 'items' => [
        ['a. Originalitas', $n->seminar_originalitas, '30 %'],
        ['b. Relevansi dan keterpaduan', $n->seminar_relevansi, '15 %'],
        ['c. Penulisan (format dan bahasa)', $n->seminar_penulisan, '15 %'],
      ]],
    ];
  @endphp

  <table class="aspek">
    <tr><th style="text-align:left">Aspek yang dinilai</th><th>Nilai</th><th>Persentase</th><th>NA</th></tr>
    @foreach($aspek as $grup)
      <tr class="kategori"><td colspan="4">{{ $grup['kategori'] }}</td></tr>
      @foreach($grup['items'] as [$label, $nilai, $persen])
      <tr>
        <td style="padding-left:24px">{{ $label }}</td>
        <td class="nilai">{{ $nilai }}</td>
        <td class="persen">{{ $persen }}</td>
        <td class="na"></td>
      </tr>
      @endforeach
    @endforeach
    <tr class="total">
      <td style="text-align:right">Nilai Total</td>
      <td class="nilai">{{ number_format($n->nilai_seminar, 2) }}</td>
      <td class="persen"></td>
      <td class="na">{{ $n->huruf_mutu_seminar }}</td>
    </tr>
  </table>

  <div class="bawah">
    <table class="mutu">
      <tr><th>Huruf Mutu</th><th>Range Nilai</th></tr>
      <tr><td>A</td><td>Nilai &ge; 76</td></tr>
      <tr><td>B+</td><td>71 &le; Nilai &lt; 76</td></tr>
      <tr><td>B</td><td>66 &le; Nilai &lt; 71</td></tr>
      <tr><td>C+</td><td>61 &le; Nilai &lt; 66</td></tr>
      <tr><td>C</td><td>56 &le; Nilai &lt; 61</td></tr>
      <tr><td>BL</td><td>Nilai &lt; 56</td></tr>
    </table>

    <div class="ttd">
      <p>Bandar Lampung, {{ now()->translatedFormat('d F Y') }}</p>
      <p><strong>Dosen Pembimbing</strong></p>
      <div class="ruang-ttd"></div>
      <p class="nama">{{ $dosen->nama }}</p>
      <p>NIP. {{ $dosen->nip }}</p>
    </div>
  </div>

  <p class="footer-kode">F-21/SOP/MIPA/7.5/II/12/002</p>
</div>

</body>
</html>