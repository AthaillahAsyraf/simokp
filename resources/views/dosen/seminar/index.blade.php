@extends('layouts.app')
@section('title','Jadwal Seminar')
@section('content')

<div class="page-header">
  <h1>🎤 Jadwal Seminar Mahasiswa</h1>
  <p>Jadwal seminar KP mahasiswa bimbingan Anda.</p>
</div>

@if($seminars->isEmpty())
  <div class="card">
    <div class="card-body empty-state">
      <div class="icon">🗓️</div>
      <p>Belum ada jadwal seminar untuk mahasiswa bimbingan Anda.</p>
    </div>
  </div>
@else
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Mahasiswa</th>
            <th>Tanggal</th>
            <th>Jam</th>
            <th>Ruangan</th>
            <th>Dosen Penguji</th>
            <th>Nilai</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach($seminars as $s)
          <tr>
            <td>
              <strong>{{ $s->mahasiswa->nama }}</strong>
              <div class="text-sm text-muted">{{ $s->mahasiswa->nim }}</div>
            </td>
            <td>{{ \Carbon\Carbon::parse($s->tanggal)->format('d M Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($s->jam)->format('H:i') }} WIB</td>
            <td>{{ $s->ruangan ?? '–' }}</td>
            <td>{{ $s->dosen_penguji ?? '–' }}</td>
            <td>
              @if($s->nilai)
                <strong style="color:var(--green-600);font-size:16px">{{ $s->nilai }}</strong>
              @else
                <span class="text-muted">–</span>
              @endif
            </td>
            <td>
              <span class="badge {{ $s->status === 'selesai' ? 'badge-selesai' : ($s->status === 'terjadwal' ? 'badge-terjadwal' : 'badge-proses') }}">
                {{ ucfirst(str_replace('_', ' ', $s->status)) }}
              </span>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endif
@endsection