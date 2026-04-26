@extends('layouts.app')
@section('title','Surat & Dokumen')
@section('content')

<div class="page-header">
  <h1>Surat & Dokumen</h1>
  <p>Kelola pengajuan surat administrasi mahasiswa KP</p>
</div>

@php $pending = $surats->where('status','pending')->count(); @endphp
@if($pending > 0)
  <div class="alert alert-warning">⚠️ Terdapat <strong>{{ $pending }} pengajuan surat</strong> yang menunggu persetujuan Anda.</div>
@endif

<div class="card">
  <div class="card-header"><h3>Semua Pengajuan Surat</h3><p>{{ $surats->count() }} pengajuan</p></div>
  <table>
    <thead><tr><th>Mahasiswa</th><th>Jenis Surat</th><th>Keterangan</th><th>Tanggal Ajuan</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
      @forelse($surats as $s)
      <tr>
        <td>
          <strong>{{ $s->mahasiswa?->nama }}</strong><br>
          <code>{{ $s->mahasiswa?->nim }}</code>
        </td>
        <td>
          <span class="pill {{ $s->jenis === 'permohonan' ? 'pill-seminar' : ($s->jenis === 'keterangan' ? 'pill-disetujui' : 'pill-proses') }}">
            {{ ucfirst($s->jenis) }}
          </span>
        </td>
        <td style="font-size:12px;color:var(--muted);max-width:200px">{{ $s->keterangan ? Str::limit($s->keterangan, 50) : '–' }}</td>
        <td style="font-size:12px;color:var(--muted)">{{ $s->created_at->format('d/m/Y H:i') }}</td>
        <td><span class="pill pill-{{ $s->status }}">{{ ucfirst($s->status) }}</span></td>
        <td>
          @if($s->status === 'pending')
            <form method="POST" action="{{ route('admin.surat.approve', $s) }}" style="display:inline">
              @csrf
              <button type="submit" class="btn btn-success btn-xs">✅ Setujui</button>
            </form>
            <button class="btn btn-danger btn-xs" style="margin-left:4px" onclick="openReject({{ $s->id }})">❌ Tolak</button>
          @else
            <span style="font-size:12px;color:var(--muted)">{{ $s->catatan_admin ? Str::limit($s->catatan_admin, 30) : 'Sudah diproses' }}</span>
          @endif
        </td>
      </tr>
      @empty
        <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:24px">Belum ada pengajuan surat.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- MODAL TOLAK --}}
<div class="modal-bg" id="modalReject">
  <div class="modal-box">
    <h3>❌ Tolak Pengajuan Surat</h3>
    <form method="POST" id="rejectForm">
      @csrf
      <div class="form-group">
        <label>Alasan Penolakan</label>
        <textarea name="catatan_admin" class="form-control" rows="3" placeholder="Jelaskan alasan penolakan..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modalReject')">Batal</button>
        <button type="submit" class="btn btn-danger">Tolak Surat</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function openReject(id) {
  document.getElementById('rejectForm').action = `/admin/surat/${id}/reject`;
  openModal('modalReject');
}
</script>
@endpush
@endsection