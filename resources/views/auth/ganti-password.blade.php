@extends('layouts.app')
@section('title','Ganti Password')
@section('content')

<div class="page-header">
  <h1>🔐 Ganti Password</h1>
  <p>Perbarui password akun Anda untuk keamanan yang lebih baik.</p>
</div>

<div class="card" style="max-width:480px">
  <div class="card-body">

   @if(session('success'))
      <div class="alert alert-success" style="margin-bottom:18px">
        ✅ {{ session('success') }}
      </div>
    @endif

    @if(session('force_ganti_password'))
      <div class="alert alert-warning" style="margin-bottom:18px">
        🔒 {{ session('force_ganti_password') }}
      </div>
    @endif

    <form method="POST" action="{{ route('ganti-password.post') }}">
      @csrf

      {{-- Password Lama --}}
      <div class="form-group">
        <label class="form-label">Password Lama <span style="color:var(--red-500)">*</span></label>
        <input type="password" name="password_lama" required
          class="form-control @error('password_lama') is-invalid @enderror"
          placeholder="Masukkan password lama">
        @error('password_lama')
          <div style="color:var(--red-500);font-size:12px;margin-top:4px">⚠️ {{ $message }}</div>
        @enderror
      </div>

      {{-- Password Baru --}}
      <div class="form-group">
        <label class="form-label">Password Baru <span style="color:var(--red-500)">*</span></label>
        <input type="password" name="password_baru" required
          class="form-control @error('password_baru') is-invalid @enderror"
          placeholder="Minimal 8 karakter">
        @error('password_baru')
          <div style="color:var(--red-500);font-size:12px;margin-top:4px">⚠️ {{ $message }}</div>
        @enderror
      </div>

      {{-- Konfirmasi --}}
      <div class="form-group" style="margin-bottom:20px">
        <label class="form-label">Konfirmasi Password Baru <span style="color:var(--red-500)">*</span></label>
        <input type="password" name="password_baru_confirmation" required
          class="form-control"
          placeholder="Ulangi password baru">
      </div>

      {{-- Tips --}}
      <div class="alert alert-info" style="margin-bottom:20px;font-size:12px">
        💡 Tips password yang kuat: minimal 8 karakter, kombinasi huruf besar, huruf kecil, angka, dan simbol.
      </div>

      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary">🔐 Simpan Password</button>
        <a href="javascript:history.back()" class="btn btn-outline">Batal</a>
      </div>
    </form>

  </div>
</div>
@endsection