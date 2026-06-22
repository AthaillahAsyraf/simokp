@extends('layouts.app')
@section('title', $chat->subjek)
@section('content')

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
  <div>
    <a href="{{ route('dosen.chat.index') }}" style="font-size:13px;color:var(--gray-400);text-decoration:none">← Kembali ke Chat</a>
    <h1 style="margin-top:4px">{{ $chat->subjek }}</h1>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;font-size:12px;color:var(--gray-500)">
      @php $tipeLabel = ['monitoring'=>'📋 Monitoring','pengaduan'=>'⚠️ Pengaduan','umum'=>'💬 Umum']; @endphp
      <span>{{ $tipeLabel[$chat->tipe] ?? $chat->tipe }}</span>
      <span>·</span>
      <span>Dari: <strong>{{ $chat->instansi->nama }}</strong></span>
      @if($chat->mahasiswa)
        <span>·</span>
        <span>Mahasiswa: <strong>{{ $chat->mahasiswa->nama }} ({{ $chat->mahasiswa->nim }})</strong></span>
      @endif
      @if($chat->status === 'closed')
        <span style="background:#dcfce7;color:#16a34a;padding:2px 8px;border-radius:99px">✅ Selesai</span>
      @endif
    </div>
  </div>

  @if($chat->status === 'open')
  <form action="{{ route('dosen.chat.close', $chat) }}" method="POST" onsubmit="return confirm('Tandai chat ini sebagai selesai?')">
    @csrf @method('PATCH')
    <button class="btn btn-outline btn-sm">✅ Tandai Selesai</button>
  </form>
  @endif
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card" style="margin-bottom:16px">
  <div class="card-body" id="chat-box" style="display:flex;flex-direction:column;gap:12px;max-height:480px;overflow-y:auto;padding:16px">
    @forelse($pesans as $p)
    @php $isMe = $p->pengirim_role === 'dosen'; @endphp
    <div style="display:flex;flex-direction:column;align-items:{{ $isMe?'flex-end':'flex-start' }}">
      <div style="font-size:11px;color:var(--gray-400);margin-bottom:3px">
        {{ $isMe ? $dosen->nama : $chat->instansi->nama }}
        · {{ $p->created_at->format('d M Y, H:i') }}
      </div>
      <div style="max-width:80%;padding:10px 14px;
        border-radius:{{ $isMe?'16px 16px 4px 16px':'16px 16px 16px 4px' }};
        background:{{ $isMe?'var(--blue-600)':'var(--white)' }};
        color:{{ $isMe?'#fff':'inherit' }};
        border:{{ $isMe?'none':'1px solid var(--gray-200)' }};
        font-size:14px;line-height:1.5;white-space:pre-wrap">{{ $p->pesan }}</div>
    </div>
    @empty
      <p style="text-align:center;color:var(--gray-400);font-size:13px">Belum ada pesan.</p>
    @endforelse
  </div>
</div>

@if($chat->status === 'open')
<div class="card">
  <div class="card-body">
    <form action="{{ route('dosen.chat.reply', $chat) }}" method="POST">
      @csrf
      <div style="display:flex;gap:10px;align-items:flex-end">
        <textarea name="pesan" rows="3" required placeholder="Tulis balasan untuk instansi..."
          class="form-control" style="flex:1;resize:none">{{ old('pesan') }}</textarea>
        <button type="submit" class="btn btn-primary" style="white-space:nowrap">Kirim ✉️</button>
      </div>
      @error('pesan')<div style="color:var(--red-500);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
    </form>
  </div>
</div>
@else
  <div class="alert" style="background:var(--gray-100);color:var(--gray-500);text-align:center">Chat ini sudah ditutup.</div>
@endif

<script>
  const box = document.getElementById('chat-box');
  if (box) box.scrollTop = box.scrollHeight;
</script>
@endsection