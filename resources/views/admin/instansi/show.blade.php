@extends('layouts.app')
@section('title','Detail Instansi')
@section('content')

<div class="page-header page-header-row">
  <div style="display:flex;align-items:center;gap:12px">
    <a href="{{ route('admin.instansi.index') }}" class="btn btn-outline btn-sm">← Kembali</a>
    <div>
      <h1>{{ $instansi->nama }}</h1>
      <p>{{ $instansi->bidang ?? 'Instansi KP' }}</p>
    </div>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-header"><h3>Profil Instansi</h3></div>
    <div class="card-body" style="font-size:13px;display:grid;gap:12px">
      <div><div class="text-sm text-muted">Nama</div><strong>{{ $instansi->nama }}</strong></div>
      <div><div class="text-sm text-muted">Bidang</div>{{ $instansi->bidang ?? '–' }}</div>
      <div><div class="text-sm text-muted">Alamat</div>{{ $instansi->alamat ?? '–' }}</div>
      <div><div class="text-sm text-muted">Kontak Person</div>{{ $instansi->kontak_person ?? '–' }}</div>
      <div><div class="text-sm text-muted">No. HP</div>{{ $instansi->no_hp ?? '–' }}</div>
      <div><div class="text-sm text-muted">Email Login</div>{{ $instansi->user?->email ?? '–' }}</div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div><h3>Mahasiswa KP</h3><p>{{ $instansi->mahasiswas->count() }} mahasiswa aktif</p></div>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>NIM</th><th>Nama</th><th>Dosen</th><th>Progress</th><th>Status</th></tr></thead>
        <tbody>
          @forelse($instansi->mahasiswas as $m)
          @php $pct = $m->progressPersen(); @endphp
          <tr>
            <td><code>{{ $m->nim }}</code></td>
            <td><strong>{{ $m->nama }}</strong></td>
            <td class="text-sm">{{ $m->dosen?->nama ?? '–' }}</td>
            <td style="min-width:90px">
              <div class="prog-wrap" style="height:5px;margin-bottom:2px">
                <div class="prog-bar prog-bar-{{ $pct==100?'green':'amber' }}" style="width:{{ $pct }}%"></div>
              </div>
              <div class="text-sm text-muted">{{ $pct }}%</div>
            </td>
            <td><span class="badge badge-{{ $m->status }}">{{ ucfirst($m->status) }}</span></td>
          </tr>
          @empty
            <tr><td colspan="5" style="text-align:center;padding:20px;color:#94a3b8">Belum ada mahasiswa KP.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection