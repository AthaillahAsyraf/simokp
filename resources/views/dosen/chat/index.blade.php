@extends('layouts.app')
@section('title','Pesan dari Instansi')
@section('content')

<div class="page-header">
  <h1>💬 Pesan dari Instansi</h1>
  <p>Laporan monitoring & pengaduan dari instansi tempat mahasiswa bimbingan Anda.</p>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($chats->isEmpty())
  <div class="card" style="text-align:center;padding:40px">
    <div style="font-size:48px;margin-bottom:12px">📭</div>
    <p style="color:var(--gray-400)">Belum ada pesan masuk dari instansi.</p>
  </div>
@else
  <div class="card">
    <div class="card-body" style="padding:0">
      @foreach($chats as $chat)
      @php
        $warna = ['monitoring'=>'blue','pengaduan'=>'red','umum'=>'gray'][$chat->tipe] ?? 'gray';
        $label = ['monitoring'=>'📋 Monitoring','pengaduan'=>'⚠️ Pengaduan','umum'=>'💬 Umum'][$chat->tipe] ?? $chat->tipe;
      @endphp
      <a href="{{ route('dosen.chat.show', $chat) }}"
         style="display:flex;align-items:center;gap:14px;padding:14px 18px;border-bottom:1px solid var(--gray-200);text-decoration:none;color:inherit"
         onmouseover="this.style.background='var(--gray-50)'" onmouseout="this.style.background=''">

        <div style="width:42px;height:42px;border-radius:50%;background:#0ea5e9;display:flex;align-items:center;justify-content:center;font-size:18px;color:#fff;flex-shrink:0">
          🏢
        </div>

        <div style="flex:1;min-width:0">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px;flex-wrap:wrap">
            <span style="font-weight:700;font-size:14px">{{ $chat->instansi->nama }}</span>
            <span style="font-size:11px;padding:2px 8px;border-radius:99px;
              background:{{ $warna==='red'?'#fee2e2':($warna==='blue'?'#dbeafe':'#f3f4f6') }};
              color:{{ $warna==='red'?'#dc2626':($warna==='blue'?'#2563eb':'#6b7280') }}">
              {{ $label }}
            </span>
            @if($chat->status === 'closed')
              <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:#dcfce7;color:#16a34a">✅ Selesai</span>
            @endif
          </div>
          <div style="font-size:13px;font-weight:600">{{ $chat->subjek }}</div>
          @if($chat->mahasiswa)
            <div style="font-size:11px;color:var(--gray-400)">Mahasiswa: {{ $chat->mahasiswa->nama }}</div>
          @endif
          @if($chat->pesanTerakhir)
            <div style="font-size:12px;color:var(--gray-400);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:400px">
              {{ Str::limit($chat->pesanTerakhir->pesan, 60) }}
            </div>
          @endif
        </div>

        <div style="text-align:right;flex-shrink:0">
          @if($chat->unread > 0)
            <span style="background:#ef4444;color:#fff;font-size:11px;font-weight:700;padding:2px 8px;border-radius:99px">
              {{ $chat->unread }} baru
            </span>
          @endif
          <div style="font-size:11px;color:var(--gray-400);margin-top:4px">{{ $chat->updated_at->diffForHumans() }}</div>
        </div>
      </a>
      @endforeach
    </div>
  </div>
@endif
@endsection