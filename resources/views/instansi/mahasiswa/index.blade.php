@extends('layouts.app')
@section('title','Mahasiswa KP')
@section('content')

<div class="page-header">
  <h1>🎓 Mahasiswa KP</h1>
  <p>Data dan progress laporan mahasiswa di instansi Anda.</p>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Nama / NIM</th>
          <th>Dosen Pembimbing</th>
          <th style="text-align:center">BAB I</th>
          <th style="text-align:center">BAB II</th>
          <th style="text-align:center">BAB III</th>
          <th style="text-align:center">BAB IV</th>
          <th style="text-align:center">BAB V</th>
          <th style="text-align:center">Progress</th>
          <th>Status KP</th>
        </tr>
      </thead>
      <tbody>
        @forelse($mahasiswas as $m)
        @php
          $babs = $m->progressBabs->keyBy('bab');
          $pct  = $m->progressPersen();
        @endphp
        <tr>
          <td>
            <strong>{{ $m->nama }}</strong>
            <div class="text-sm text-muted">{{ $m->nim }}</div>
          </td>
          <td class="text-sm">{{ $m->dosen?->nama ?? '–' }}</td>

          @foreach(['BAB I','BAB II','BAB III','BAB IV','BAB V'] as $label)
          @php
            $b  = $babs[$label] ?? null;
            $vs = $b?->verifikasi_status ?? 'none';
            $st = $b?->status ?? 'belum';
          @endphp
          <td style="text-align:center">
            @if($st === 'selesai' && $vs === 'approved')
              <span title="Selesai" style="font-size:16px">✅</span>
            @elseif($vs === 'pending')
              <span title="Menunggu verifikasi dosen" style="font-size:16px">⏳</span>
            @elseif($vs === 'rejected')
              <span title="Ditolak dosen" style="font-size:16px">❌</span>
            @elseif($st === 'proses')
              <span title="Sedang dikerjakan" style="font-size:16px">🔄</span>
            @else
              <span title="Belum dimulai" style="font-size:16px;color:var(--gray-300)">○</span>
            @endif
          </td>
          @endforeach

          <td style="min-width:100px">
            <div style="display:flex;align-items:center;gap:6px">
              <div class="prog-wrap" style="flex:1;height:6px">
                <div class="prog-bar prog-bar-{{ $pct==100?'green':'blue' }}" style="width:{{ $pct }}%"></div>
              </div>
              <span style="font-size:12px;font-weight:700;color:{{ $pct==100?'var(--green-600)':'var(--blue-600)' }};white-space:nowrap">{{ $pct }}%</span>
            </div>
          </td>

          <td>
            <span class="badge badge-{{ $m->status }}">{{ ucfirst($m->status) }}</span>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="9" style="text-align:center;padding:32px;color:var(--gray-400)">
            Tidak ada mahasiswa KP di instansi ini.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Keterangan ikon --}}
  <div style="padding:10px 18px;border-top:1px solid var(--gray-100);display:flex;gap:16px;flex-wrap:wrap;font-size:12px;color:var(--gray-500)">
    <span>✅ Selesai</span>
    <span>⏳ Menunggu verifikasi</span>
    <span>❌ Ditolak</span>
    <span>🔄 Proses</span>
    <span style="color:var(--gray-300)">○ Belum</span>
  </div>
</div>
@endsection