@extends('layouts.app')
@section('title','Profil Saya')

@section('content')
<div class="page-header page-header-row">
  <div>
    <h1>👤 Profil Saya</h1>
    <p>Informasi profil Anda yang terlihat oleh dosen dan instansi.</p>
  </div>
  <a href="{{ route('mahasiswa.profile.edit') }}" class="btn btn-primary">✏️ Edit Profil</a>
</div>

{{-- PROFIL CARD --}}
<div class="card" style="margin-bottom:20px">
  <div class="card-body">
    <div style="display:flex;align-items:flex-start;gap:24px;flex-wrap:wrap">

      {{-- FOTO --}}
      <div style="flex-shrink:0;text-align:center">
        @if($mahasiswa->foto_profil)
          <img src="{{ Storage::url($mahasiswa->foto_profil) }}"
               alt="Foto {{ $mahasiswa->nama }}"
               style="width:120px;height:120px;border-radius:50%;object-fit:cover;
                      border:3px solid var(--purple-200);box-shadow:var(--shadow-md)">
        @else
          <div style="width:120px;height:120px;border-radius:50%;
                      background:linear-gradient(135deg,var(--purple-100),var(--purple-50));
                      border:3px solid var(--purple-200);
                      display:flex;align-items:center;justify-content:center;
                      font-size:48px;box-shadow:var(--shadow-md)">🎓</div>
        @endif
        <div style="margin-top:8px;font-size:11px;color:var(--gray-400)">
          {{ $mahasiswa->foto_profil ? 'Foto Profil' : 'Belum ada foto' }}
        </div>
      </div>

      {{-- INFO UTAMA --}}
      <div style="flex:1;min-width:240px">
        <div style="font-size:22px;font-weight:800;color:var(--gray-900);margin-bottom:2px">
          {{ $mahasiswa->nama }}
        </div>
        <div style="font-size:13px;color:var(--gray-500);margin-bottom:12px">
          <code>{{ $mahasiswa->nim }}</code>
          · Angkatan {{ $mahasiswa->angkatan }}
          · <span class="badge badge-{{ $mahasiswa->status }}">{{ ucfirst($mahasiswa->status) }}</span>
        </div>

        @if($mahasiswa->bio)
          <div style="font-size:13px;color:var(--gray-600);background:var(--gray-50);
                      border-radius:8px;padding:10px 14px;border-left:3px solid var(--purple-400);
                      margin-bottom:14px;line-height:1.6">
            {{ $mahasiswa->bio }}
          </div>
        @endif

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:13px">
          <div>
            <div style="font-size:11px;color:var(--gray-400);font-weight:600;text-transform:uppercase;letter-spacing:.4px">No. HP</div>
            <div style="font-weight:500">{{ $mahasiswa->no_hp ?? '–' }}</div>
          </div>
          <div>
            <div style="font-size:11px;color:var(--gray-400);font-weight:600;text-transform:uppercase;letter-spacing:.4px">Email</div>
            <div style="font-weight:500">{{ $mahasiswa->user->email }}</div>
          </div>
          <div>
            <div style="font-size:11px;color:var(--gray-400);font-weight:600;text-transform:uppercase;letter-spacing:.4px">Dosen Pembimbing</div>
            <div style="font-weight:500">{{ $mahasiswa->dosen?->nama ?? 'Belum ditentukan' }}</div>
          </div>
          <div>
            <div style="font-size:11px;color:var(--gray-400);font-weight:600;text-transform:uppercase;letter-spacing:.4px">Instansi</div>
            <div style="font-weight:500">{{ $mahasiswa->instansi?->nama ?? '–' }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- INFO KP --}}
<div class="grid-2">
  <div class="card">
    <div class="card-header"><h3>📅 Info Kerja Praktik</h3></div>
    <div class="card-body" style="font-size:13px;display:grid;gap:12px">
      <div>
        <div style="font-size:11px;color:var(--gray-400);font-weight:600;text-transform:uppercase;letter-spacing:.4px">Periode KP</div>
        <div style="font-weight:500">
          {{ $mahasiswa->tanggal_mulai ?? '–' }}
          @if($mahasiswa->tanggal_selesai) s/d {{ $mahasiswa->tanggal_selesai }} @endif
        </div>
      </div>
      <div>
        <div style="font-size:11px;color:var(--gray-400);font-weight:600;text-transform:uppercase;letter-spacing:.4px">Instansi</div>
        <div style="font-weight:500">{{ $mahasiswa->instansi?->nama ?? '–' }}</div>
        @if($mahasiswa->instansi?->alamat)
          <div style="font-size:11px;color:var(--gray-400)">{{ $mahasiswa->instansi->alamat }}</div>
        @endif
      </div>
      <div>
        <div style="font-size:11px;color:var(--gray-400);font-weight:600;text-transform:uppercase;letter-spacing:.4px">Status</div>
        <span class="badge badge-{{ $mahasiswa->status }}">{{ ucfirst($mahasiswa->status) }}</span>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3>👔 Pembimbing Lapangan</h3></div>
    <div class="card-body" style="font-size:13px;display:grid;gap:12px">
      @if($mahasiswa->pembimbing_lapangan_nama)
        <div>
          <div style="font-size:11px;color:var(--gray-400);font-weight:600;text-transform:uppercase;letter-spacing:.4px">Nama</div>
          <div style="font-weight:500">{{ $mahasiswa->pembimbing_lapangan_nama }}</div>
        </div>
        <div>
          <div style="font-size:11px;color:var(--gray-400);font-weight:600;text-transform:uppercase;letter-spacing:.4px">Jabatan</div>
          <div style="font-weight:500">{{ $mahasiswa->pembimbing_lapangan_jabatan ?? '–' }}</div>
        </div>
        <div>
          <div style="font-size:11px;color:var(--gray-400);font-weight:600;text-transform:uppercase;letter-spacing:.4px">No. HP</div>
          <div style="font-weight:500">{{ $mahasiswa->pembimbing_lapangan_no_hp ?? '–' }}</div>
        </div>
      @else
        <div class="empty-state" style="padding:20px">
          <div class="icon">👔</div>
          <div>Data pembimbing lapangan belum diisi.</div>
          <div style="font-size:11px;color:var(--gray-400);margin-top:4px">Akan diisi oleh Admin.</div>
        </div>
      @endif
    </div>
  </div>
</div>

@endsection