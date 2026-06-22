@extends('layouts.app')
@section('title','Monitoring Progress BAB')

@push('styles')
<style>
/* ── STUDENT CARDS ──────────────────────────────────────── */
.mhs-card {
  background:#fff;border:1px solid #e2e8f0;border-radius:10px;
  margin-bottom:8px;overflow:hidden;
}
.mhs-card-header {
  padding:10px 16px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;
  border-bottom:1px solid #f1f5f9;
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

/* ── BAB CHIPS ──────────────────────────────────────────── */
.bab-track {
  padding:10px 16px;
  display:flex;gap:6px;align-items:center;flex-wrap:wrap;
}
.bab-chip {
  display:inline-flex;align-items:center;gap:5px;
  padding:5px 10px;border-radius:7px;font-size:12px;font-weight:600;
  border:1px solid #e2e8f0;white-space:nowrap;
}
.bab-chip.done  { background:#f0fdf4;border-color:#86efac;color:#15803d }
.bab-chip.belum { background:#f8fafc;border-color:#e2e8f0;color:#94a3b8 }
.bab-chip .chip-icon { font-size:13px }
.btn-chip {
  padding:1px 7px;border-radius:5px;font-size:11px;font-weight:700;
  border:none;cursor:pointer;line-height:1.6;
}
.btn-ok  { background:#dcfce7;color:#15803d;border:1px solid #86efac }
.btn-ok:hover  { background:#bbf7d0 }
.btn-rst { background:#fee2e2;color:#dc2626;border:1px solid #fca5a5 }
.btn-rst:hover { background:#fecaca }
</style>
@endpush

@section('content')

<div class="page-header page-header-row">
  <div>
    <h1>Monitoring Progress BAB</h1>
    <p>Klik tombol BAB untuk update — BAB sebelumnya otomatis ikut selesai</p>
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
@php $pct = $m->progressPersen(); @endphp
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

  {{-- BAB chips: satu baris --}}
  <div class="bab-track">
    @foreach($m->progressBabs as $p)
    <div class="bab-chip {{ $p->status==='selesai'?'done':'belum' }}"
         title="{{ $p->catatan ? $p->catatan.' · ' : '' }}{{ $p->tanggal_selesai ?? '' }}">
      <span class="chip-icon">{{ $p->status==='selesai' ? '✅' : '⏳' }}</span>
      <span>{{ $p->bab }}</span>
      @if($p->status === 'belum')
        <button class="btn-chip btn-ok"
                onclick="openSelesai({{ $p->id }},'{{ $p->bab }}','{{ $m->nama }}')">
          ✓
        </button>
      @else
        <button class="btn-chip btn-rst"
                onclick="submitReset({{ $p->id }})">
          ↩
        </button>
      @endif
    </div>
    @endforeach
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
</script>
@endpush
@endsection