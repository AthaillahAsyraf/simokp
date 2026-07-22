@extends('layouts.app')
@section('title','Detail Mahasiswa')
@section('content')

<div class="page-header page-header-row">
  <div style="display:flex;align-items:center;gap:14px">
    <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline btn-sm">← Kembali</a>
    @if($mahasiswa->foto_profil)
      <img src="{{ $mahasiswa->fotoUrl() }}" alt="{{ $mahasiswa->nama }}"
           style="width:52px;height:52px;border-radius:50%;object-fit:cover;
                  border:3px solid var(--purple-200);box-shadow:var(--shadow)">
    @else
      <div style="width:52px;height:52px;border-radius:50%;
                  background:var(--purple-50);border:3px solid var(--purple-200);
                  display:flex;align-items:center;justify-content:center;
                  font-size:20px;font-weight:800;color:var(--purple-600)">
        {{ $mahasiswa->inisial() }}
      </div>
    @endif
    <div>
      <h1>{{ $mahasiswa->nama }}</h1>
      <p><code>{{ $mahasiswa->nim }}</code> · Angkatan {{ $mahasiswa->angkatan }}</p>
    </div>
  </div>
  <span class="badge badge-{{ $mahasiswa->status }}" style="font-size:13px;padding:5px 14px">{{ ucfirst($mahasiswa->status) }}</span>
</div>

<div class="grid-2">
  {{-- Info KP --}}
  <div class="card">
    <div class="card-header"><h3>Informasi Kerja Praktik</h3></div>
    <div class="card-body" style="display:grid;gap:12px;font-size:13px">
      <div>
        <div class="text-sm text-muted" style="margin-bottom:2px">Dosen Pembimbing</div>
        <strong>{{ $mahasiswa->dosen?->nama ?? '–' }}</strong>
      </div>
      <div>
        <div class="text-sm text-muted" style="margin-bottom:2px">Instansi / Lokasi KP</div>
        <strong>{{ $mahasiswa->instansi?->nama ?? '–' }}</strong>
      </div>
      <div>
        <div class="text-sm text-muted" style="margin-bottom:2px">Bidang Instansi</div>
        {{ $mahasiswa->instansi?->bidang ?? '–' }}
      </div>
      <div>
        <div class="text-sm text-muted" style="margin-bottom:2px">Alamat Instansi</div>
        {{ $mahasiswa->instansi?->alamat ?? '–' }}
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div>
          <div class="text-sm text-muted" style="margin-bottom:2px">Tanggal Mulai</div>
          <strong>{{ $mahasiswa->tanggal_mulai ?? '–' }}</strong>
        </div>
        <div>
          <div class="text-sm text-muted" style="margin-bottom:2px">Tanggal Selesai</div>
          <strong>{{ $mahasiswa->tanggal_selesai ?? '–' }}</strong>
        </div>
      </div>
      <div>
        <div class="text-sm text-muted" style="margin-bottom:2px">No. HP</div>
        {{ $mahasiswa->no_hp ?? '–' }}
      </div>
    </div>
  </div>

  {{-- Seminar --}}
  <div>
    @if(false) {{-- Progress per BAB sudah tidak digunakan. --}}
    <div class="card" style="margin-bottom:16px">
      <div class="card-header">
        <div><h3>Progress BAB</h3></div>
        <span style="font-weight:700;color:{{ $mahasiswa->progressPersen()==100?'#16a34a':'#2563eb' }}">{{ $mahasiswa->progressPersen() }}%</span>
      </div>
      <div class="card-body">
        <div class="prog-wrap" style="height:8px;margin-bottom:14px">
          <div class="prog-bar prog-bar-{{ $mahasiswa->progressPersen()==100?'green':'blue' }}" style="width:{{ $mahasiswa->progressPersen() }}%"></div>
        </div>
        <div style="display:flex;flex-direction:column;gap:6px">
          @foreach($mahasiswa->progressBabs as $p)
          <div style="display:flex;align-items:center;gap:10px;padding:7px 10px;border-radius:8px;background:{{ $p->status==='selesai'?'#f0fdf4':'#f8fafc' }};border:1px solid {{ $p->status==='selesai'?'#dcfce7':'#e2e8f0' }}">
            <span style="font-size:16px">{{ $p->status==='selesai'?'✅':'⏳' }}</span>
            <div style="flex:1">
              <div style="font-size:13px;font-weight:600;color:{{ $p->status==='selesai'?'#15803d':'#374151' }}">{{ $p->bab }}</div>
              @if($p->tanggal_selesai)<div class="text-sm text-muted">{{ $p->tanggal_selesai }}</div>@endif
            </div>
            <span class="badge badge-{{ $p->status==='selesai'?'selesai':'belum' }}">{{ ucfirst($p->status) }}</span>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    @endif
    {{-- Seminar --}}
    <div class="card">
      <div class="card-header"><h3>🎤 Seminar KP</h3></div>
      <div class="card-body" style="font-size:13px">
        @if($mahasiswa->seminar)
          @php $s = $mahasiswa->seminar; @endphp
          <div style="display:grid;gap:9px">
            <div><span class="text-muted">Tanggal:</span> <strong>{{ $s->tanggal }}</strong></div>
            <div><span class="text-muted">Jam:</span> {{ \Carbon\Carbon::parse($s->jam)->format('H:i') }} WIB · {{ $s->ruangan }}</div>
            <div><span class="text-muted">Penguji:</span> {{ $s->dosen_penguji ?? '–' }}</div>
            <div><span class="text-muted">Status:</span> <span class="badge badge-{{ $s->status }}">{{ ucfirst($s->status) }}</span></div>
          </div>
        @else
          <div class="empty-state" style="padding:20px">
            <div class="icon">🗓️</div>
            <p>Belum ada jadwal seminar.</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
