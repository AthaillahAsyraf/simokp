<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login – SiMoKP Unila</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#0f1117;color:#e8eaf2;min-height:100vh;display:flex;align-items:center;justify-content:center;
  background:radial-gradient(ellipse at 20% 50%,rgba(99,102,241,.15),transparent 60%),
             radial-gradient(ellipse at 80% 20%,rgba(16,185,129,.1),transparent 50%),#0f1117}
.card{background:#191c25;border:1px solid #2a2f45;border-radius:20px;padding:44px;width:420px;box-shadow:0 40px 80px rgba(0,0,0,.5)}
.logo{text-align:center;margin-bottom:32px}
.badge{display:inline-flex;align-items:center;gap:6px;background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.3);border-radius:100px;padding:5px 14px;font-size:12px;color:#818cf8;margin-bottom:14px}
h1{font-size:28px;font-weight:800}
h1 span{color:#6366f1}
.sub{color:#7b82a0;font-size:13px;margin-top:5px}
label{font-size:12px;font-weight:600;color:#7b82a0;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:5px}
input{width:100%;background:#0f1117;border:1.5px solid #2a2f45;border-radius:9px;padding:11px 13px;color:#e8eaf2;font-size:14px;font-family:inherit;transition:border-color .2s}
input:focus{outline:none;border-color:#6366f1}
.fg{margin-bottom:16px}
.btn{width:100%;padding:13px;border-radius:9px;border:none;font-size:15px;font-weight:700;cursor:pointer;background:#6366f1;color:#fff;font-family:inherit;transition:opacity .2s;margin-top:4px}
.btn:hover{opacity:.88}
.err{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:9px;padding:10px 14px;font-size:13px;color:#f87171;margin-bottom:16px}
.reg-link{text-align:center;margin-top:18px;font-size:13px;color:#7b82a0}
.reg-link a{color:#818cf8;font-weight:600}
.accounts{margin-top:20px;background:rgba(255,255,255,.03);border:1px solid #2a2f45;border-radius:10px;padding:14px;font-size:12px;color:#7b82a0}
.accounts strong{color:#e8eaf2;display:block;margin-bottom:8px}
.accounts code{font-family:monospace;color:#818cf8}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="badge">🎓 Ilmu Komputer Unila</div>
    <h1>SiMo<span>KP</span></h1>
    <p class="sub">Sistem Monitoring Progress Kerja Praktik</p>
  </div>

  @if($errors->any())
    <div class="err">{{ $errors->first() }}</div>
  @endif

  <form method="POST" action="{{ route('login.post') }}">
    @csrf
    <div class="fg">
      <label>Email</label>
      <input type="email" name="email" value="{{ old('email') }}" placeholder="email@cs.unila.ac.id" required autofocus>
    </div>
    <div class="fg">
      <label>Password</label>
      <input type="password" name="password" placeholder="••••••••" required>
    </div>
    <button type="submit" class="btn">Masuk ke Sistem →</button>
  </form>

  <div class="reg-link">
    Mahasiswa baru? <a href="{{ route('register') }}">Daftar di sini</a>
  </div>

  <div class="accounts">
    <strong>🔑 Akun Demo</strong>
    Admin: <code>admin@cs.unila.ac.id</code><br>
    Dosen: <code>ahmad.rifai@cs.unila.ac.id</code><br>
    Instansi: <code>hrd@teknusantara.co.id</code><br>
    Mahasiswa: <code>2017061001@students.cs.unila.ac.id</code><br>
    Password semua: <code>password</code>
  </div>
</div>
</body>
</html>