@extends('layouts.app')
@section('title','Nilai Saya')

@push('styles')
<style>
.predikat{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;font-weight:700;font-size:15px}
.predikat-A{background:var(--green-100);color:var(--green-700)}
.predikat-B{background:var(--blue-100);color:var(--blue-700)}
.predikat-C{background:var(--amber-100);color:var(--amber-600)}
.predikat-D, .predikat-E{background:var(--red-100);color:var(--red-600)}
.status-lulus{color:var(--green-600);font-weight:600}
.status-tidak-lulus{color:var(--red-600);font-weight:600}
.status-belum{color:var(--gray-400)}
.nilai-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:20px}
.nilai-box{background:var(--gray-50);border-radius:10px;padding:16px}
.nilai-box .label{font-size:12px;color:var(--gray-500);margin-bottom:6px}
.nilai-box .val{font-size:24px;font-weight:700}
</style>
@endpush

@section('content')
<div class="page-header"><h1>Nilai Saya</h1><p>Rekap nilai Kerja Praktik Anda — Nilai Lapangan, Nilai Pembimbing, dan Nilai Akhir</p></div>

@php $n = $mahasiswa->nilai; @endphp

<div class="card">
  <div class="card-header">
    <h3>📝 Rekap Nilai</h3>
    @if($n?->predikat)<span class="predikat predikat-{{ $n->predikat }}">{{ $n->predikat }}</span>@endif
  </div>
  <div class="card-body">
    <div class="nilai-grid">
      <div class="nilai-box">
        <div class="label">Nilai Lapangan</div>
        <div class="val">{{ $n?->nilai_lapangan ?? '–' }}</div>
      </div>
      <div class="nilai-box">
        <div class="label">Nilai Pembimbing</div>
        <div class="val">{{ $n?->nilai_seminar ?? '–' }} @if($n?->huruf_mutu_seminar)<span style="font-size:14px;color:var(--gray-500)">({{ $n->huruf_mutu_seminar }})</span>@endif</div>
      </div>
      <div class="nilai-box">
        <div class="label">Nilai Akhir</div>
        <div class="val">{{ $n?->nilai_akhir ?? '–' }}</div>
      </div>
    </div>

    @php $status = $n?->status_kelulusan ?? 'Belum Lengkap'; @endphp
    <p style="margin-bottom:16px">Status:
      <span class="{{ $status === 'Lulus' ? 'status-lulus' : ($status === 'Tidak Lulus' ? 'status-tidak-lulus' : 'status-belum') }}">{{ $status }}</span>
    </p>

    @if($n?->catatan_lapangan)
      <div class="alert alert-info" style="margin-bottom:12px"><strong>Catatan Pembimbing Lapangan:</strong> {{ $n->catatan_lapangan }}</div>
    @endif

    @if($n?->nilai_seminar !== null)
      <a href="{{ route('mahasiswa.nilai.cetak') }}" class="btn btn-primary" target="_blank">🖨️ Cetak Nilai Pembimbing (Dosen)</a>
    @else
      <button class="btn btn-outline" disabled title="Nilai Pembimbing belum diisi dosen">🖨️ Cetak Nilai Pembimbing (Dosen)</button>
    @endif

    @if($n?->nilai_lapangan !== null)
      <a href="{{ route('mahasiswa.nilai.cetakLapangan') }}" class="btn btn-primary" target="_blank">🖨️ Cetak Nilai Lapangan (Instansi)</a>
    @else
      <button class="btn btn-outline" disabled title="Nilai Lapangan belum diisi pembimbing lapangan">🖨️ Cetak Nilai Lapangan (Instansi)</button>
    @endif

    @if($n?->nilai_seminar === null || $n?->nilai_lapangan === null)
      <p class="form-hint" style="margin-top:8px">Tombol cetak aktif setelah nilai yang bersangkutan diisi oleh dosen pembimbing / pembimbing lapangan.</p>
    @endif
  </div>
</div>
@endsection