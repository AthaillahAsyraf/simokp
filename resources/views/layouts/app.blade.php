<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'SiMoKP') – Ilmu Komputer Unila</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<style>
:root {
  --bg:#0f1117; --surface:#191c25; --surface2:#21263a; --border:#2a2f45;
  --text:#e8eaf2; --muted:#7b82a0;
  --admin:#6366f1; --admin-l:#818cf8;
  --dosen:#10b981; --dosen-l:#34d399;
  --inst:#f59e0b;  --inst-l:#fbbf24;
  --mhs:#ec4899;   --mhs-l:#f472b6;
  --danger:#ef4444; --success:#22c55e;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh}
a{color:inherit;text-decoration:none}

/* SIDEBAR */
.sidebar{width:240px;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;position:fixed;left:0;top:0;bottom:0;z-index:100}
.sb-logo{padding:22px 20px;border-bottom:1px solid var(--border)}
.sb-logo h2{font-size:20px;font-weight:800;letter-spacing:-0.5px}
.sb-logo h2 span{color:var(--admin)}
.sb-logo p{font-size:11px;color:var(--muted);margin-top:2px}
.sb-role{margin:14px;padding:8px 14px;border-radius:8px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;text-align:center}
.role-admin   {background:rgba(99,102,241,.15);color:var(--admin-l)}
.role-dosen   {background:rgba(16,185,129,.15);color:var(--dosen-l)}
.role-instansi{background:rgba(245,158,11,.15);color:var(--inst-l)}
.role-mahasiswa{background:rgba(236,72,153,.15);color:var(--mhs-l)}
.sb-nav{flex:1;padding:6px 10px;overflow-y:auto}
.nav-sec{font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;padding:12px 8px 4px}
.nav-item{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:500;color:var(--muted);margin-bottom:1px;transition:all .15s}
.nav-item:hover{background:var(--surface2);color:var(--text)}
.nav-item.active{font-weight:600}
.nav-item.active.role-admin   {background:rgba(99,102,241,.15);color:var(--admin-l)}
.nav-item.active.role-dosen   {background:rgba(16,185,129,.15);color:var(--dosen-l)}
.nav-item.active.role-instansi{background:rgba(245,158,11,.15);color:var(--inst-l)}
.nav-item.active.role-mahasiswa{background:rgba(236,72,153,.15);color:var(--mhs-l)}
.nav-badge{margin-left:auto;background:var(--danger);color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:100px}
.sb-footer{padding:14px;border-top:1px solid var(--border)}
.user-pill{display:flex;align-items:center;gap:10px;padding:8px;border-radius:8px;cursor:default}
.avatar{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:700;flex-shrink:0}
.u-name{font-size:13px;font-weight:600}
.u-role{font-size:11px;color:var(--muted)}
.btn-logout{margin-left:auto;background:none;border:1px solid var(--border);border-radius:6px;color:var(--muted);cursor:pointer;font-size:12px;padding:5px 10px;transition:all .15s}
.btn-logout:hover{border-color:var(--danger);color:var(--danger)}

/* MAIN */
.main{margin-left:240px;flex:1;padding:32px;max-width:1200px}
.page-header{margin-bottom:26px}
.page-header h1{font-size:26px;font-weight:800;letter-spacing:-.5px}
.page-header p{color:var(--muted);font-size:14px;margin-top:4px}

/* CARDS */
.card{background:var(--surface);border:1px solid var(--border);border-radius:14px;margin-bottom:20px;overflow:hidden}
.card-header{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.card-header h3{font-size:15px;font-weight:700}
.card-header p{font-size:12px;color:var(--muted);margin-top:2px}
.card-body{padding:20px}

/* STATS */
.stats-grid{display:grid;gap:16px;margin-bottom:22px}
.stats-4{grid-template-columns:repeat(4,1fr)}
.stats-3{grid-template-columns:repeat(3,1fr)}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px;position:relative;overflow:hidden}
.stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px}
.stat-card.c-admin::before{background:var(--admin)}
.stat-card.c-dosen::before{background:var(--dosen)}
.stat-card.c-inst::before{background:var(--inst)}
.stat-card.c-mhs::before{background:var(--mhs)}
.stat-label{font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px}
.stat-val{font-size:32px;font-weight:800;margin:6px 0 2px;font-family:'JetBrains Mono',monospace}
.stat-sub{font-size:12px;color:var(--muted)}
.stat-icon{position:absolute;right:16px;top:50%;transform:translateY(-50%);font-size:36px;opacity:.12}

