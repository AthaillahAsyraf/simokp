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
.thread-icon{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.thread-icon.in{background:var(--green-50)}.thread-icon.out{background:var(--blue-50)}
.thread-body{flex:1;min-width:0}
.thread-head{display:flex;justify-content:space-between;gap:10px;margin-bottom:4px;flex-wrap:wrap}
.thread-route{font-size:12px;color:var(--gray-500)}
.thread-date{font-size:11px;color:var(--gray-400);white-space:nowrap}

.file-link{display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--blue-600);font-weight:600;padding:5px 10px;border:1px solid var(--blue-100);border-radius:8px;background:var(--blue-50);margin-top:8px;text-decoration:none}
.file-link:hover{background:var(--blue-100)}
.file-hint{font-size:11px;color:var(--gray-400);margin-top:4px}

.err-box{background:var(--red-50);border:1px solid var(--red-100);border-radius:6px;padding:10px 14px;margin-bottom:14px}
.err-box ul{margin:4px 0 0 18px;color:var(--red-600);font-size:.82rem}
.info-box{background:var(--blue-50);border:1px solid var(--blue-100);border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:var(--blue-700);display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}

.recipient-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px;margin-top:6px}
.recipient-card{border:1.5px solid var(--gray-200);border-radius:10px;padding:10px 12px;cursor:pointer;transition:all .15s;display:flex;align-items:center;gap:8px;font-size:13px}
.recipient-card:hover{border-color:var(--blue-300);background:var(--blue-50)}
.recipient-card input[type=radio]{accent-color:var(--blue-600)}
.recipient-card.selected{border-color:var(--blue-500);background:var(--blue-50);font-weight:600}

.reply-bubble{margin-top:10px;padding:10px 14px;background:var(--gray-50);border-left:3px solid var(--blue-200);border-radius:0 8px 8px 0}
.reply-bubble .reply-meta{font-size:11px;color:var(--gray-500);margin-bottom:4px}
</style>
@endpush

@section('content')
<div class="page-header">
  <h1>Surat</h1>
 
</div>

@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">⚠️ {{ session('error') }}</div>@endif

@if($pengantarBelumDiteruskan)
<div class="info-box">
  <span>📨 Ada surat pengantar dari admin yang belum diteruskan ke <strong>{{ $mahasiswa->instansi?->nama ?? 'instansi' }}</strong>.</span>
  <button class="btn btn-primary btn-sm"
    onclick="openTeruskan({{ $pengantarBelumDiteruskan->id }}, {{ json_encode($pengantarBelumDiteruskan->perihal) }})">
    Teruskan Sekarang
  </button>
</div>
@endif

<div class="tab-bar">
  <button type="button" class="tab-btn" id="tabBtnKirim"   onclick="switchTab('kirim')">✉️ Kirim Surat</button>
  <button type="button" class="tab-btn" id="tabBtnMasuk"   onclick="switchTab('masuk')">
    📥 Surat Masuk @if($suratMasuk->count())<span class="badge badge-proses" style="margin-left:4px">{{ $suratMasuk->count() }}</span>@endif
  </button>
  <button type="button" class="tab-btn" id="tabBtnRiwayat" onclick="switchTab('riwayat')">🗂️ Riwayat Terkirim</button>
</div>

