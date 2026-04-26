<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar – SiMoKP Unila</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#0f1117;color:#e8eaf2;min-height:100vh;display:flex;align-items:center;justify-content:center;
  background:radial-gradient(ellipse at 80% 20%,rgba(236,72,153,.12),transparent 55%),#0f1117}
.card{background:#191c25;border:1px solid #2a2f45;border-radius:20px;padding:44px;width:460px;box-shadow:0 40px 80px rgba(0,0,0,.5)}
.logo{text-align:center;margin-bottom:28px}
h1{font-size:24px;font-weight:800}
h1 span{color:#6366f1}
.sub{color:#7b82a0;font-size:13px;margin-top:5px}
label{font-size:12px;font-weight:600;color:#7b82a0;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:5px}
input{width:100%;background:#0f1117;border:1.5px solid #2a2f45;border-radius:9px;padding:10px 13px;color:#e8eaf2;font-size:14px;font-family:inherit;transition:border-color .2s}
input:focus{outline:none;border-color:#ec4899}
.fg{margin-bottom:13px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.btn{width:100%;padding:13px;border-radius:9px;border:none;font-size:15px;font-weight:700;cursor:pointer;background:#ec4899;color:#fff;font-family:inherit;margin-top:4px}
.btn:hover{opacity:.88}
.err{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:9px;padding:10px 14px;font-size:13px;color:#f87171;margin-bottom:14px}
.link{text-align:center;margin-top:16px;font-size:13px;color:#7b82a0}
.link a{color:#f472b6;font-weight:600}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <h1>SiMo<span>KP</span> — Daftar</h1>
    <p class="sub">Pendaftaran Akun Mahasiswa KP</p>
  </div>

  @if($errors->any())
    <div class="err">{{ $errors->first() }}</div>
  @endif

  <form method="POST" action="{{ route('register.post') }}">
    @csrf
    <div class="grid">
      <div class="fg">
        <label>NIM</label>
        <input type="text" name="nim" value="{{ old('nim') }}" placeholder="20170610xx" required>
      </div>
      <div class="fg">
        <label>Angkatan</label>
        <input type="text" name="angkatan" value="{{ old('angkatan') }}" placeholder="2021" required>
      </div>
    </div>
    <div class="fg">
      <label>Nama Lengkap</label>
      <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama sesuai KTP" required>
    </div>
    <div class="fg">
      <label>Email Mahasiswa</label>
      <input type="email" name="email" value="{{ old('email') }}" placeholder="nim@students.cs.unila.ac.id" required>
    </div>
    <div class="grid">
      <div class="fg">
        <label>Password</label>
        <input type="password" name="password" placeholder="Min. 8 karakter" required>
      </div>
      <div class="fg">
        <label>Konfirmasi</label>
        <input type="password" name="password_confirmation" placeholder="Ulangi password" required>
      </div>
    </div>
    <button type="submit" class="btn">Daftar Sekarang</button>
  </form>
  <div class="link">Sudah punya akun? <a href="{{ route('login') }}">Login</a></div>
</div>
</body>
</html>