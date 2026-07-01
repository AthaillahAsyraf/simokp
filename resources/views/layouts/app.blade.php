<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','SiMoKP') — Ilmu Komputer Unila</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<style>
:root{
  --white:#ffffff;
  --gray-50:#f8fafc;
  --gray-100:#f1f5f9;
  --gray-200:#e2e8f0;
  --gray-300:#cbd5e1;
  --gray-400:#94a3b8;
  --gray-500:#64748b;
  --gray-600:#475569;
  --gray-700:#334155;
  --gray-800:#1e293b;
  --gray-900:#0f172a;

  --blue-50:#eff6ff;
  --blue-100:#dbeafe;
  --blue-500:#3b82f6;
  --blue-600:#2563eb;
  --blue-700:#1d4ed8;

  --green-50:#f0fdf4;
  --green-100:#dcfce7;
  --green-500:#22c55e;
  --green-600:#16a34a;
  --green-700:#15803d;

  --amber-50:#fffbeb;
  --amber-100:#fef3c7;
  --amber-500:#f59e0b;
  --amber-600:#d97706;

  --red-50:#fef2f2;
  --red-100:#fee2e2;
  --red-500:#ef4444;
  --red-600:#dc2626;

  --purple-50:#faf5ff;
  --purple-100:#f3e8ff;
  --purple-200:#e9d5ff;
  --purple-300:#d8b4fe;
  --purple-400:#c084fc;
  --purple-500:#a855f7;
  --purple-600:#9333ea;

  /* role colors */
  --c-admin:   var(--blue-600);
  --c-dosen:   var(--green-600);
  --c-instansi:var(--amber-600);
  --c-mhs:     var(--purple-600);

  --sidebar-w: 248px;
  --radius:    10px;
  --shadow:    0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
  --shadow-md: 0 4px 6px -1px rgba(0,0,0,.07), 0 2px 4px -2px rgba(0,0,0,.05);
  --shadow-lg: 0 10px 15px -3px rgba(0,0,0,.08), 0 4px 6px -4px rgba(0,0,0,.05);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--gray-100);color:var(--gray-800);display:flex;min-height:100vh;font-size:14px;line-height:1.5}
a{color:inherit;text-decoration:none}

/* ── SIDEBAR ─────────────────────────────────────────────────── */
.sidebar{
  width:var(--sidebar-w);background:var(--white);
  border-right:1px solid var(--gray-200);
  display:flex;flex-direction:column;
  position:fixed;left:0;top:0;bottom:0;z-index:100;
  box-shadow:var(--shadow-md);
}
.sb-logo{
  padding:20px 20px 16px;
  border-bottom:1px solid var(--gray-100);
  display:flex;align-items:center;gap:10px;
}
.sb-logo-icon{
  width:36px;height:36px;border-radius:9px;
  background:linear-gradient(135deg,var(--blue-600),var(--blue-500));
  display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:16px;flex-shrink:0;
}
.sb-logo h2{font-size:15px;font-weight:800;color:var(--gray-900);letter-spacing:-.3px}
.sb-logo p{font-size:11px;color:var(--gray-400);margin-top:1px}

.sb-role-badge{
  margin:12px 14px 6px;padding:7px 12px;border-radius:8px;
  font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;
  display:flex;align-items:center;gap:6px;
}
.role-admin   {background:var(--blue-50);color:var(--blue-700);border:1px solid var(--blue-100)}
.role-dosen   {background:var(--green-50);color:var(--green-700);border:1px solid var(--green-100)}
.role-instansi{background:var(--amber-50);color:var(--amber-600);border:1px solid var(--amber-100)}
.role-mahasiswa{background:var(--purple-50);color:var(--purple-600);border:1px solid var(--purple-100)}

.sb-nav{flex:1;padding:6px 10px;overflow-y:auto}
.nav-section{font-size:10px;font-weight:700;color:var(--gray-400);text-transform:uppercase;letter-spacing:.8px;padding:14px 8px 5px}
.nav-item{
  display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:8px;
  font-size:13px;font-weight:500;color:var(--gray-600);margin-bottom:1px;
  transition:all .15s;cursor:pointer;
}
.nav-item:hover{background:var(--gray-100);color:var(--gray-900)}
.nav-item.active{font-weight:600;background:var(--blue-50);color:var(--blue-700)}
.nav-item.active.role-dosen   {background:var(--green-50);color:var(--green-700)}
.nav-item.active.role-instansi{background:var(--amber-50);color:var(--amber-600)}
.nav-item.active.role-mahasiswa{background:var(--purple-50);color:var(--purple-600)}
.nav-icon{font-size:15px;width:20px;text-align:center;flex-shrink:0}

