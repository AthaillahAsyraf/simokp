@extends('layouts.app')
@section('title', $chat->subjek)
@section('content')

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
  <div>
    <a href="{{ route('instansi.chat.index') }}" style="font-size:13px;color:var(--muted);text-decoration:none">← Kembali ke Chat</a>
    <h1 style="margin-top:4px">{{ $chat->subjek }}</h1>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;font-size:12px;color:var(--muted)">
      @php
        $tipeLabel = ['monitoring'=>'📋 Monitoring','pengaduan'=>'⚠️ Pengaduan','umum'=>'💬 Umum'];
      @endphp
      <span>{{ $tipeLabel[$chat->tipe] ?? $chat->tipe }}</span>
      <span>·</span>
      <span>Dosen: <strong>{{ $chat->dosen->nama }}</strong></span>
      @if($chat->mahasiswa)
        <span>·</span>
        <span>Mahasiswa: <strong>{{ $chat->mahasiswa->nama }} ({{ $chat->mahasiswa->nim }})</strong></span>
      @endif
      @if($chat->status === 'closed')
        <span style="background:#f3f4f6;color:#6b7280;padding:2px 8px;border-radius:99px">✅ Selesai</span>
      @endif
    </div>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- Histori Pesan --}}
<div class="card" style="margin-bottom:16px">
  <div class="card-body" id="chat-box" style="display:flex;flex-direction:column;gap:12px;max-height:480px;overflow-y:auto;padding:16px">
    @forelse($pesans as $p)
    @php $isMe = $p->pengirim_role === 'instansi'; @endphp
    <div style="display:flex;flex-direction:column;align-items:{{ $isMe?'flex-end':'flex-start' }}">
      <div style="font-size:11px;color:var(--muted);margin-bottom:3px">
        {{ $isMe ? $instansi->nama : $chat->dosen->nama }}
        · {{ $p->created_at->format('d M Y, H:i') }}
      </div>
      <div style="max-width:80%;padding:10px 14px;border-radius:{{ $isMe?'16px 16px 4px 16px':'16px 16px 16px 4px' }};
        background:{{ $isMe?'var(--primary,#4f46e5)':'var(--surface)' }};
        color:{{ $isMe?'#fff':'inherit' }};
        border:{{ $isMe?'none':'1px solid var(--border)' }};
        font-size:14px;line-height:1.5;white-space:pre-wrap">{{ $p->pesan }}</div>
    </div>
    @empty
      <p style="text-align:center;color:var(--muted);font-size:13px">Belum ada pesan.</p>
    @endforelse
  </div>
</div>

{{-- Form Balas --}}
@if($chat->status === 'open')
<div class="card">
  <div class="card-body">
    <form action="{{ route('instansi.chat.reply', $chat) }}" method="POST">
      @csrf
      <div style="display:flex;gap:10px;align-items:flex-end">
        <textarea name="pesan" rows="3" required placeholder="Tulis balasan..."
          style="flex:1;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;background:var(--surface);resize:none">{{ old('pesan') }}</textarea>
        <button type="submit" class="btn btn-primary" style="white-space:nowrap">Kirim ✉️</button>
      </div>
      @error('pesan')<div style="color:red;font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
    </form>
  </div>
</div>
@else
  <div class="alert" style="background:#f3f4f6;color:#6b7280;text-align:center">Chat ini sudah ditutup oleh dosen.</div>
@endif

<script>
  // Auto-scroll ke bawah
  const box = document.getElementById('chat-box');
  if (box) box.scrollTop = box.scrollHeight;
</script>
@endsection