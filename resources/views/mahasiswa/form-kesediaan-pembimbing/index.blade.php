@extends('layouts.app')
@section('title', 'Form Kesediaan Pembimbing')
@push('styles')
<style>
.form-kesediaan{max-width:794px;margin:auto;background:#fff;padding:42px 54px;color:#111;font-family:Arial,sans-serif;font-size:12px;line-height:1.45}
.form-kesediaan h1{text-align:center;font-size:16px;margin:0 0 38px}.form-kesediaan table{border-collapse:collapse;margin:18px 0 34px}.form-kesediaan td{padding:2px 4px;vertical-align:top}.form-kesediaan ol{padding-left:22px;margin-top:6px}.form-kesediaan li{padding-left:6px;margin-bottom:5px}.signature{margin-top:52px}.print-only{display:none}@media print{body *{visibility:hidden}.form-kesediaan,.form-kesediaan *{visibility:visible}.form-kesediaan{position:absolute;left:0;top:0;width:100%;max-width:none;padding:18mm 20mm;font-size:11px}.no-print{display:none!important}.print-only{display:block}}
</style>
@endpush
@section('content')
<div class="page-header"><h1>Form Kesediaan Pembimbing</h1><p>Form diterbitkan otomatis oleh admin. Cetak atau simpan PDF, lalu teruskan kepada dosen pembimbing Anda.</p></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="no-print" style="display:flex;gap:8px;justify-content:flex-end;margin-bottom:14px">
  <button class="btn btn-outline" onclick="window.print()">Cetak / Simpan PDF</button>
  @if($mahasiswa->formKesediaanPembimbing->status === \App\Models\FormKesediaanPembimbing::STATUS_DITERBITKAN)
    <form method="POST" action="{{ route('mahasiswa.form-kesediaan-pembimbing.teruskan') }}">@csrf<button class="btn btn-primary">Teruskan ke Dosen Pembimbing</button></form>
  @elseif($mahasiswa->formKesediaanPembimbing->status === \App\Models\FormKesediaanPembimbing::STATUS_DITERUSKAN)
    <span class="badge badge-proses">Menunggu persetujuan dosen</span>
  @endif
</div>
<article class="form-kesediaan">
  <h1>FORM KESEDIAAN PEMBIMBING</h1>
  <p>Yang bertanda tangan di bawah ini menyatakan bahwa bersedia membimbing KP/PKL mahasiswa:</p>
  <table><tr><td>Nama</td><td>:</td><td>{{ $mahasiswa->nama }}</td></tr><tr><td>NPM</td><td>:</td><td>{{ $mahasiswa->nim }}</td></tr><tr><td>Program Studi</td><td>:</td><td>S1 Ilmu Komputer</td></tr></table>
  <p>Dengan memahami tugas dan tanggung jawab sebagai pembimbing KP/PKL, sebagai berikut:</p>
  <ol>
    <li>Mengawasi, memonitor, dan mengevaluasi mahasiswa peserta KP/PKL pada seluruh agenda KP/PKL.</li>
    <li>Membimbing mahasiswa peserta KP/PKL dalam menentukan tema/topik/judul Laporan KP/PKL.</li>
    <li>Membimbing mahasiswa peserta KP/PKL dalam menentukan keluasan cakupan isi Laporan KP/PKL.</li>
    <li>Membimbing mahasiswa peserta KP/PKL dalam upaya pemerolehan data dan informasi yang diperlukan dalam penyusunan Laporan KP/PKL.</li>
    <li>Membantu dan mengarahkan mahasiswa peserta KP/PKL agar mengembangkan kemampuan menulis karya ilmiah yang sesuai dengan pedoman, kaidah, dan etika penulisan.</li>
    <li>Mengevaluasi disiplin mahasiswa peserta KP/PKL dan memastikan penyelesaian Laporan KP/PKL secara tepat waktu.</li>
    <li>Menguji mahasiswa peserta KP/PKL sebagai bentuk pertanggungjawaban isi Laporan KP/PKL dan penguasaan teori sesuai bidang masing-masing.</li>
  </ol>
  <div class="signature"><p>Bandar Lampung, {{ now()->translatedFormat('d F Y') }}<br>Dosen Pembimbing</p><br><br><p><strong>{{ $mahasiswa->dosen->nama }}</strong><br><strong>NIP. {{ $mahasiswa->dosen->nip }}</strong></p></div>
</article>
@endsection
