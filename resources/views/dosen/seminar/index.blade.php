@extends('layouts.app')
@section('title', 'Seminar Mahasiswa')

@section('content')
<div class="page-header">
  <h1>Seminar Mahasiswa</h1>
  <p>Tinjau permintaan ACC dan pantau jadwal seminar mahasiswa bimbingan Anda.</p>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

@forelse($seminars as $seminar)
  @php $mahasiswa = $seminar->mahasiswa; @endphp
  <div class="card" style="margin-bottom:16px">
    <div class="card-header">
      <div>
        <h3>{{ $mahasiswa?->nama ?? 'Mahasiswa' }}</h3>
        <span class="text-sm text-muted">{{ $mahasiswa?->nim ?? '-' }}</span>
      </div>
      <span class="badge {{ $seminar->status === 'acc_dospem' || $seminar->status === 'selesai' ? 'badge-selesai' : 'badge-proses' }}">
        {{ ucwords(str_replace('_', ' ', $seminar->status)) }}
      </span>
    </div>
    <div class="card-body">
      <div class="form-group">
        <label class="form-label">Judul KP</label>
        <div>{{ $seminar->judul_kp }}</div>
      </div>
      <div class="form-grid" style="margin-bottom:16px">
        <div><span class="text-sm text-muted">Tanggal & jam</span><div>{{ $seminar->tanggal ? \Carbon\Carbon::parse($seminar->tanggal)->format('d M Y') : '-' }} · {{ $seminar->jam_mulai ? \Carbon\Carbon::parse($seminar->jam_mulai)->format('H:i') : '-' }}{{ $seminar->jam_selesai ? ' - '.\Carbon\Carbon::parse($seminar->jam_selesai)->format('H:i') : '' }} WIB</div></div>
        <div><span class="text-sm text-muted">Ruangan</span><div>{{ $seminar->ruangan ?? '-' }}</div></div>
      </div>

      @if($seminar->status === \App\Models\Seminar::STATUS_MENUNGGU_ACC_DOSPEM)
        <form method="POST" action="{{ route('dosen.seminar.verifikasi-acc', $seminar) }}">
          @csrf
          <div class="form-group">
            <label class="form-label">Catatan <span class="text-muted">(wajib saat menolak)</span></label>
            <textarea name="catatan" class="form-control" rows="3" maxlength="500" placeholder="Berikan arahan atau alasan penolakan bila diperlukan..."></textarea>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <button type="submit" name="keputusan" value="setujui" class="btn btn-success">Setujui ACC</button>
            <button type="submit" name="keputusan" value="tolak" class="btn btn-danger">Tolak ACC</button>
          </div>
        </form>
      @else
        @if($seminar->catatan)<div class="alert alert-warning" style="margin-top:16px">{{ $seminar->catatan }}</div>@endif
      @endif
    </div>
  </div>
@empty
  <div class="card"><div class="card-body empty-state"><div class="icon">🗓️</div><p>Belum ada permintaan atau jadwal seminar dari mahasiswa bimbingan Anda.</p></div></div>
@endforelse
@endsection
