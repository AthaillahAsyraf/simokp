@extends('layouts.app')
@section('title','Surat')

@push('styles')
<style>
.tab-bar{display:flex;gap:4px;margin-bottom:20px;border-bottom:1.5px solid var(--gray-200)}
.tab-btn{padding:10px 18px;font-size:13px;font-weight:600;color:var(--gray-500);background:none;border:none;cursor:pointer;border-bottom:2.5px solid transparent;margin-bottom:-1.5px;transition:color .15s}
.tab-btn:hover{color:var(--gray-800)}
.tab-btn.active{color:var(--blue-600);border-bottom-color:var(--blue-600)}
.tab-panel{display:none}.tab-panel.active{display:block}

.thread-item{display:flex;gap:14px;padding:16px 0;border-bottom:1px solid var(--gray-100)}
.thread-item:last-child{border-bottom:none}
.thread-icon{width:38px;height:38px;border-radius:50%;background:var(--amber-50);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.thread-icon.out{background:var(--blue-50)}
.thread-body{flex:1;min-width:0}
.thread-head{display:flex;justify-content:space-between;gap:10px;margin-bottom:4px;flex-wrap:wrap}
.thread-route{font-size:12px;color:var(--gray-500)}
.thread-date{font-size:11px;color:var(--gray-400);white-space:nowrap}

.file-link{display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--blue-600);font-weight:600;padding:5px 10px;border:1px solid var(--blue-100);border-radius:8px;background:var(--blue-50);margin-top:8px;text-decoration:none}
.file-link:hover{background:var(--blue-100)}
.file-hint{font-size:11px;color:var(--gray-400);margin-top:4px}
.err-box{background:var(--red-50);border:1px solid var(--red-100);border-radius:6px;padding:10px 14px;margin-bottom:14px}
.err-box ul{margin:4px 0 0 18px;color:var(--red-600);font-size:.82rem}

.reply-bubble{margin-top:10px;padding:10px 14px;background:var(--gray-50);border-left:3px solid var(--blue-200);border-radius:0 8px 8px 0}
.reply-bubble .reply-meta{font-size:11px;color:var(--gray-500);margin-bottom:4px}

.recipient-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px;margin-top:6px}
.recipient-card{border:1.5px solid var(--gray-200);border-radius:10px;padding:10px 12px;cursor:pointer;transition:all .15s;display:flex;align-items:center;gap:8px;font-size:13px}
.recipient-card:hover{border-color:var(--blue-300);background:var(--blue-50)}
.recipient-card input[type=radio]{accent-color:var(--blue-600)}
.recipient-card.selected{border-color:var(--blue-500);background:var(--blue-50);font-weight:600}
.specific-recipient{display:none;margin-top:10px}
.specific-recipient.visible{display:block}
</style>
@endpush

@section('content')
<div class="page-header">
  <h1>Surat</h1>
  <p>Kelola surat masuk dari mahasiswa & kirim korespondensi ke semua pihak</p>
</div>

@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">⚠️ {{ session('error') }}</div>@endif

<div class="tab-bar">
  <button type="button" class="tab-btn" id="tabBtnMasuk"   onclick="switchTab('masuk')">
    📥 Surat Masuk
    @if($suratMasuk->count())<span class="badge badge-proses" style="margin-left:4px">{{ $suratMasuk->count() }}</span>@endif
  </button>
  <button type="button" class="tab-btn" id="tabBtnKirim"   onclick="switchTab('kirim')">✉️ Kirim Surat</button>
  <button type="button" class="tab-btn" id="tabBtnRiwayat" onclick="switchTab('riwayat')">🗂️ Riwayat Terkirim</button>
</div>