.sb-footer{padding:12px 14px;border-top:1px solid var(--gray-100)}
.user-card{
  display:flex;align-items:center;gap:9px;padding:9px 10px;
  border-radius:8px;background:var(--gray-50);border:1px solid var(--gray-200);
}
.user-avatar{
  width:32px;height:32px;border-radius:8px;
  display:flex;align-items:center;justify-content:center;font-size:14px;
  flex-shrink:0;background:var(--blue-100);
  overflow:hidden;
}
.user-name{font-size:12px;font-weight:600;color:var(--gray-800)}
.user-role{font-size:10px;color:var(--gray-500)}
.btn-logout{
  margin-left:auto;background:none;border:none;color:var(--gray-400);
  cursor:pointer;font-size:13px;padding:4px;border-radius:5px;
  transition:all .15s;
}
.btn-logout:hover{color:var(--red-500);background:var(--red-50)}

/* ── MAIN ─────────────────────────────────────────────────────── */
.main{margin-left:var(--sidebar-w);flex:1;padding:28px 32px;max-width:1200px}

.page-header{margin-bottom:24px}
.page-header h1{font-size:22px;font-weight:800;color:var(--gray-900);letter-spacing:-.4px}
.page-header p{color:var(--gray-500);font-size:13px;margin-top:3px}
.page-header-row{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}

/* ── CARDS ────────────────────────────────────────────────────── */
.card{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:20px;overflow:hidden}
.card-header{padding:14px 18px;border-bottom:1px solid var(--gray-100);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;background:var(--white)}
.card-header h3{font-size:14px;font-weight:700;color:var(--gray-800)}
.card-header p{font-size:12px;color:var(--gray-500);margin-top:1px}
.card-body{padding:18px}

/* ── STATS ────────────────────────────────────────────────────── */
.stats-grid{display:grid;gap:14px;margin-bottom:20px}
.stats-4{grid-template-columns:repeat(4,1fr)}
.stats-3{grid-template-columns:repeat(3,1fr)}
.stats-2{grid-template-columns:repeat(2,1fr)}
.stat-card{
  background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);
  padding:18px;position:relative;overflow:hidden;box-shadow:var(--shadow);
}
.stat-card::after{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:var(--radius) var(--radius) 0 0}
.stat-card.c-blue::after  {background:var(--blue-500)}
.stat-card.c-green::after {background:var(--green-500)}
.stat-card.c-amber::after {background:var(--amber-500)}
.stat-card.c-purple::after{background:var(--purple-500)}
.stat-card.c-red::after   {background:var(--red-500)}
.stat-card.c-admin::after {background:var(--blue-500)}
.stat-card.c-dosen::after {background:var(--green-500)}
.stat-card.c-inst::after  {background:var(--amber-500)}
.stat-card.c-mhs::after   {background:var(--purple-500)}
.stat-label{font-size:11px;font-weight:600;color:var(--gray-500);text-transform:uppercase;letter-spacing:.5px}
.stat-val{font-size:30px;font-weight:800;color:var(--gray-900);margin:6px 0 2px;font-family:'JetBrains Mono',monospace}
.stat-sub{font-size:11px;color:var(--gray-400)}
.stat-icon{position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:32px;opacity:.1}

/* ── TABLE ────────────────────────────────────────────────────── */
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:13px}
thead tr{background:var(--gray-50);border-bottom:1px solid var(--gray-200)}
th{padding:10px 14px;text-align:left;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.4px;white-space:nowrap}
td{padding:11px 14px;border-bottom:1px solid var(--gray-100);vertical-align:middle}
tr:last-child td{border-bottom:none}
tbody tr:hover td{background:var(--gray-50)}

