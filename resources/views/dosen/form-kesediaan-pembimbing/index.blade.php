@extends('layouts.app')
@section('title', 'Kesediaan Membimbing')
@section('content')
<div class="page-header"><h1>Kesediaan Membimbing</h1><p>Setujui form yang telah diteruskan oleh mahasiswa bimbingan Anda.</p></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="card"><div class="table-wrap"><table><thead><tr><th>Mahasiswa</th><th>Instansi</th><th>Status</th><th>Dikirim</th><th>Surat</th><th>Aksi</th></tr></thead><tbody>
@forelse($forms as $form)<tr><td><strong>{{ $form->mahasiswa->nama }}</strong><br><small>{{ $form->mahasiswa->nim }}</small></td><td>{{ $form->mahasiswa->instansi?->nama ?? '-' }}</td><td><span class="badge {{ $form->status === 'disetujui' ? 'badge-selesai' : ($form->status === 'diteruskan' ? 'badge-proses' : 'badge-belum') }}">{{ ucfirst($form->status) }}</span></td><td>{{ $form->diteruskan_at?->format('d M Y H:i') ?? '-' }}</td><td><a class="btn btn-outline btn-sm" href="{{ route('dosen.form-kesediaan-pembimbing.show', $form) }}" target="_blank">Lihat Surat</a></td><td>@if($form->status === 'diteruskan')<form method="POST" action="{{ route('dosen.form-kesediaan-pembimbing.setujui', $form) }}">@csrf<button class="btn btn-success btn-sm">Setujui Kesediaan</button></form>@else <span class="text-muted">{{ $form->status === 'disetujui' ? 'Sudah disetujui' : 'Belum diteruskan' }}</span>@endif</td></tr>@empty<tr><td colspan="6" style="text-align:center;padding:28px">Belum ada form kesediaan pembimbing.</td></tr>@endforelse
</tbody></table></div></div>
@endsection
