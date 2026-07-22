<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Rekap Kehadiran - {{ $mahasiswa->nama }}</title>
  <style>
    @page { size: A4 portrait; margin: 14mm 13mm 16mm; }
    * { box-sizing: border-box; }
    body { margin: 0; color: #111; font-family: Arial, Helvetica, sans-serif; font-size: 10pt; line-height: 1.35; }
    .no-print { margin: 16px auto; width: 184mm; text-align: right; }
    .print-button { border: 1px solid #222; border-radius: 4px; background: #fff; padding: 8px 12px; cursor: pointer; font-size: 10pt; }
    .document-title { margin: 0 0 16px; font-size: 15pt; font-weight: 700; }
    .identity { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
    .identity td { padding: 2px 0; vertical-align: top; }
    .identity td:first-child { width: 39mm; font-weight: 700; }
    .summary { display: flex; gap: 22px; margin: 0 0 18px; font-size: 9pt; }
    .summary strong { font-size: 11pt; }
    table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    thead { display: table-header-group; }
    th { border-bottom: 1.5px solid #111; padding: 0 5px 7px; text-align: left; font-size: 8.5pt; text-transform: uppercase; }
    td { padding: 10px 5px; vertical-align: top; border-bottom: 1px solid #cfcfcf; }
    tr { break-inside: avoid; page-break-inside: avoid; }
    .col-date { width: 17%; }.col-time { width: 16%; }.col-distance { width: 14%; }.col-note { width: 39%; }.col-sign { width: 14%; }
    .date { font-weight: 700; }.time-line, .distance-line { white-space: nowrap; margin-bottom: 2px; }
    .note-block + .note-block { margin-top: 8px; }.note-label { font-weight: 700; }.signature { min-height: 56px; }
    .empty { padding: 24px 0; text-align: center; color: #666; }.footer { margin-top: 12px; color: #555; font-size: 8pt; }
    @media print { .no-print { display: none; }.document-title { margin-top: 0; } }
  </style>
</head>
<body>
  <div class="no-print"><button class="print-button" onclick="window.print()">Cetak / Simpan PDF</button></div>
  <main>
    <h1 class="document-title">Rekap Kehadiran Mahasiswa Kerja Praktik</h1>
    <table class="identity" aria-label="Identitas mahasiswa">
      <tr><td>Nama</td><td>: {{ $mahasiswa->nama }}</td></tr>
      <tr><td>NPM</td><td>: {{ $mahasiswa->nim }}</td></tr>
      <tr><td>Instansi KP</td><td>: {{ $mahasiswa->instansi?->nama ?? '-' }}</td></tr>
      <tr><td>Dosen Pembimbing</td><td>: {{ $mahasiswa->dosen?->nama ?? '-' }}</td></tr>
      <tr><td>Pembimbing Lapangan</td><td>: {{ $mahasiswa->pembimbing_lapangan_nama ?? '-' }}</td></tr>
    </table>
    <div class="summary">
      <div>Total Kehadiran<br><strong>{{ $absensis->count() }} hari</strong></div>
      <div>Total Durasi<br><strong>{{ number_format($absensis->sum(fn ($absensi) => $absensi->durasi_jam ?? 0), 1, ',', '.') }} jam</strong></div>
    </div>
    <table aria-label="Rekap absensi">
      <thead><tr><th class="col-date">Tanggal</th><th class="col-time">Jam</th><th class="col-distance">Jarak</th><th class="col-note">Catatan</th><th class="col-sign">Paraf Pem. Lapangan</th></tr></thead>
      <tbody>
        @forelse($absensis as $absensi)
          <tr>
            <td class="date">{{ $absensi->tanggal->locale('id')->translatedFormat('l, d F Y') }}</td>
            <td>
              <div class="time-line"><strong>Masuk:</strong> {{ \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i:s') }}</div>
              <div class="time-line"><strong>Pulang:</strong> {{ $absensi->jam_keluar ? \Carbon\Carbon::parse($absensi->jam_keluar)->format('H:i:s') : '-' }}</div>
              <div class="time-line"><strong>Durasi:</strong> {{ $absensi->durasi_jam !== null ? number_format($absensi->durasi_jam, 2, ',', '.') . ' jam' : '-' }}</div>
            </td>
            <td>
              <div class="distance-line"><strong>Masuk:</strong> {{ $absensi->jarak_masuk !== null ? $absensi->jarak_masuk . ' m' : '-' }}</div>
              <div class="distance-line"><strong>Pulang:</strong> {{ $absensi->jarak_keluar !== null ? $absensi->jarak_keluar . ' m' : '-' }}</div>
            </td>
            <td>
              <div class="note-block"><span class="note-label">Rencana:</span> {{ $absensi->rencana ?: '-' }}</div>
              <div class="note-block"><span class="note-label">Realisasi:</span> {{ $absensi->realisasi ?: '-' }}</div>
              @if($absensi->catatan_dosen)<div class="note-block"><span class="note-label">Catatan Pembimbing:</span> {{ $absensi->catatan_dosen }}</div>@endif
            </td>
            <td><div class="signature"></div></td>
          </tr>
        @empty
          <tr><td colspan="5" class="empty">Belum ada data kehadiran yang dapat dicetak.</td></tr>
        @endforelse
      </tbody>
    </table>
    <div class="footer">Dicetak dari SIMOKP pada {{ now()->locale('id')->translatedFormat('d F Y, H:i') }} WIB.</div>
  </main>
</body>
</html>