/* ── BADGES ───────────────────────────────────────────────────── */
.badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:100px;font-size:11px;font-weight:600;letter-spacing:.2px;white-space:nowrap}
.badge-proses   {background:var(--amber-100);color:var(--amber-600)}
.badge-seminar  {background:var(--blue-100);color:var(--blue-700)}
.badge-selesai  {background:var(--green-100);color:var(--green-700)}
.badge-belum    {background:var(--gray-100);color:var(--gray-500)}
.badge-terjadwal{background:var(--blue-100);color:var(--blue-700)}
.badge-approved {background:var(--green-100);color:var(--green-700)}
.badge-rejected {background:var(--red-100);color:var(--red-600)}
.badge-pending  {background:var(--amber-100);color:var(--amber-600)}

/* ── BUTTONS ──────────────────────────────────────────────────── */
.btn{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;border:none;font-family:'Inter',sans-serif;transition:all .15s;white-space:nowrap}
.btn-primary{background:var(--blue-600);color:#fff;box-shadow:0 1px 2px rgba(37,99,235,.2)}
.btn-primary:hover{background:var(--blue-700)}
.btn-success{background:var(--green-600);color:#fff}
.btn-success:hover{background:var(--green-700)}
.btn-warning{background:var(--amber-500);color:#fff}
.btn-warning:hover{background:var(--amber-600)}
.btn-danger{background:var(--red-500);color:#fff}
.btn-danger:hover{background:var(--red-600)}
.btn-outline{background:var(--white);color:var(--gray-700);border:1px solid var(--gray-300)}
.btn-outline:hover{background:var(--gray-50);border-color:var(--gray-400)}
.btn-sm{padding:5px 10px;font-size:12px}
.btn-xs{padding:3px 8px;font-size:11px;border-radius:5px}
.btn-ghost{background:transparent;color:var(--blue-600);border:1px solid var(--blue-200)}
.btn-ghost:hover{background:var(--blue-50)}

/* ── FORMS ────────────────────────────────────────────────────── */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.form-group{display:flex;flex-direction:column;gap:4px;margin-bottom:14px}
.form-label{font-size:12px;font-weight:600;color:var(--gray-700)}
.form-control{
  background:var(--white);border:1.5px solid var(--gray-300);border-radius:7px;
  padding:8px 11px;color:var(--gray-800);font-size:13px;font-family:'Inter',sans-serif;
  width:100%;transition:border-color .15s,box-shadow .15s;
}
.form-control:focus{outline:none;border-color:var(--blue-500);box-shadow:0 0 0 3px rgba(59,130,246,.12)}
select.form-control{cursor:pointer}
textarea.form-control{resize:vertical;min-height:80px}
.form-hint{font-size:11px;color:var(--gray-400);margin-top:2px}

/* ── PROGRESS BAR ─────────────────────────────────────────────── */
.prog-wrap{background:var(--gray-200);border-radius:100px;overflow:hidden}
.prog-bar{height:100%;border-radius:100px;transition:width .5s ease}
.prog-bar-blue  {background:var(--blue-500)}
.prog-bar-green {background:var(--green-500)}
.prog-bar-amber {background:var(--amber-500)}
.prog-bar-purple{background:var(--purple-500)}

/* ── ALERTS ───────────────────────────────────────────────────── */
.alert{padding:10px 14px;border-radius:8px;font-size:13px;display:flex;align-items:flex-start;gap:8px;margin-bottom:16px;border:1px solid transparent}
.alert-info   {background:var(--blue-50);border-color:var(--blue-100);color:var(--blue-700)}
.alert-success{background:var(--green-50);border-color:var(--green-100);color:var(--green-700)}
.alert-warning{background:var(--amber-50);border-color:var(--amber-100);color:var(--amber-600)}
.alert-danger {background:var(--red-50);border-color:var(--red-100);color:var(--red-600)}

/* ── GRID ─────────────────────────────────────────────────────── */
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}

/* ── BAB CARDS ────────────────────────────────────────────────── */
.bab-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:10px}
.bab-card{
  border:1.5px solid var(--gray-200);border-radius:10px;padding:14px 10px;text-align:center;
  background:var(--white);transition:all .2s;position:relative;cursor:pointer;
}
.bab-card:hover{border-color:var(--blue-300);box-shadow:var(--shadow-md)}
.bab-card.done{background:var(--green-50);border-color:var(--green-300)}
.bab-card.done:hover{border-color:var(--green-500)}
.bab-card .bab-icon{font-size:22px;margin-bottom:6px}
.bab-card .bab-num{font-size:10px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.5px}
.bab-card .bab-stat{font-size:12px;font-weight:600;margin-top:3px;color:var(--gray-400)}
.bab-card.done .bab-stat{color:var(--green-600)}
.bab-card .bab-date{font-size:10px;color:var(--gray-400);margin-top:2px}

/* ── MODAL ────────────────────────────────────────────────────── */
.modal-bg{display:none;position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:200;align-items:center;justify-content:center;backdrop-filter:blur(2px)}
.modal-bg.open{display:flex}
.modal-box{background:var(--white);border:1px solid var(--gray-200);border-radius:14px;padding:26px;width:500px;max-height:88vh;overflow-y:auto;box-shadow:var(--shadow-lg);animation:mIn .2s ease}
@keyframes mIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.modal-title{font-size:16px;font-weight:800;color:var(--gray-900);margin-bottom:18px}
.modal-footer{display:flex;gap:8px;justify-content:flex-end;margin-top:20px;padding-top:16px;border-top:1px solid var(--gray-100)}

/* ── FLASH ────────────────────────────────────────────────────── */
.flash{position:fixed;bottom:20px;right:20px;z-index:9999;padding:12px 18px;border-radius:9px;font-size:13px;font-weight:600;background:var(--white);border:1px solid var(--gray-200);border-left:4px solid var(--green-500);box-shadow:var(--shadow-lg);max-width:320px;animation:fIn .3s ease}
.flash-err{border-left-color:var(--red-500)}
@keyframes fIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}

/* ── DIVIDER ──────────────────────────────────────────────────── */
.divider{border:none;border-top:1px solid var(--gray-100);margin:16px 0}

/* ── MISC ─────────────────────────────────────────────────────── */
code{font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--blue-600);background:var(--blue-50);padding:1px 6px;border-radius:4px}
.text-muted{color:var(--gray-400)}
.text-sm{font-size:12px}
.fw-bold{font-weight:700}
.empty-state{text-align:center;padding:36px;color:var(--gray-400)}
.empty-state .icon{font-size:40px;margin-bottom:10px}
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-thumb{background:var(--gray-300);border-radius:3px}
</style>
@stack('styles')
</head>
<body>

{{-- ── SIDEBAR ─────────────────────────────────────────────────── --}}
@php $role = auth()->user()->role; @endphp
<aside class="sidebar">
  <div class="sb-logo">
    <div class="sb-logo-icon">🎓</div>
    <div>
      <h2>SiMoKP</h2>
      <p>Ilmu Komputer Unila</p>
    </div>
  </div>

  <div class="sb-role-badge role-{{ $role }}">
    <span>{{ ['admin'=>'🛡️','dosen'=>'👨‍🏫','instansi'=>'🏢','mahasiswa'=>'🎓'][$role] }}</span>
    <span>{{ ['admin'=>'Administrator','dosen'=>'Dosen Pembimbing','instansi'=>'Pihak Instansi','mahasiswa'=>'Mahasiswa'][$role] }}</span>
  </div>

  <nav class="sb-nav">
    @if($role === 'admin')
      <div class="nav-section">Utama</div>
      <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active role-admin' : '' }}">
        <span class="nav-icon">📊</span> Dashboard
      </a>
      <div class="nav-section">Data Master</div>
      <a href="{{ route('admin.mahasiswa.index') }}" class="nav-item {{ request()->routeIs('admin.mahasiswa*') ? 'active role-admin' : '' }}">
        <span class="nav-icon">🎓</span> Mahasiswa
      </a>
      <a href="{{ route('admin.pembimbing.index') }}" class="nav-item {{ request()->routeIs('admin.pembimbing*') || request()->routeIs('admin.dosen*') ? 'active role-admin' : '' }}">
        <span class="nav-icon">👨‍🏫</span> Pembimbing
      </a>
      <a href="{{ route('admin.instansi.index') }}" class="nav-item {{ request()->routeIs('admin.instansi*') ? 'active role-admin' : '' }}">
        <span class="nav-icon">🏢</span> Instansi
      </a>
      <div class="nav-section">Monitoring</div>
      <a href="{{ route('admin.progress.index') }}" class="nav-item {{ request()->routeIs('admin.progress*') ? 'active role-admin' : '' }}">
        <span class="nav-icon">📈</span> Laporan
      </a>
      <a href="{{ route('admin.nilai.index') }}" class="nav-item {{ request()->routeIs('admin.nilai*') ? 'active role-admin' : '' }}">
        <span class="nav-icon">📝</span> Nilai
      </a>
      <a href="{{ route('admin.seminar.index') }}" class="nav-item {{ request()->routeIs('admin.seminar*') ? 'active role-admin' : '' }}">
        <span class="nav-icon">🎤</span> Jadwal Seminar
      </a>
      <a href="{{ route('admin.absensi.index') }}" class="nav-item {{ request()->routeIs('admin.absensi*') ? 'active role-admin' : '' }}">
        <span class="nav-icon">📋</span> Absensi Mahasiswa
      </a>
      <a href="{{ route('admin.surat.index') }}" class="nav-item {{ request()->routeIs('admin.surat*') ? 'active role-admin' : '' }}">
  <span class="nav-icon">✉️</span> Surat
