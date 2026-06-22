@extends('layouts.app')
@section('title','Chat dengan Dosen')
@section('content')

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
  <div>
    <h1>💬 Chat dengan Dosen</h1>
    <p>Laporkan kegiatan monitoring atau sampaikan pengaduan terkait mahasiswa KP.</p>
  </div>
  <a href="{{ route('instansi.chat.create') }}" class="btn btn-primary">+ Pesan Baru</a>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($chats->isEmpty())
  <div class="card" style="text-align:center;padding:40px">
    <div style="font-size:48px;margin-bottom:12px">💬</div>
    <p style="color:var(--muted)">Belum ada percakapan. Mulai dengan klik <strong>+ Pesan Baru</strong>.</p>
  </div>
@else
  <div class="card">
    <div class="card-body" style="padding:0">
      @foreach($chats as $chat)
      @php
        $tipeBadge = ['monitoring'=>'blue','pengaduan'=>'red','umum'=>'gray'];
        $tipeLabel = ['monitoring'=>'📋 Monitoring','pengaduan'=>'⚠️ Pengaduan','umum'=>'💬 Umum'];
        $warna = $tipeBadge[$chat->tipe] ?? 'gray';
      @endphp
      <a href="{{ route('instansi.chat.show', $chat) }}"
         style="display:flex;align-items:center;gap:14px;padding:14px 18px;border-bottom:1px solid var(--border);text-decoration:none;color:inherit;transition:background .15s"
         onmouseover="this.style.background='var(--surface)'" onmouseout="this.style.background=''">

        {{-- Avatar dosen --}}
        <div style="width:42px;height:42px;border-radius:50%;background:var(--dosen,#6366f1);display:flex;align-items:center;justify-content:center;font-size:18px;color:#fff;flex-shrink:0">
          👨‍🏫
        </div>

        <div style="flex:1;min-width:0">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px">
            <span style="font-weight:700;font-size:14px">{{ $chat->dosen->nama }}</span>
            <span style="font-size:11px;padding:2px 8px;border-radius:99px;
              background:{{ $warna==='red'?'#fee2e2':($warna==='blue'?'#dbeafe':'#f3f4f6') }};
              color:{{ $warna==='red'?'#dc2626':($warna==='blue'?'#2563eb':'#6b7280') }}">
              {{ $tipeLabel[$chat->tipe] ?? $chat->tipe }}
            </span>
            @if($chat->status === 'closed')
              <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:#f3f4f6;color:#6b7280">✅ Selesai</span>
            @endif
          </div>
          <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $chat->subjek }}</div>
          @if($chat->mahasiswa)
            <div style="font-size:11px;color:var(--muted)">Mahasiswa: {{ $chat->mahasiswa->nama }} ({{ $chat->mahasiswa->nim }})</div>
          @endif
          @if($chat->pesanTerakhir)
            <div style="font-size:12px;color:var(--muted);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:400px">
              {{ Str::limit($chat->pesanTerakhir->pesan, 60) }}
            </div>
          @endif
        </div>

        <div style="text-align:right;flex-shrink:0">
          @if($chat->unread > 0)
            <span style="background:var(--primary,#4f46e5);color:#fff;font-size:11px;font-weight:700;padding:2px 8px;border-radius:99px">
              {{ $chat->unread }} baru
            </span>
          @endif
          <div style="font-size:11px;color:var(--muted);margin-top:4px">
            {{ $chat->updated_at->diffForHumans() }}
          </div>
        </div>
      </a>
      @endforeach
    </div>
  </div>
@endif
@endsection