{{-- ══ TAB MASUK ══ --}}
<div class="tab-panel" id="panelMasuk">
  @if($errors->balas->any())
    <div class="err-box"><ul>@foreach($errors->balas->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif
  <div class="card">
    <div class="card-header"><h3>Surat Masuk</h3><p>{{ $suratMasuk->count() }} surat</p></div>
    <div class="card-body">
      @forelse($suratMasuk as $s)
        <div class="thread-item">
          <div class="thread-icon">📥</div>
          <div class="thread-body">
            <div class="thread-head">
              <strong>{{ $s->perihal }}</strong>
              <span class="thread-date">{{ $s->created_at->translatedFormat('d M Y, H:i') }}</span>
            </div>
            <div class="thread-route">
              Dari: <strong>{{ $s->pengirim_nama }}</strong> ({{ ucfirst($s->pengirim_role) }}) &middot; {{ $s->jenis_label }}
            </div>
            @if($s->keterangan)<p class="text-sm" style="margin-top:6px">{{ $s->keterangan }}</p>@endif
            @if($s->file)<a href="{{ $s->file_url }}" target="_blank" class="file-link">📄 Lihat Surat Pengantar</a>@endif

            @php $sudahDibalas = $s->balasan->where('jenis', \App\Models\Surat::JENIS_BALASAN)->isNotEmpty(); @endphp
            <div style="margin-top:10px;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
              <button class="btn btn-primary btn-sm"
                onclick="openBalas({{ $s->id }}, {{ json_encode($s->pengirim_nama) }}, {{ json_encode($s->perihal) }})">
                ↩️ Balas
              </button>
              @if($sudahDibalas)<span class="badge badge-selesai" style="font-size:11px">✅ Sudah dibalas</span>@endif
            </div>

            @foreach($s->balasan->where('jenis', \App\Models\Surat::JENIS_BALASAN) as $b)
              <div class="reply-bubble">
                <div class="reply-meta">📤 Balasan Anda — {{ $b->created_at->format('d M Y, H:i') }}</div>
                <p class="text-sm">{{ $b->keterangan }}</p>
                @if($b->file)<a href="{{ $b->file_url }}" target="_blank" class="file-link">📄 Lampiran Balasan</a>@endif
              </div>
            @endforeach
          </div>
        </div>
      @empty
        <div style="text-align:center;padding:40px;color:var(--gray-400)">
          <div style="font-size:36px;margin-bottom:10px">📭</div><p>Belum ada surat masuk.</p>
        </div>
      @endforelse
    </div>
  </div>
</div>

{{-- ══ TAB KIRIM ══ --}}
<div class="tab-panel" id="panelKirim">
  @if($errors->kirim->any())
    <div class="err-box"><ul>@foreach($errors->kirim->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif
  <div class="card">
    <div class="card-header"><h3>Kirim Surat Baru</h3></div>
    <div class="card-body">
      <form method="POST" action="{{ route('instansi.surat.kirim') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
          <label class="form-label">Tujuan Penerima *</label>
          <div class="recipient-grid">
            <label class="recipient-card {{ old('tujuan_role') === 'mahasiswa' ? 'selected' : '' }}"
              onclick="selectRecipient(this,'mahasiswa')">
              <input type="radio" name="tujuan_role" value="mahasiswa" {{ old('tujuan_role') === 'mahasiswa' ? 'checked' : '' }} required>
              🎓 Mahasiswa
            </label>
            <label class="recipient-card {{ old('tujuan_role') === 'admin' ? 'selected' : '' }}"
              onclick="selectRecipient(this,'admin')">
              <input type="radio" name="tujuan_role" value="admin" {{ old('tujuan_role') === 'admin' ? 'checked' : '' }}>
              🏛️ Jurusan
            </label>
            @if($listDosen->isNotEmpty())
            <label class="recipient-card {{ old('tujuan_role') === 'dosen' ? 'selected' : '' }}"
              onclick="selectRecipient(this,'dosen')">
              <input type="radio" name="tujuan_role" value="dosen" {{ old('tujuan_role') === 'dosen' ? 'checked' : '' }}>
              👨‍🏫 Dosen
            </label>
            @endif
          </div>
          @if($listDosen->isEmpty())
            <p class="file-hint">Dosen pembimbing akan tersedia sebagai tujuan setelah admin menetapkannya untuk mahasiswa di instansi Anda.</p>
          @endif
          <div class="specific-recipient {{ old('tujuan_role') === 'mahasiswa' ? 'visible' : '' }}" id="dropMahasiswa">
            <label class="form-label" style="font-size:12px;margin-top:8px">Pilih Mahasiswa *</label>
            <select name="tujuan_mahasiswa_id" class="form-control">
              <option value="">-- Pilih mahasiswa --</option>
              @foreach($listMahasiswa as $m)
                <option value="{{ $m->id }}" {{ old('tujuan_mahasiswa_id') == $m->id ? 'selected' : '' }}>
                  {{ $m->nama }} ({{ $m->nim }})
                </option>
              @endforeach
            </select>
          </div>
          <div class="specific-recipient {{ old('tujuan_role') === 'dosen' ? 'visible' : '' }}" id="dropDosen">
            <label class="form-label" style="font-size:12px;margin-top:8px">Pilih Dosen *</label>
            <select name="tujuan_dosen_id" class="form-control">
              <option value="">-- Pilih dosen --</option>
              @foreach($listDosen as $d)
                <option value="{{ $d->id }}" {{ old('tujuan_dosen_id') == $d->id ? 'selected' : '' }}>{{ $d->nama }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Perihal *</label>
          <input type="text" name="perihal" class="form-control" value="{{ old('perihal') }}" placeholder="Perihal surat..." required>
        </div>
        <div class="form-group">
          <label class="form-label">Isi Surat *</label>
          <textarea name="keterangan" class="form-control" rows="4" placeholder="Tulis isi surat..." required>{{ old('keterangan') }}</textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Lampiran <span style="color:var(--gray-400);font-weight:400">(opsional)</span></label>
          <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
          <p class="file-hint">PDF, DOC/DOCX, atau gambar — maks 10 MB. Cocok untuk surat balasan resmi berkop instansi.</p>
        </div>
        <button type="submit" class="btn btn-primary">Kirim Surat</button>
      </form>
    </div>
  </div>
</div>

{{-- ══ TAB RIWAYAT ══ --}}
<div class="tab-panel" id="panelRiwayat">
  <div class="card">
    <div class="card-header"><h3>Riwayat Terkirim</h3><p>{{ $suratTerkirim->count() }} surat</p></div>
    <div class="card-body">
      @forelse($suratTerkirim as $s)
        <div class="thread-item">
          <div class="thread-icon out">📤</div>
          <div class="thread-body">
            <div class="thread-head">
              <strong>{{ $s->perihal }}</strong>
              <span class="thread-date">{{ $s->created_at->translatedFormat('d M Y, H:i') }}</span>
            </div>
            <div class="thread-route">Ke: <strong>{{ $s->penerima_nama }}</strong> &middot; {{ $s->jenis_label }}</div>
            @if($s->keterangan)<p class="text-sm" style="margin-top:6px">{{ $s->keterangan }}</p>@endif
            @if($s->file)<a href="{{ $s->file_url }}" target="_blank" class="file-link">📄 Lampiran</a>@endif
          </div>
        </div>
      @empty
        <div style="text-align:center;padding:40px;color:var(--gray-400)">
          <div style="font-size:36px;margin-bottom:10px">📭</div><p>Belum ada surat terkirim.</p>
        </div>
      @endforelse
    </div>
  </div>
</div>

{{-- MODAL BALAS --}}
<div class="modal-bg" id="modalBalas">
  <div class="modal-box">
    <div class="modal-title" id="balasTitle">↩️ Balas Surat</div>
    <form method="POST" id="balasForm" enctype="multipart/form-data">
      @csrf
      <div class="form-group">
        <label class="form-label">Isi Balasan *</label>
        <textarea name="keterangan" class="form-control" rows="4" placeholder="Tulis isi balasan surat..." required></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Lampiran <span style="color:var(--gray-400);font-weight:400">(opsional)</span></label>
        <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
        <p class="file-hint">PDF, DOC/DOCX, atau gambar — maks 10 MB</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalBalas')">Batal</button>
        <button type="submit" class="btn btn-primary">Kirim Balasan</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function switchTab(tab) {
  ['masuk','kirim','riwayat'].forEach(t => {
    const c = t[0].toUpperCase() + t.slice(1);
    document.getElementById('panel' + c).classList.toggle('active', t === tab);
    document.getElementById('tabBtn'  + c).classList.toggle('active', t === tab);
  });
}
switchTab('masuk');

function selectRecipient(el, role) {
  document.querySelectorAll('.recipient-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  ['dropMahasiswa','dropDosen'].forEach(id => document.getElementById(id).classList.remove('visible'));
  if (role === 'mahasiswa') document.getElementById('dropMahasiswa').classList.add('visible');
  if (role === 'dosen')     document.getElementById('dropDosen').classList.add('visible');
}
document.querySelectorAll('.recipient-card').forEach(c => {
  if (c.querySelector('input:checked')) c.classList.add('selected');
});

function openBalas(id, nama, perihal) {
  document.getElementById('balasForm').action = `{{ url('instansi-area/surat') }}/${id}/balas`;
  document.getElementById('balasTitle').textContent = '↩️ Balas — ' + nama + ' (' + perihal + ')';
  openModal('modalBalas');
}
@if($errors->balas->any())
  window.addEventListener('load', () => { openModal('modalBalas'); switchTab('masuk'); });
@endif
@if($errors->kirim->any())
  window.addEventListener('load', () => switchTab('kirim'));
@endif
</script>
@endpush
@endsection