</a>

    @elseif($role === 'dosen')
      <div class="nav-section">Utama</div>
      <a href="{{ route('dosen.dashboard') }}" class="nav-item {{ request()->routeIs('dosen.dashboard') ? 'active role-dosen' : '' }}">
        <span class="nav-icon">📊</span> Dashboard
      </a>
      <div class="nav-section">Bimbingan</div>
      <a href="{{ route('dosen.progress.index') }}" class="nav-item {{ request()->routeIs('dosen.progress*') ? 'active role-dosen' : '' }}">
        <span class="nav-icon">📈</span> Laporan
      </a>
      <a href="{{ route('dosen.nilai.index') }}" class="nav-item {{ request()->routeIs('dosen.nilai*') ? 'active role-dosen' : '' }}">
        <span class="nav-icon">📝</span> Nilai
      </a>
      <a href="{{ route('dosen.seminar.index') }}" class="nav-item {{ request()->routeIs('dosen.seminar*') ? 'active role-dosen' : '' }}">
        <span class="nav-icon">🎤</span> Jadwal Seminar
      </a>
      <a href="{{ route('dosen.absensi.index') }}" class="nav-item {{ request()->routeIs('dosen.absensi*') ? 'active role-dosen' : '' }}">
        <span class="nav-icon">📋</span> Absensi Mahasiswa
      </a>
      <a href="{{ route('dosen.chat.index') }}" class="nav-item {{ request()->routeIs('dosen.chat*') ? 'active role-dosen' : '' }}">
        <span class="nav-icon">💬</span> Pesan Instansi
      </a>

    @elseif($role === 'instansi')
      <div class="nav-section">Utama</div>
      <a href="{{ route('instansi.dashboard') }}" class="nav-item {{ request()->routeIs('instansi.dashboard') ? 'active role-instansi' : '' }}">
        <span class="nav-icon">📊</span> Dashboard
      </a>
      <div class="nav-section">Data KP</div>
      <a href="{{ route('instansi.mahasiswa.index') }}" class="nav-item {{ request()->routeIs('instansi.mahasiswa*') ? 'active role-instansi' : '' }}">
        <span class="nav-icon">🎓</span> Mahasiswa KP
      </a>
      <a href="{{ route('instansi.nilai.index') }}" class="nav-item {{ request()->routeIs('instansi.nilai*') ? 'active role-instansi' : '' }}">
        <span class="nav-icon">📝</span> Nilai Lapangan
      </a>
      <a href="{{ route('instansi.absensi.index') }}" class="nav-item {{ request()->routeIs('instansi.absensi*') ? 'active role-instansi' : '' }}">
        <span class="nav-icon">📋</span> Absensi Mahasiswa
      </a>
      <a href="{{ route('instansi.surat.index') }}" class="nav-item {{ request()->routeIs('instansi.surat*') ? 'active role-instansi' : '' }}">
  <span class="nav-icon">✉️</span> Surat
