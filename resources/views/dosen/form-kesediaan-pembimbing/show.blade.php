@extends('layouts.app')
@section('title', 'Form Kesediaan Pembimbing')
@push('styles')
<style>
.surat-kesediaan{max-width:794px;margin:auto;background:#fff;padding:42px 54px;color:#111;font-family:Arial,sans-serif;font-size:12px;line-height:1.45}.surat-kesediaan h1{text-align:center;font-size:16px;margin:0 0 38px}.surat-kesediaan table{border-collapse:collapse;margin:18px 0 34px}.surat-kesediaan td{padding:2px 4px;vertical-align:top}.surat-kesediaan ol{padding-left:22px;margin-top:6px}.surat-kesediaan li{padding-left:6px;margin-bottom:5px}.signature{margin-top:52px}@media print{body *{visibility:hidden}.surat-kesediaan,.surat-kesediaan *{visibility:visible}.surat-kesediaan{position:absolute;left:0;top:0;width:100%;max-width:none;padding:18mm 20mm;font-size:11px}.no-print{display:none!important}}
</style>
@endpush
@section('content')
<div class="no-print" style="margin-bottom:14px"><button class="btn btn-outline" onclick="window.print()">Cetak / Simpan PDF</button></div>
<article class="surat-kesediaan">
  <h1>FORM KESEDIAAN PEMBIMBING</h1>
  <p>Yang bertanda tangan di bawah ini menyatakan bahwa bersedia membimbing KP/PKL mahasiswa:</p>
  <table><tr><td>Nama</td><td>:</td><td>{{ $form->mahasiswa->nama }}</td></tr><tr><td>NPM</td><td>:</td><td>{{ $form->mahasiswa->nim }}</td></tr><tr><td>Program Studi</td><td>:</td><td>S1 Ilmu Komputer</td></tr></table>
  <p>Dengan memahami tugas dan tanggung jawab sebagai pembimbing KP/PKL, sebagai berikut:</p>
  <ol><li>Mengawasi, memonitor, dan mengevaluasi mahasiswa peserta KP/PKL pada seluruh agenda KP/PKL.</li><li>Membimbing mahasiswa dalam menentukan tema/topik/judul Laporan KP/PKL.</li><li>Membimbing mahasiswa dalam menentukan keluasan cakupan isi Laporan KP/PKL.</li><li>Membimbing mahasiswa dalam pemerolehan data dan informasi untuk penyusunan Laporan KP/PKL.</li><li>Mengarahkan mahasiswa mengembangkan penulisan karya ilmiah sesuai pedoman, kaidah, dan etika.</li><li>Mengevaluasi disiplin mahasiswa dan memastikan penyelesaian laporan tepat waktu.</li><li>Menguji mahasiswa sebagai pertanggungjawaban isi laporan dan penguasaan teori.</li></ol>
  <div class="signature"><p>Bandar Lampung, {{ ($form->disetujui_at ?? now())->translatedFormat('d F Y') }}<br>Dosen Pembimbing</p><br><br><p><strong>{{ $form->dosen->nama }}</strong><br><strong>NIP. {{ $form->dosen->nip }}</strong></p></div>
</article>
@endsection
