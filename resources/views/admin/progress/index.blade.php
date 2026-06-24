@extends('layouts.app')
@section('title','Monitoring Progress BAB')

@push('styles')
<style>
/* ── STUDENT CARDS ──────────────────────────────────────── */
.mhs-card {
  background:#fff;border:1px solid #e2e8f0;border-radius:10px;
  margin-bottom:10px;overflow:hidden;
}
.mhs-card-header {
  padding:12px 16px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;
}
.mhs-info { flex:1;min-width:0 }
.mhs-name {
  font-size:14px;font-weight:700;color:#0f172a;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.mhs-meta { font-size:11px;color:#94a3b8;margin-top:1px }
.mhs-right { display:flex;align-items:center;gap:8px;flex-shrink:0 }
.pct-text { font-size:14px;font-weight:800;color:#2563eb;min-width:36px;text-align:right }
.pct-text.selesai { color:#16a34a }

/* ── SIMPLE PROGRESS + DROPDOWN ─────────────────────────── */
.bab-simple { padding:0 16px 14px }

.progress-bar-bg {
  width:100%;height:6px;background:#f1f5f9;border-radius:99px;overflow:hidden;margin-bottom:10px;
}
.progress-bar-fill {
  height:100%;background:#2563eb;border-radius:99px;transition:width .3s;
}
.progress-bar-fill.selesai { background:#16a34a }

.bab-select-row { display:flex;gap:8px }

.bab-select {
  flex:1;min-width:0;font-size:13px;padding:7px 10px;border:1px solid #e2e8f0;
  border-radius:7px;background:#f8fafc;color:#334155;
}

.btn-bab-action {
  flex-shrink:0;font-size:12px;font-weight:700;padding:7px 14px;border-radius:7px;
  border:1px solid #86efac;background:#dcfce7;color:#15803d;cursor:pointer;white-space:nowrap;
}
.btn-bab-action:hover { background:#bbf7d0 }
.btn-bab-action.reset {
  border-color:#fca5a5;background:#fee2e2;color:#dc2626;
}
.btn-bab-action.reset:hover { background:#fecaca }
</style>
@endpush

@section('content')

<div class="page-header page-header-row">
  <div>
    <h1>Monitoring Progress BAB</h1>
    <p>Pilih BAB dari dropdown, lalu klik tombol aksi</p>
  </div>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:16px">
  <div class="card-body" style="padding:12px 18px">
    <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <input type="text" name="search" value="{{ request('search') }}"
             placeholder="🔍 Cari nama / NIM..." class="form-control" style="width:210px">
      <select name="status" class="form-control" style="width:140px">
        <option value="">Semua Status</option>
        <option value="proses"  {{ request('status')=='proses'  ?'selected':'' }}>Proses</option>
        <option value="seminar" {{ request('status')=='seminar' ?'selected':'' }}>Seminar</option>
        <option value="selesai" {{ request('status')=='selesai' ?'selected':'' }}>Selesai</option>
      </select>
      <select name="instansi" class="form-control" style="width:190px">
        <option value="">Semua Instansi</option>
        @foreach(\App\Models\Instansi::all() as $inst)
          <option value="{{ $inst->id }}" {{ request('instansi')==$inst->id?'selected':'' }}>
            {{ $inst->nama }}
          </option>
        @endforeach
      </select>
      <button type="submit" class="btn btn-outline btn-sm">Filter</button>
      <a href="{{ route('admin.progress.index') }}" class="btn btn-outline btn-sm">Reset</a>
    </form>
  </div>
</div>

{{-- Student List --}}
@forelse($mahasiswas as $m)
@php
  $pct = $m->progressPersen();
  $firstBab = $m->progressBabs->first();
  $firstIsSelesai = $firstBab && $firstBab->status === 'selesai';
@endphp
<div class="mhs-card">

  {{-- Header: info + badge --}}
  <div class="mhs-card-header">
    <div class="mhs-info">
      <div class="mhs-name">
        {{ $m->nama }}
        <span style="font-weight:400;color:#94a3b8;font-size:12px">{{ $m->nim }}</span>
      </div>
      <div class="mhs-meta">{{ $m->instansi?->nama ?? '–' }} · {{ $m->dosen?->nama ?? '–' }}</div>
    </div>
    <div class="mhs-right">
      <span class="pct-text {{ $pct==100?'selesai':'' }}">{{ $pct }}%</span>
      <span class="badge badge-{{ $m->status }}">{{ ucfirst($m->status) }}</span>
    </div>
  </div>

  {{-- Progress bar + dropdown BAB --}}
  <div class="bab-simple">
    <div class="progress-bar-bg">
      <div class="progress-bar-fill {{ $pct==100?'selesai':'' }}" style="width:{{ $pct }}%"></div>
    </div>

    <div class="bab-select-row">
      <select class="bab-select" id="babSelect{{ $m->id }}" onchange="updateBabAction({{ $m->id }})">
        @foreach($m->progressBabs as $p)
          <option value="{{ $p->id }}"
                  data-status="{{ $p->status }}"
                  data-bab="{{ $p->bab }}"
                  data-nama="{{ $m->nama }}">
            {{ $p->status === 'selesai' ? '✅' : '⬜' }} {{ $p->bab }} — {{ $p->status === 'selesai' ? 'Selesai' : 'Belum' }}
          </option>
        @endforeach
      </select>

      <button type="button"
              class="btn-bab-action {{ $firstIsSelesai ? 'reset' : '' }}"
              id="babAction{{ $m->id }}"
              onclick="handleBabAction({{ $m->id }})">
        {{ $firstIsSelesai ? '↩ Reset' : '✓ Tandai Selesai' }}
      </button>
    </div>
  </div>

</div>
@empty
  <div class="card">
    <div class="empty-state">
      <div class="icon">📚</div>
      <p>Tidak ada mahasiswa ditemukan.</p>
    </div>
  </div>
@endforelse

{{-- MODAL SELESAI --}}
<div class="modal-bg" id="modalSelesai">
  <div class="modal-box" style="width:400px">
    <div class="modal-title">✅ Tandai Selesai</div>
    <div style="font-size:13px;color:#64748b;margin-bottom:14px">
      <strong id="sNama"></strong> · <strong id="sBab"></strong><br>
      <span style="color:#f59e0b;font-size:12px">⚡ BAB sebelumnya otomatis ikut selesai</span>
    </div>
    <form method="POST" id="selesaiForm">
      @csrf @method('PUT')
      <input type="hidden" name="status" value="selesai">
      <div class="form-group">
        <label class="form-label">Catatan (opsional)</label>
        <textarea name="catatan" class="form-control" rows="2"
                  placeholder="Misal: Sudah direvisi sesuai arahan dosen..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline"
                onclick="closeModal('modalSelesai')">Batal</button>
        <button type="submit" class="btn btn-success">Selesai</button>
      </div>
    </form>
  </div>
</div>

{{-- FORM RESET (hidden) --}}
<form method="POST" id="resetForm" style="display:none">
  @csrf @method('PUT')
  <input type="hidden" name="status" value="belum">
</form>

@push('scripts')
<script>
function openSelesai(id, bab, nama) {
  document.getElementById('selesaiForm').action = `/admin/progress/${id}`;
  document.getElementById('sBab').textContent   = bab;
  document.getElementById('sNama').textContent  = nama;
  openModal('modalSelesai');
}

function submitReset(id) {
  if (!confirm('Reset BAB ini dan semua BAB sesudahnya ke Belum?')) return;
  document.getElementById('resetForm').action = `/admin/progress/${id}`;
  document.getElementById('resetForm').submit();
}

// Update tombol aksi sesuai status BAB yang dipilih di dropdown
function updateBabAction(mid) {
  const select = document.getElementById('babSelect' + mid);
  const opt    = select.options[select.selectedIndex];
  const btn    = document.getElementById('babAction' + mid);

  if (opt.dataset.status === 'selesai') {
    btn.textContent = '↩ Reset';
    btn.classList.add('reset');
  } else {
    btn.textContent = '✓ Tandai Selesai';
    btn.classList.remove('reset');
  }
}

// Jalankan aksi sesuai BAB yang sedang dipilih
function handleBabAction(mid) {
  const select = document.getElementById('babSelect' + mid);
  const opt    = select.options[select.selectedIndex];

  if (opt.dataset.status === 'selesai') {
    submitReset(select.value);
  } else {
    openSelesai(select.value, opt.dataset.bab, opt.dataset.nama);
  }
}
</script>
@endpush
@endsection