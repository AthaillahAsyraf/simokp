@extends('layouts.app')
@section('title','Detail Dosen')
@section('content')

<div class="page-header page-header-row">
  <div style="display:flex;align-items:center;gap:12px">
    <a href="{{ route('admin.dosen.index') }}" class="btn btn-outline btn-sm">← Kembali</a>
    <div>
      <h1>{{ $dosen->nama }}</h1>
      <p>NIP: <code>{{ $dosen->nip }}</code></p>
    </div>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-header"><h3>Profil Dosen</h3></div>
    <div class="card-body" style="font-size:13px;display:grid;gap:12px">
      <div><div class="text-sm text-muted">NIP</div><code>{{ $dosen->nip }}</code></div>
      <div><div class="text-sm text-muted">Nama Lengkap</div><strong>{{ $dosen->nama }}</strong></div>
      <div><div class="text-sm text-muted">Email</div>{{ $dosen->user?->email ?? '–' }}</div>
      <div><div class="text-sm text-muted">No. HP</div>{{ $dosen->no_hp ?? '–' }}</div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div><h3>Mahasiswa Bimbingan</h3><p>{{ $dosen->mahasiswas->count() }} mahasiswa</p></div>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>NIM</th><th>Nama</th><th>Instansi</th><th>Progress</th><th>Status</th></tr></thead>
        <tbody>
          @forelse($dosen->mahasiswas as $m)
          @php $pct = $m->progressPersen(); @endphp
          <tr>
            <td><code>{{ $m->nim }}</code></td>
            <td><strong>{{ $m->nama }}</strong></td>
            <td class="text-sm">{{ $m->instansi?->nama ?? '–' }}</td>
            <td style="min-width:90px">
              <div class="prog-wrap" style="height:5px;margin-bottom:2px">
                <div class="prog-bar prog-bar-{{ $pct==100?'green':'blue' }}" style="width:{{ $pct }}%"></div>
              </div>
              <div class="text-sm text-muted">{{ $pct }}%</div>
            </td>
            <td><span class="badge badge-{{ $m->status }}">{{ ucfirst($m->status) }}</span></td>
          </tr>
          @empty
            <tr><td colspan="5" style="text-align:center;padding:20px;color:#94a3b8">Belum ada mahasiswa bimbingan.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection