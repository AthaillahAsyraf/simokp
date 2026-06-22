<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Daftar — SiMoKP Unila</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#faf5ff 0%,#eff6ff 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.card{background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:40px;width:480px;box-shadow:0 10px 40px rgba(0,0,0,.1)}
.header{text-align:center;margin-bottom:28px}
.logo{width:48px;height:48px;background:linear-gradient(135deg,#7c3aed,#a855f7);border-radius:14px;margin:0 auto 14px;display:flex;align-items:center;justify-content:center;font-size:24px}
h2{font-size:20px;font-weight:800;color:#0f172a}
.sub{font-size:13px;color:#64748b;margin-top:4px}
label{font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:5px}
input{width:100%;border:1.5px solid #e2e8f0;border-radius:7px;padding:9px 11px;font-size:13px;font-family:'Inter',sans-serif;color:#0f172a;transition:all .15s;outline:none}
input:focus{border-color:#a855f7;box-shadow:0 0 0 3px rgba(168,85,247,.1)}
/* Tampil merah hanya setelah user pernah mengisi field (via JS class) */
input.touched:invalid{border-color:#fca5a5;background:#fef9f9}
.fg{margin-bottom:12px}
.hint{font-size:11px;color:#94a3b8;margin-top:4px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.btn{width:100%;padding:12px;border-radius:8px;border:none;background:#7c3aed;color:#fff;font-size:14px;font-weight:700;cursor:pointer;font-family:'Inter',sans-serif;transition:background .15s;margin-top:6px}
.btn:hover{background:#6d28d9}
.err{background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;font-size:13px;color:#dc2626;margin-bottom:14px}
.login-link{text-align:center;margin-top:16px;font-size:13px;color:#64748b}
.login-link a{color:#7c3aed;font-weight:600}
</style>
</head>
<body>
<div class="card">
  <div class="header">
    <div class="logo">🎓</div>
    <h2>Daftar Akun Mahasiswa</h2>
    <p class="sub">SiMoKP — Sistem Monitoring Kerja Praktik</p>
  </div>

  @if($errors->any())
    <div class="err">{{ $errors->first() }}</div>
  @endif

  <form method="POST" action="{{ route('register.post') }}">
    @csrf
    <div class="grid">

      {{-- NIM: hanya angka --}}
      <div class="fg">
        <label>NIM *</label>
        <input type="text"
               name="nim"
               value="{{ old('nim') }}"
               placeholder="2021060..."
               inputmode="numeric"
               pattern="\d+"
               title="NIM hanya boleh berisi angka"
               oninput="onlyDigits(this)"
               onblur="this.classList.add('touched')"
               required>
        <div class="hint">Angka saja, tanpa spasi</div>
      </div>

      {{-- Angkatan: tepat 4 digit --}}
      <div class="fg">
        <label>Angkatan *</label>
        <input type="text"
               name="angkatan"
               value="{{ old('angkatan') }}"
               placeholder="2021"
               inputmode="numeric"
               pattern="\d{4}"
               maxlength="4"
               title="Angkatan harus tepat 4 digit angka"
               oninput="onlyDigits(this)"
               onblur="this.classList.add('touched')"
               required>
        <div class="hint">4 digit, contoh: 2021</div>
      </div>

    </div>

    {{-- Nama Lengkap: hanya huruf + spasi --}}
    <div class="fg">
      <label>Nama Lengkap *</label>
      <input type="text"
             name="name"
             value="{{ old('name') }}"
             placeholder="Sesuai KTP / KTM"
             pattern="[A-Za-z\s]+"
             title="Nama hanya boleh berisi huruf dan spasi"
             oninput="onlyLetters(this)"
             onblur="this.classList.add('touched')"
             required>
      <div class="hint">Huruf dan spasi saja, tanpa angka atau simbol</div>
    </div>

    <div class="fg">
      <label>Email Mahasiswa *</label>
      <input type="email"
             name="email"
             value="{{ old('email') }}"
             placeholder="nim@students.cs.unila.ac.id"
             onblur="this.classList.add('touched')"
             required>
    </div>

    <div class="grid">
      <div class="fg">
        <label>Password *</label>
        <input type="password"
               name="password"
               placeholder="Min. 8 karakter"
               minlength="8"
               onblur="this.classList.add('touched')"
               required>
      </div>
      <div class="fg">
        <label>Konfirmasi *</label>
        <input type="password"
               name="password_confirmation"
               placeholder="Ulangi"
               minlength="8"
               onblur="this.classList.add('touched')"
               required>
      </div>
    </div>

    <button type="submit" class="btn">Daftar Sekarang</button>
  </form>
  <div class="login-link">Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a></div>
</div>

<script>
  // Blokir karakter non-angka pada field NIM & Angkatan
  function onlyDigits(el) {
    el.value = el.value.replace(/\D/g, '');
  }

  // Blokir angka & simbol pada field Nama (izinkan huruf + spasi)
  function onlyLetters(el) {
    el.value = el.value.replace(/[^A-Za-z\s]/g, '');
  }
</script>
</body>
</html>