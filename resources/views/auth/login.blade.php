{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Login — SiMoKP Unila</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#eff6ff 0%,#f0fdf4 50%,#faf5ff 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.container{display:grid;grid-template-columns:1fr 1fr;gap:0;max-width:900px;width:100%;border-radius:20px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.12)}
/* Left panel */
.left{background:linear-gradient(150deg,#1d4ed8 0%,#2563eb 50%,#3b82f6 100%);padding:48px 40px;display:flex;flex-direction:column;justify-content:space-between;color:#fff}
.brand{display:flex;align-items:center;gap:12px;margin-bottom:48px}
.brand-icon{width:44px;height:44px;background:rgba(255,255,255,.2);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px}
.brand h1{font-size:22px;font-weight:800}
.brand p{font-size:12px;opacity:.8;margin-top:2px}
.tagline h2{font-size:28px;font-weight:800;line-height:1.3;margin-bottom:16px}
.tagline p{font-size:14px;opacity:.8;line-height:1.6}
.roles-list{margin-top:32px;display:grid;gap:8px}
.role-item{display:flex;align-items:center;gap:10px;background:rgba(255,255,255,.12);border-radius:10px;padding:10px 14px;font-size:13px;font-weight:500}
.role-item span:first-child{font-size:18px}
/* Right panel */
.right{background:#fff;padding:48px 40px}
.right h2{font-size:22px;font-weight:800;color:#0f172a;margin-bottom:4px}
.right .sub{font-size:13px;color:#64748b;margin-bottom:28px}
label{font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:5px}
input{width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:10px 12px;font-size:14px;font-family:'Inter',sans-serif;color:#0f172a;transition:all .15s;outline:none}
input:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.12)}
.fg{margin-bottom:16px}
.btn-login{width:100%;padding:12px;border-radius:8px;border:none;background:#2563eb;color:#fff;font-size:14px;font-weight:700;cursor:pointer;font-family:'Inter',sans-serif;transition:background .15s;margin-top:4px}
.btn-login:hover{background:#1d4ed8}
.err{background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;font-size:13px;color:#dc2626;margin-bottom:16px}
.demo-box{margin-top:24px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px}
.demo-box h4{font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px}
.demo-row{display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid #f1f5f9;font-size:12px}
.demo-row:last-child{border:none}
.demo-role{font-weight:600;color:#334155}
.demo-email{color:#64748b;font-family:monospace}
.reg-link{text-align:center;margin-top:16px;font-size:13px;color:#64748b}
.reg-link a{color:#2563eb;font-weight:600}
</style>
</head>
<body>
<div class="container">
  <div class="left">
    <div>
      <div class="brand">
        <div class="brand-icon">🎓</div>
        <div>
          <h1>SiMoKP</h1>
          <p>Ilmu Komputer — Unila</p>
        </div>
      </div>
      <div class="tagline">
        <h2>Sistem Monitoring Progress Kerja Praktik</h2>
        <p>Platform terpadu untuk memantau perkembangan KP mahasiswa secara real-time dengan 4 aktor yang terintegrasi.</p>
      </div>
    </div>
    <div style="font-size:12px;opacity:.6;margin-top:32px">© 2026 Ilmu Komputer Universitas Lampung</div>
  </div>

  <div class="right">
    <h2>Selamat Datang 👋</h2>
    <p class="sub">Masuk ke akun Anda untuk mulai monitoring</p>

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
      <button type="submit" class="btn-login">Masuk ke Sistem →</button>
    </form>

    <div class="demo-box">
      <h4>🔑 Akun Demo — Password: <code style="background:#e2e8f0;padding:1px 5px;border-radius:3px;font-size:11px">password</code></h4>
      <div class="demo-row"><span class="demo-role">Admin</span><span class="demo-email">admin@cs.unila.ac.id</span></div>
      <div class="demo-row"><span class="demo-role">Dosen</span><span class="demo-email">ahmad.rifai@cs.unila.ac.id</span></div>
      <div class="demo-row"><span class="demo-role">Instansi</span><span class="demo-email">hrd@teknusantara.co.id</span></div>
      <div class="demo-row"><span class="demo-role">Mahasiswa</span><span class="demo-email">2017061001@students.cs.unila.ac.id</span></div>
    </div>

    <div class="reg-link">Mahasiswa baru? <a href="{{ route('register') }}">Daftar di sini</a></div>
  </div>
</div>
</body>
</html>