{{-- ══ TAB KIRIM ══ --}}
<div class="tab-panel" id="panelKirim">
  @if($errors->kirim->any())
    <div class="err-box"><strong>⚠️ Kesalahan:</strong><ul>@foreach($errors->kirim->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif
  @if($errors->permohonan->any())
    <div class="err-box"><strong>⚠️ Kesalahan:</strong><ul>@foreach($errors->permohonan->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif


  {{-- Form kirim surat bebas --}}
  <div class="card">
    <div class="card-header">
      <h3>✉️ Kirim Surat ke Admin</h3>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('mahasiswa.surat.kirim') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
          <label class="form-label">Tujuan Penerima *</label>
          <div class="recipient-grid">
            <label class="recipient-card {{ old('tujuan_role') === 'admin' ? 'selected' : '' }}"
              onclick="selectRecipient(this)">
              <input type="radio" name="tujuan_role" value="admin"
                {{ old('tujuan_role') === 'admin' ? 'checked' : '' }} required>
              🏛️ Admin
            </label>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Perihal *</label>
          <input type="text" name="perihal" class="form-control"
            placeholder="cth: Pengumpulan Soft File Lengkap"
            value="{{ old('perihal') }}" required>
        </div>
        <div class="form-group">
          <label class="form-label">Isi Surat *</label>
          <textarea name="keterangan" class="form-control" rows="4"
            placeholder="Tulis isi surat..." required>{{ old('keterangan') }}</textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Lampiran <span style="color:var(--gray-400);font-weight:400">(opsional)</span></label>
          <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
          <p class="file-hint">PDF, DOC/DOCX, atau gambar — maks 10 MB</p>
        </div>
        <button type="submit" class="btn btn-primary">Kirim Surat</button>
      </form>
    </div>
  </div>
</div>

{{-- ══ TAB MASUK ══ --}}
<div class="tab-panel" id="panelMasuk">
  @if($errors->balas->any())
    <div class="err-box"><ul>@foreach($errors->balas->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  @if($suratPengantar->count())
  <div class="card" style="margin-bottom:16px">
    <div class="card-header"><h3>📋 Surat Pengantar dari Admin</h3><p>{{ $suratPengantar->count() }} surat</p></div>
    <div class="card-body">
      @foreach($suratPengantar as $s)
        <div class="thread-item">
          <div class="thread-icon in">📋</div>
          <div class="thread-body">
            <div class="thread-head">
              <strong>{{ $s->perihal }}</strong>
              <span class="thread-date">{{ $s->created_at->translatedFormat('d M Y, H:i') }}</span>
            </div>
            <div class="thread-route">Dari: Admin Akademik &middot; Surat Pengantar</div>
            @if($s->keterangan)<p class="text-sm" style="margin-top:6px">{{ $s->keterangan }}</p>@endif
            @if($s->file)<a href="{{ $s->file_url }}" target="_blank" class="file-link">📄 Lihat Surat Pengantar</a>@endif
            <div style="margin-top:10px">
              @if($s->sudahDiteruskan())
                <span class="badge badge-selesai">✅ Sudah diteruskan ke instansi</span>
              @else
                <button class="btn btn-primary btn-sm"
                  onclick="openTeruskan({{ $s->id }}, {{ json_encode($s->perihal) }})">
                  📨 Teruskan ke Instansi
                </button>
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
  @endif

  <div class="card">
    <div class="card-header"><h3>Surat Masuk Lainnya</h3><p>{{ $suratMasuk->count() }} surat</p></div>
    <div class="card-body">
      @forelse($suratMasuk as $s)
        <div class="thread-item">
          <div class="thread-icon in">📥</div>
          <div class="thread-body">
            <div class="thread-head">
              <strong>{{ $s->perihal }}</strong>
              <span class="thread-date">{{ $s->created_at->translatedFormat('d M Y, H:i') }}</span>
            </div>
            <div class="thread-route">Dari: <strong>{{ $s->pengirim_nama }}</strong> &middot; {{ $s->jenis_label }}</div>
            @if($s->keterangan)<p class="text-sm" style="margin-top:6px">{{ $s->keterangan }}</p>@endif
            @if($s->lampiran_list->isNotEmpty())
              <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px">
                @foreach($s->lampiran_list as $lampiran)
                  <a href="{{ $lampiran->file_url }}" target="_blank" class="file-link">📄 {{ $lampiran->nama_asli }}</a>
                @endforeach
              </div>
            @endif
            <div style="margin-top:10px">
              <button class="btn btn-outline btn-sm"
                onclick="openBalas({{ $s->id }}, {{ json_encode($s->pengirim_nama) }}, {{ json_encode($s->perihal) }})">
                ↩️ Balas
              </button>
            </div>
            @foreach($s->balasan ?? [] as $b)
              <div class="reply-bubble">
                <div class="reply-meta">📤 {{ $b->pengirim_nama }} — {{ $b->created_at->format('d M Y, H:i') }}</div>
                <p class="text-sm">{{ $b->keterangan }}</p>
                @if($b->file)<a href="{{ $b->file_url }}" target="_blank" class="file-link">📄 Lampiran</a>@endif
              </div>
            @endforeach
          </div>
        </div>
      @empty
        <div style="text-align:center;padding:40px;color:var(--gray-400)">
          <div style="font-size:36px;margin-bottom:10px">📭</div>
          <p>Belum ada surat masuk.</p>
        </div>
      @endforelse
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
            @if($s->jenis === \App\Models\Surat::JENIS_PERMOHONAN)
              <div style="margin-top:8px">
                <span class="badge {{ $s->status === 'disetujui' ? 'badge-selesai' : ($s->status === 'ditolak' ? '' : 'badge-proses') }}"
                  @if($s->status === 'ditolak') style="background:var(--red-100);color:var(--red-600)" @endif>
                  {{ ucfirst($s->status) }}
                </span>
                @if($s->status === 'ditolak' && $s->catatan)
                  <p class="text-sm" style="color:var(--red-600);margin-top:4px">Alasan: {{ $s->catatan }}</p>
                @endif
              </div>
            @endif
          </div>
        </div>
      @empty
        <div style="text-align:center;padding:40px;color:var(--gray-400)">
          <div style="font-size:36px;margin-bottom:10px">📭</div>
          <p>Belum ada surat terkirim.</p>
        </div>
      @endforelse
    </div>
  </div>
</div>

{{-- MODAL BALAS --}}
<div class="modal-bg" id="modalBalas">
  <div class="modal-box">
    <div class="modal-title" id="balasTitle">↩️ Balas Surat</div>
    @if($errors->balas->any())
      <div class="err-box"><ul>@foreach($errors->balas->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    <form method="POST" id="balasForm" enctype="multipart/form-data">
      @csrf
      <div class="form-group">
        <label class="form-label">Isi Balasan *</label>
        <textarea name="keterangan" class="form-control" rows="4" placeholder="Tulis isi balasan..." required></textarea>
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

{{-- MODAL TERUSKAN --}}
<div class="modal-bg" id="modalTeruskan">
  <div class="modal-box">
    <div class="modal-title" id="teruskanTitle">📨 Teruskan Surat Pengantar</div>
    <form method="POST" id="teruskanForm">
      @csrf
      <p class="form-hint" style="margin-bottom:14px">
        Surat pengantar akan dikirim ke <strong>{{ $mahasiswa->instansi?->nama ?? 'instansi Anda' }}</strong>.
      </p>
      <div class="form-group">
        <label class="form-label">Catatan <span style="color:var(--gray-400);font-weight:400">(opsional)</span></label>
        <textarea name="catatan_teruskan" class="form-control" rows="3" placeholder="Catatan untuk instansi..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalTeruskan')">Batal</button>
        <button type="submit" class="btn btn-primary">Teruskan</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function switchTab(tab) {
  ['kirim','masuk','riwayat'].forEach(t => {
    const c = t[0].toUpperCase() + t.slice(1);
    document.getElementById('panel' + c).classList.toggle('active', t === tab);
    document.getElementById('tabBtn' + c).classList.toggle('active', t === tab);
  });
}
switchTab('{{ $errors->kirim->any() || $errors->permohonan->any() ? "kirim" : ($errors->balas->any() ? "masuk" : "kirim") }}');

function selectRecipient(el) {
  document.querySelectorAll('.recipient-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
}
document.querySelectorAll('.recipient-card').forEach(c => {
  if (c.querySelector('input:checked')) c.classList.add('selected');
});

function openBalas(id, nama, perihal) {
  document.getElementById('balasForm').action = `{{ url('mahasiswa/surat') }}/${id}/balas`;
  document.getElementById('balasTitle').textContent = '↩️ Balas — ' + nama + ' (' + perihal + ')';
  openModal('modalBalas');
}
function openTeruskan(id, perihal) {
  document.getElementById('teruskanForm').action = `{{ url('mahasiswa/surat') }}/${id}/teruskan`;
  document.getElementById('teruskanTitle').textContent = '📨 Teruskan — ' + perihal;
  openModal('modalTeruskan');
}
@if($errors->balas->any())
  window.addEventListener('load', () => openModal('modalBalas'));
@endif
</script>
@endpush
@endsection