/* TABLE */
table{width:100%;border-collapse:collapse;font-size:13px}
thead tr{border-bottom:1px solid var(--border)}
th{padding:10px 14px;text-align:left;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px}
td{padding:11px 14px;border-bottom:1px solid rgba(255,255,255,.04)}
tr:last-child td{border-bottom:none}
tr:hover td{background:rgba(255,255,255,.02)}

/* PILLS */
.pill{display:inline-flex;align-items:center;padding:2px 10px;border-radius:100px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px}
.pill-proses  {background:rgba(245,158,11,.15);color:var(--inst-l)}
.pill-selesai {background:rgba(34,197,94,.15);color:#4ade80}
.pill-seminar {background:rgba(99,102,241,.15);color:var(--admin-l)}
.pill-belum   {background:rgba(255,255,255,.07);color:var(--muted)}
.pill-disetujui{background:rgba(16,185,129,.15);color:var(--dosen-l)}
.pill-pending {background:rgba(245,158,11,.15);color:var(--inst-l)}
.pill-ditolak {background:rgba(239,68,68,.15);color:#f87171}
.pill-hadir   {background:rgba(34,197,94,.15);color:#4ade80}
.pill-terjadwal{background:rgba(99,102,241,.15);color:var(--admin-l)}
.pill-tidak_hadir{background:rgba(239,68,68,.15);color:#f87171}

/* BUTTONS */
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;border:none;font-family:inherit;transition:all .2s}
.btn-primary{background:var(--admin);color:#fff} .btn-primary:hover{opacity:.85}
.btn-success{background:var(--dosen);color:#fff} .btn-success:hover{opacity:.85}
.btn-warning{background:var(--inst);color:#0f1117} .btn-warning:hover{opacity:.85}
.btn-danger {background:var(--danger);color:#fff} .btn-danger:hover{opacity:.85}
.btn-ghost  {background:var(--surface2);color:var(--text);border:1px solid var(--border)} .btn-ghost:hover{border-color:var(--text)}
.btn-sm{padding:5px 10px;font-size:12px}
.btn-xs{padding:3px 8px;font-size:11px}

/* FORMS */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.form-group{display:flex;flex-direction:column;gap:5px;margin-bottom:14px}
.form-group label{font-size:12px;font-weight:600;color:var(--muted)}
.form-control{background:var(--bg);border:1.5px solid var(--border);border-radius:8px;padding:9px 12px;color:var(--text);font-size:13px;font-family:inherit;width:100%;transition:border-color .2s}
.form-control:focus{outline:none;border-color:var(--admin)}
select.form-control option{background:var(--surface)}
textarea.form-control{resize:vertical;min-height:80px}

/* PROGRESS */
.prog-wrap{background:var(--surface2);border-radius:100px;height:6px;overflow:hidden}
.prog-bar{height:100%;border-radius:100px;transition:width .5s}
.prog-txt{font-size:11px;color:var(--muted);margin-top:3px}

/* ALERT */
.alert{padding:11px 16px;border-radius:9px;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.alert-info   {background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.3);color:var(--admin-l)}
.alert-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#4ade80}
.alert-warning{background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3);color:var(--inst-l)}
.alert-danger {background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#f87171}

/* GRID */
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}

/* MODAL */
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:200;align-items:center;justify-content:center}
.modal-bg.open{display:flex}
.modal-box{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px;width:500px;max-height:90vh;overflow-y:auto;box-shadow:0 40px 80px rgba(0,0,0,.5);animation:su .2s ease}
@keyframes su{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
.modal-box h3{font-size:17px;font-weight:800;margin-bottom:20px}
.modal-footer{display:flex;gap:8px;justify-content:flex-end;margin-top:20px;padding-top:18px;border-top:1px solid var(--border)}

/* BAB GRID */
.bab-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:10px}
.bab-item{background:var(--surface2);border:1.5px solid var(--border);border-radius:10px;padding:12px 6px;text-align:center}
.bab-item.done{border-color:var(--dosen);background:rgba(16,185,129,.1)}
.bab-item.proses{border-color:var(--inst);background:rgba(245,158,11,.1)}
.bab-name{font-size:9px;font-weight:700;color:var(--muted);text-transform:uppercase}
.bab-status{font-size:18px;margin:4px 0}
.bab-label{font-size:10px;font-weight:600;color:var(--muted)}
.bab-item.done .bab-label{color:var(--dosen-l)}
.bab-item.proses .bab-label{color:var(--inst-l)}

/* FLASH */
.flash{position:fixed;bottom:22px;right:22px;z-index:9999;padding:13px 20px;border-radius:10px;font-size:13px;font-weight:600;border-left:4px solid var(--success);background:var(--surface);border:1px solid var(--border);border-left:4px solid var(--success);max-width:320px;animation:flashIn .3s ease}
.flash-error{border-left-color:var(--danger)}
@keyframes flashIn{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}

/* Scrollbar */
::-webkit-scrollbar{width:5px}
::-webkit-scrollbar-thumb{background:var(--border);border-radius:3px}
code{font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--admin-l)}
</style>
@stack('styles')
</head>
<body>

{{-- SIDEBAR --}}
<aside class="sidebar">
  <div class="sb-logo">
    <h2>SiMo<span>KP</span></h2>
    <p>Ilmu Komputer Unila</p>
  </div>

  @php $role = auth()->user()->role; @endphp
  <div class="sb-role role-{{ $role }}">
    {{ ['admin'=>'👑 Administrator','dosen'=>'👨‍🏫 Dosen Pembimbing','instansi'=>'🏢 Pihak Instansi','mahasiswa'=>'🎓 Mahasiswa'][$role] }}
  </div>

  <nav class="sb-nav">
    @if($role === 'admin')
      <div class="nav-sec">Utama</div>
      <a href="{{ route('admin.dashboard') }}" class="nav-item role-admin {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><span>📊</span> Dashboard</a>
      <div class="nav-sec">Manajemen Data</div>
      <a href="{{ route('admin.mahasiswa.index') }}" class="nav-item role-admin {{ request()->routeIs('admin.mahasiswa*') ? 'active' : '' }}"><span>🎓</span> Data Mahasiswa</a>
      <a href="{{ route('admin.dosen.index') }}" class="nav-item role-admin {{ request()->routeIs('admin.dosen*') ? 'active' : '' }}"><span>👨‍🏫</span> Data Dosen</a>
      <a href="{{ route('admin.instansi.index') }}" class="nav-item role-admin {{ request()->routeIs('admin.instansi*') ? 'active' : '' }}"><span>🏢</span> Data Instansi</a>
      <div class="nav-sec">Monitoring</div>
      <a href="{{ route('admin.progress.index') }}" class="nav-item role-admin {{ request()->routeIs('admin.progress*') ? 'active' : '' }}"><span>📈</span> Progress BAB</a>
      <a href="{{ route('admin.seminar.index') }}" class="nav-item role-admin {{ request()->routeIs('admin.seminar*') ? 'active' : '' }}"><span>🎤</span> Jadwal Seminar</a>
      <div class="nav-sec">Administrasi</div>
      <a href="{{ route('admin.surat.index') }}" class="nav-item role-admin {{ request()->routeIs('admin.surat*') ? 'active' : '' }}">
        <span>📄</span> Surat & Dokumen
        @php $sp = \App\Models\Surat::where('status','pending')->count() @endphp
        @if($sp > 0)<span class="nav-badge">{{ $sp }}</span>@endif
      </a>
      <a href="{{ route('admin.laporan.index') }}" class="nav-item role-admin {{ request()->routeIs('admin.laporan*') ? 'active' : '' }}"><span>📑</span> Laporan & Rekap</a>

    @elseif($role === 'dosen')
      <div class="nav-sec">Utama</div>
      <a href="{{ route('dosen.dashboard') }}" class="nav-item role-dosen {{ request()->routeIs('dosen.dashboard') ? 'active' : '' }}"><span>📊</span> Dashboard</a>
      <div class="nav-sec">Bimbingan</div>
      <a href="{{ route('dosen.progress.index') }}" class="nav-item role-dosen {{ request()->routeIs('dosen.progress*') ? 'active' : '' }}"><span>📈</span> Progress BAB</a>
      <a href="{{ route('dosen.logbook.index') }}" class="nav-item role-dosen {{ request()->routeIs('dosen.logbook*') ? 'active' : '' }}"><span>📋</span> Logbook Mahasiswa</a>
      <div class="nav-sec">Penilaian</div>
      <a href="{{ route('dosen.nilai.index') }}" class="nav-item role-dosen {{ request()->routeIs('dosen.nilai*') ? 'active' : '' }}"><span>⭐</span> Input Nilai</a>

    @elseif($role === 'instansi')
      <div class="nav-sec">Utama</div>
      <a href="{{ route('instansi.dashboard') }}" class="nav-item role-instansi {{ request()->routeIs('instansi.dashboard') ? 'active' : '' }}"><span>📊</span> Dashboard</a>
      <div class="nav-sec">Monitoring</div>
      <a href="{{ route('instansi.logbook.index') }}" class="nav-item role-instansi {{ request()->routeIs('instansi.logbook*') ? 'active' : '' }}">
        <span>📋</span> Verifikasi Logbook
        @php $lp = \App\Models\Logbook::whereIn('mahasiswa_id', auth()->user()->instansi->mahasiswas->pluck('id'))->where('status_instansi','pending')->count() @endphp
        @if($lp > 0)<span class="nav-badge">{{ $lp }}</span>@endif
      </a>
      <div class="nav-sec">Penilaian</div>
      <a href="{{ route('instansi.nilai.index') }}" class="nav-item role-instansi {{ request()->routeIs('instansi.nilai*') ? 'active' : '' }}"><span>⭐</span> Beri Nilai</a>

    @elseif($role === 'mahasiswa')
      <div class="nav-sec">Utama</div>
      <a href="{{ route('mahasiswa.dashboard') }}" class="nav-item role-mahasiswa {{ request()->routeIs('mahasiswa.dashboard') ? 'active' : '' }}"><span>📊</span> Dashboard Saya</a>
      <div class="nav-sec">KP Saya</div>
      <a href="{{ route('mahasiswa.progress.index') }}" class="nav-item role-mahasiswa {{ request()->routeIs('mahasiswa.progress*') ? 'active' : '' }}"><span>📈</span> Progress BAB</a>
      <a href="{{ route('mahasiswa.logbook.index') }}" class="nav-item role-mahasiswa {{ request()->routeIs('mahasiswa.logbook*') ? 'active' : '' }}"><span>📋</span> Logbook Harian</a>
      <div class="nav-sec">Administrasi</div>
      <a href="{{ route('mahasiswa.seminar.index') }}" class="nav-item role-mahasiswa {{ request()->routeIs('mahasiswa.seminar*') ? 'active' : '' }}"><span>🎤</span> Info Seminar</a>
      <a href="{{ route('mahasiswa.surat.index') }}" class="nav-item role-mahasiswa {{ request()->routeIs('mahasiswa.surat*') ? 'active' : '' }}"><span>📄</span> Pengajuan Surat</a>
    @endif
  </nav>

  <div class="sb-footer">
    <div class="user-pill">
      <div class="avatar role-{{ $role }}" style="font-size:16px">
        {{ ['admin'=>'🛡️','dosen'=>'👨‍🏫','instansi'=>'🏢','mahasiswa'=>'👤'][$role] }}
      </div>
      <div>
        <div class="u-name">{{ Str::limit(auth()->user()->name, 18) }}</div>
        <div class="u-role">{{ ucfirst($role) }}</div>
      </div>
      <form method="POST" action="{{ route('logout') }}" style="margin-left:auto">
        @csrf
        <button type="submit" class="btn-logout">Keluar</button>
      </form>
    </div>
  </div>
</aside>

{{-- MAIN CONTENT --}}
<main class="main">
  @if(session('success'))
    <div class="flash" id="flashMsg">✅ {{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="flash flash-error" id="flashMsg">❌ {{ session('error') }}</div>
  @endif

  @yield('content')
</main>

<script>
  // Auto-hide flash
  setTimeout(() => { const f = document.getElementById('flashMsg'); if(f) f.style.opacity = '0'; }, 3000);

  // Modal helpers
  function openModal(id) { document.getElementById(id).classList.add('open'); }
  function closeModal(id) { document.getElementById(id).classList.remove('open'); }
  document.querySelectorAll('.modal-bg').forEach(m => {
    m.addEventListener('click', e => { if(e.target === m) m.classList.remove('open'); });
  });
</script>
@stack('scripts')
</body>
</html>