</a>
      <a href="{{ route('instansi.chat.index') }}" class="nav-item {{ request()->routeIs('instansi.chat*') ? 'active role-instansi' : '' }}">
        <span class="nav-icon">💬</span> Chat Dosen
      </a>

    @elseif($role === 'mahasiswa')
      <div class="nav-section">Utama</div>
      <a href="{{ route('mahasiswa.dashboard') }}" class="nav-item {{ request()->routeIs('mahasiswa.dashboard') ? 'active role-mahasiswa' : '' }}">
        <span class="nav-icon">📊</span> Dashboard Saya
      </a>
      {{-- ── MENU PROFIL (BARU) ── --}}
      <a href="{{ route('mahasiswa.profile.show') }}" class="nav-item {{ request()->routeIs('mahasiswa.profile*') ? 'active role-mahasiswa' : '' }}">
        <span class="nav-icon">👤</span> Profil Saya
      </a>
      <div class="nav-section">KP Saya</div>
      <a href="{{ route('mahasiswa.absensi.index') }}" class="nav-item {{ request()->routeIs('mahasiswa.absensi*') ? 'active role-mahasiswa' : '' }}">
        <span class="nav-icon">📍</span> Absensi
      </a>
      <a href="{{ route('mahasiswa.progress.index') }}" class="nav-item {{ request()->routeIs('mahasiswa.progress*') ? 'active role-mahasiswa' : '' }}">
        <span class="nav-icon">📈</span> Laporan
      </a>
      <a href="{{ route('mahasiswa.seminar.index') }}" class="nav-item {{ request()->routeIs('mahasiswa.seminar*') ? 'active role-mahasiswa' : '' }}">
        <span class="nav-icon">🎤</span> Info Seminar
      </a>
      <a href="{{ route('mahasiswa.surat.index') }}" class="nav-item {{ request()->routeIs('mahasiswa.surat*') ? 'active role-mahasiswa' : '' }}">
  <span class="nav-icon">✉️</span> Surat
</a>
    @endif
  </nav>

  <div class="sb-footer">
    @if(auth()->user()->role !== 'admin')
    <a href="{{ route('ganti-password') }}"
       style="display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:8px;font-size:12px;font-weight:500;color:var(--gray-500);margin-bottom:6px;text-decoration:none;transition:all .15s"
       onmouseover="this.style.background='var(--gray-100)';this.style.color='var(--gray-800)'"
       onmouseout="this.style.background='';this.style.color='var(--gray-500)'">
      <span>🔐</span> Ganti Password
    </a>
    @endif

    <div class="user-card">
      {{-- ── AVATAR: foto profil jika mahasiswa, emoji jika role lain (BARU) ── --}}
      @php
        $mhsFoto = ($role === 'mahasiswa' && auth()->user()->mahasiswa?->foto_profil)
          ? auth()->user()->mahasiswa->fotoUrl()
          : null;
      @endphp
      <div class="user-avatar">
        @if($mhsFoto)
          <img src="{{ $mhsFoto }}" alt="Foto Profil"
               style="width:32px;height:32px;object-fit:cover;border-radius:8px;display:block">
        @else
          {{ ['admin'=>'🛡️','dosen'=>'👨‍🏫','instansi'=>'🏢','mahasiswa'=>'🎓'][$role] }}
        @endif
      </div>

      <div>
        <div class="user-name">{{ Str::limit(auth()->user()->name, 16) }}</div>
        <div class="user-role">{{ ucfirst($role) }}</div>
      </div>
      <form method="POST" action="{{ route('logout') }}" style="margin-left:auto">
        @csrf
        <button type="submit" class="btn-logout" title="Keluar">🔓</button>
      </form>
    </div>
  </div>
</aside>

{{-- ── MAIN ──────────────────────────────────────────────────────── --}}
<main class="main">
  @if(session('success'))
    <div class="flash" id="flashMsg">✅ {{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="flash flash-err" id="flashMsg">❌ {{ session('error') }}</div>
  @endif

  @yield('content')
</main>

<script>
setTimeout(() => { const f=document.getElementById('flashMsg'); if(f){f.style.opacity='0';f.style.transform='translateY(8px)';f.style.transition='all .4s';} }, 3000);
function openModal(id){ document.getElementById(id).classList.add('open'); }
function closeModal(id){ document.getElementById(id).classList.remove('open'); }
document.addEventListener('DOMContentLoaded',()=>{
  document.querySelectorAll('.modal-bg').forEach(m=>{
    m.addEventListener('click',e=>{ if(e.target===m) m.classList.remove('open'); });
  });
});
</script>
@stack('scripts')
</body>
</html>