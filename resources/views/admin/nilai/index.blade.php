@extends('layouts.app')
@section('title','Nilai KP')

@push('styles')
<style>
.filter-row{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.filter-row .form-control{width:auto;min-width:160px}
.predikat{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;font-weight:700;font-size:13px}
.predikat-A{background:var(--green-100);color:var(--green-700)}
.predikat-B{background:var(--blue-100);color:var(--blue-700)}
.predikat-C{background:var(--amber-100);color:var(--amber-600)}
.predikat-D, .predikat-E{background:var(--red-100);color:var(--red-600)}
.status-lulus{color:var(--green-600);font-weight:600}
.status-tidak-lulus{color:var(--red-600);font-weight:600}
.status-belum{color:var(--gray-400)}
</style>
@endpush

@section('content')
<div class="page-header"><h1>Nilai KP</h1><p>Monitoring nilai seluruh mahasiswa (read-only — input nilai dilakukan Dosen & Instansi)</p></div>

<form method="GET" class="filter-row">
  <input type="text" name="search" class="form-control" placeholder="🔍 Cari nama/NIM..." value="{{ request('search') }}">
  <select name="instansi" class="form-control">
    <option value="">Semua Instansi</option>
    @foreach($instansis as $i)
      <option value="{{ $i->id }}" {{ request('instansi')==$i->id?'selected':'' }}>{{ $i->nama }}</option>
    @endforeach
  </select>
  <select name="status_kelulusan" class="form-control">
    <option value="">Semua Status</option>
    @foreach(['Belum Lengkap','Lulus','Tidak Lulus'] as $st)
      <option value="{{ $st }}" {{ request('status_kelulusan')==$st?'selected':'' }}>{{ $st }}</option>
    @endforeach
  </select>
  <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
  <a href="{{ route('admin.nilai.index') }}" class="btn btn-outline btn-sm">Reset</a>
</form>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Mahasiswa</th><th>Instansi</th><th>Dosen</th>
          <th>Nilai Instansi</th><th>Nilai Pembimbing</th><th>Nilai Seminar</th>
          <th>Nilai Akhir</th><th>Predikat</th><th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($mahasiswas as $m)
        @php $n = $m->nilai; @endphp
        <tr>
          <td><strong>{{ $m->nama }}</strong><br><code class="text-sm">{{ $m->nim }}</code></td>
          <td class="text-sm">{{ $m->instansi?->nama ?? '–' }}</td>
          <td class="text-sm text-muted">{{ $m->dosen?->nama ?? '–' }}</td>
          <td class="text-sm">{{ $n?->nilai_instansi ?? '–' }}</td>
          <td class="text-sm">{{ $n?->nilai_pembimbing ?? '–' }}</td>
          <td class="text-sm">{{ $n?->nilai_seminar ?? '–' }}</td>
          <td><strong>{{ $n?->nilai_akhir ?? '–' }}</strong></td>
          <td>
            @if($n?->predikat)
              <span class="predikat predikat-{{ $n->predikat }}">{{ $n->predikat }}</span>
            @else
              <span class="text-muted">–</span>
            @endif
          </td>
          <td>
            @php $status = $n?->status_kelulusan ?? 'Belum Lengkap'; @endphp
            <span class="{{ $status === 'Lulus' ? 'status-lulus' : ($status === 'Tidak Lulus' ? 'status-tidak-lulus' : 'status-belum') }}">{{ $status }}</span>
          </td>
        </tr>
        @empty
          <tr><td colspan="9" style="text-align:center;padding:28px;color:var(--gray-400)">Tidak ada data yang cocok dengan filter.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection