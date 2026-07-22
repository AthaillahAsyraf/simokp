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
.thread-icon{width:38px;height:38px;border-radius:50%;background:var(--purple-50);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.thread-body{flex:1;min-width:0}
.thread-head{display:flex;justify-content:space-between;gap:10px;margin-bottom:4px;flex-wrap:wrap}
.thread-route{font-size:12px;color:var(--gray-500)}
.thread-date{font-size:11px;color:var(--gray-400);white-space:nowrap}

.file-link{display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--blue-600);font-weight:600;padding:5px 10px;border:1px solid var(--blue-100);border-radius:8px;background:var(--blue-50);text-decoration:none}
.file-link:hover{background:var(--blue-100)}
.file-hint{font-size:11px;color:var(--gray-400);margin-top:4px}
.err-box{background:var(--red-50);border:1px solid var(--red-100);border-radius:6px;padding:10px 14px;margin-bottom:14px}
.err-box ul{margin:4px 0 0 18px;color:var(--red-600);font-size:.82rem}

.recipient-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px;margin-top:6px}
.recipient-card{border:1.5px solid var(--gray-200);border-radius:10px;padding:10px 12px;cursor:pointer;transition:all .15s;display:flex;align-items:center;gap:8px;font-size:13px}
.recipient-card:hover{border-color:var(--blue-300);background:var(--blue-50)}
.recipient-card input[type=radio]{accent-color:var(--blue-600)}
.recipient-card.selected{border-color:var(--blue-500);background:var(--blue-50);font-weight:600}
.specific-recipient{display:none;margin-top:10px}
.specific-recipient.visible{display:block}

.broadcast-toggle{display:flex;align-items:center;gap:8px;padding:10px 12px;border:1.5px dashed var(--blue-300);border-radius:10px;background:var(--blue-50);cursor:pointer;font-size:13px;color:var(--blue-700)}
.broadcast-toggle input[type=checkbox]{accent-color:var(--blue-600);width:16px;height:16px;flex-shrink:0}

.filter-row{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.filter-row .form-control{width:auto;min-width:160px}

.thread-replies{margin-top:12px;padding-left:18px;border-left:2px solid var(--blue-100)}
.reply-item{display:flex;gap:10px;padding:10px 0;border-bottom:1px dashed var(--gray-100)}
.reply-item:last-child{border-bottom:none}
.reply-icon{width:28px;height:28px;border-radius:50%;background:var(--blue-50);display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0}
.reply-body{flex:1;min-width:0}
.reply-head{display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap}
.reply-from{font-size:12px;font-weight:600;color:var(--gray-700)}
.reply-date{font-size:11px;color:var(--gray-400);white-space:nowrap}
.reply-text{font-size:12.5px;color:var(--gray-600);margin-top:3px}
.toggle-thread{font-size:11.5px;color:var(--blue-600);font-weight:600;background:none;border:none;cursor:pointer;padding:0;margin-top:8px}
.toggle-thread:hover{text-decoration:underline}
.riwayat-row-balasan{background:var(--blue-50)}
.parent-hint{font-size:11px;color:var(--gray-400);margin-top:2px}
</style>
@endpush

@section('content')
<div class="page-header">
  <h1>Surat</h1>
  <p>Kelola permohonan surat pengantar & kirim korespondensi ke semua pihak</p>
</div>

@if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">⚠️ {{ session('error') }}</div>@endif

<div class="tab-bar">
  <button type="button" class="tab-btn" id="tabBtnPermohonan" onclick="switchTab('permohonan')">
    📥 Permohonan
    @if($permohonanMasuk->where('status','pending')->count())
      <span class="badge badge-proses" style="margin-left:4px">{{ $permohonanMasuk->where('status','pending')->count() }}</span>
    @endif
  </button>
  <button type="button" class="tab-btn" id="tabBtnMasuk"       onclick="switchTab('masuk')">📨 Surat Masuk
    @if($suratMasuk->count())<span class="badge badge-proses" style="margin-left:4px">{{ $suratMasuk->count() }}</span>@endif
  </button>
  <button type="button" class="tab-btn" id="tabBtnKirim"       onclick="switchTab('kirim')">✉️ Kirim Surat</button>
  <button type="button" class="tab-btn" id="tabBtnRiwayat"     onclick="switchTab('riwayat')">🗂️ Semua Riwayat</button>
</div>

{{-- ══ TAB PERMOHONAN ══ --}}
<div class="tab-panel" id="panelPermohonan">
  @if($errors->approve->any())
    <div class="err-box"><ul>@foreach($errors->approve->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif
  <div class="card">
    <div class="card-header"><h3>Permohonan Surat Pengantar</h3><p>{{ $permohonanMasuk->count() }} permohonan</p></div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Mahasiswa</th><th>Perihal</th><th>Keterangan</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
          @forelse($permohonanMasuk as $s)
          <tr>
            <td><strong>{{ $s->mahasiswa?->nama }}</strong><br><code class="text-sm">{{ $s->mahasiswa?->nim }}</code></td>
            <td class="text-sm">{{ $s->perihal }}</td>
            <td class="text-sm text-muted">{{ \Illuminate\Support\Str::limit($s->keterangan, 60) }}</td>
            <td class="text-sm text-muted">{{ $s->created_at->format('d M Y') }}</td>
            <td>
              <span class="badge {{ $s->status === 'disetujui' ? 'badge-selesai' : ($s->status === 'ditolak' ? '' : 'badge-proses') }}"
                @if($s->status === 'ditolak') style="background:var(--red-100);color:var(--red-600)" @endif>
                {{ ucfirst($s->status) }}
              </span>
            </td>
            <td>
              @if($s->status === 'pending')
                <div style="display:flex;gap:4px;flex-wrap:wrap">
                  <button class="btn btn-success btn-xs"
                    onclick="openApprove({{ $s->id }}, {{ json_encode($s->mahasiswa?->nama) }}, {{ json_encode($s->perihal) }})">✅ Setujui</button>
                  <button class="btn btn-danger btn-xs"
                    onclick="openReject({{ $s->id }}, {{ json_encode($s->mahasiswa?->nama) }})">✖️ Tolak</button>
                </div>
              @else
                <span class="text-sm text-muted">Selesai</span>
              @endif
            </td>
          </tr>
          @empty
            <tr><td colspan="6" style="text-align:center;padding:28px;color:var(--gray-400)">Belum ada permohonan.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- ══ TAB SURAT MASUK ══ --}}
<div class="tab-panel" id="panelMasuk">
  @if($errors->balas->any())
    <div class="err-box"><ul>@foreach($errors->balas->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  <form method="GET" class="filter-row">
    <input type="hidden" name="tab" value="masuk">
    <input type="text" name="search_masuk" class="form-control" placeholder="🔍 Cari perihal/isi/pengirim/balasan..." value="{{ $searchMasuk }}">
    <select name="jenis_masuk" class="form-control">
      <option value="">Semua Jenis</option>
      <option value="{{ \App\Models\Surat::JENIS_UMUM }}" {{ $jenisMasuk === \App\Models\Surat::JENIS_UMUM ? 'selected' : '' }}>Surat Umum</option>
      <option value="{{ \App\Models\Surat::JENIS_BALASAN }}" {{ $jenisMasuk === \App\Models\Surat::JENIS_BALASAN ? 'selected' : '' }}>Surat Balasan</option>
      <option value="{{ \App\Models\Surat::JENIS_PENGANTAR }}" {{ $jenisMasuk === \App\Models\Surat::JENIS_PENGANTAR ? 'selected' : '' }}>Surat Pengantar</option>
    </select>
    <select name="status_masuk" class="form-control">
      <option value="">Semua Status</option>
      <option value="dibalas" {{ $statusMasuk === 'dibalas' ? 'selected' : '' }}>Sudah Dibalas</option>
      <option value="belum" {{ $statusMasuk === 'belum' ? 'selected' : '' }}>Belum Dibalas</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
    <a href="{{ route('admin.surat.index') }}#masuk" class="btn btn-outline btn-sm">Reset</a>
  </form>

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
            <div class="thread-route">Dari: <strong>{{ $s->pengirim_nama }}</strong> ({{ ucfirst($s->pengirim_role) }}) &middot; {{ $s->jenis_label }}</div>
            @if($s->keterangan)<p class="text-sm" style="margin-top:6px">{{ $s->keterangan }}</p>@endif
            @if($s->file)<a href="{{ $s->file_url }}" target="_blank" class="file-link">📄 Lihat Lampiran</a>@endif

            @if($s->balasan->count())
              @php $threadId = 'thread' . $s->id; @endphp
              <button type="button" class="toggle-thread" onclick="toggleThread('{{ $threadId }}', this)">
                🗂️ Lihat Riwayat Balasan ({{ $s->balasan->count() }})
              </button>
              <div class="thread-replies" id="{{ $threadId }}" style="display:none">
                @foreach($s->balasan->sortBy('created_at') as $r)
                  <div class="reply-item">
                    <div class="reply-icon">↩️</div>
                    <div class="reply-body">
                      <div class="reply-head">
                        <span class="reply-from">{{ $r->pengirim_nama }} ({{ ucfirst($r->pengirim_role) }}) → {{ $r->penerima_nama }} ({{ ucfirst($r->penerima_role) }})</span>
                        <span class="reply-date">{{ $r->created_at->translatedFormat('d M Y, H:i') }}</span>
                      </div>
                      @if($r->keterangan)<div class="reply-text">{{ $r->keterangan }}</div>@endif
                      @if($r->file)<a href="{{ $r->file_url }}" target="_blank" class="file-link" style="margin-top:6px">📄 Lampiran Balasan</a>@endif

                      @if($r->balasan->count())
                        @foreach($r->balasan->sortBy('created_at') as $r2)
                          <div class="reply-item" style="padding-left:16px;border-left:2px solid var(--gray-100);margin-top:6px">
                            <div class="reply-icon">↩️</div>
                            <div class="reply-body">
                              <div class="reply-head">
                                <span class="reply-from">{{ $r2->pengirim_nama }} ({{ ucfirst($r2->pengirim_role) }}) → {{ $r2->penerima_nama }} ({{ ucfirst($r2->penerima_role) }})</span>
                                <span class="reply-date">{{ $r2->created_at->translatedFormat('d M Y, H:i') }}</span>
                              </div>
                              @if($r2->keterangan)<div class="reply-text">{{ $r2->keterangan }}</div>@endif
                              @if($r2->file)<a href="{{ $r2->file_url }}" target="_blank" class="file-link" style="margin-top:6px">📄 Lampiran Balasan</a>@endif
                            </div>
                          </div>
                        @endforeach
                      @endif
                    </div>
                  </div>
                @endforeach
              </div>
            @endif

            <div style="margin-top:10px">
              <button class="btn btn-outline btn-sm"
                onclick="openBalas({{ $s->id }}, {{ json_encode($s->pengirim_nama) }}, {{ json_encode($s->perihal) }})">
                ↩️ Balas
              </button>
            </div>
          </div>
        </div>
      @empty
        <div style="text-align:center;padding:40px;color:var(--gray-400)">
          <div style="font-size:36px;margin-bottom:10px">📭</div><p>Tidak ada surat masuk yang cocok dengan filter.</p>
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
      <form method="POST" action="{{ route('admin.surat.kirim') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
          <label class="form-label">Tujuan Penerima *</label>
          <div class="recipient-grid">
            <label class="recipient-card {{ old('tujuan_role') === 'mahasiswa' ? 'selected' : '' }}"
              onclick="selectRecipient(this,'mahasiswa')">
              <input type="radio" name="tujuan_role" value="mahasiswa" {{ old('tujuan_role') === 'mahasiswa' ? 'checked' : '' }} required>
              🎓 Mahasiswa
            </label>
            <label class="recipient-card {{ old('tujuan_role') === 'dosen' ? 'selected' : '' }}"
              onclick="selectRecipient(this,'dosen')">
              <input type="radio" name="tujuan_role" value="dosen" {{ old('tujuan_role') === 'dosen' ? 'checked' : '' }}>
              👨‍🏫 Dosen
            </label>
            <label class="recipient-card {{ old('tujuan_role') === 'instansi' ? 'selected' : '' }}"
              onclick="selectRecipient(this,'instansi')">
              <input type="radio" name="tujuan_role" value="instansi" {{ old('tujuan_role') === 'instansi' ? 'checked' : '' }}>
              🏢 Instansi
            </label>
          </div>
          <div class="specific-recipient {{ old('tujuan_role') === 'mahasiswa' ? 'visible' : '' }}" id="dropMahasiswa">
            <label class="broadcast-toggle">
              <input type="checkbox" id="kirimSemuaMahasiswa" name="kirim_semua_mahasiswa" value="1"
                {{ old('kirim_semua_mahasiswa') ? 'checked' : '' }} onchange="toggleKirimSemua(this)">
              <span>📢 Kirim ke <strong>semua mahasiswa</strong> ({{ $listMahasiswa->count() }} mahasiswa)</span>
            </label>
            <div id="pilihMahasiswaWrap" style="{{ old('kirim_semua_mahasiswa') ? 'display:none' : '' }}">
              <label class="form-label" style="font-size:12px;margin-top:8px">Pilih Mahasiswa *</label>
              <select name="tujuan_mahasiswa_id" id="selectMahasiswa" class="form-control">
                <option value="">-- Pilih mahasiswa --</option>
                @foreach($listMahasiswa as $m)
                  <option value="{{ $m->id }}" {{ old('tujuan_mahasiswa_id') == $m->id ? 'selected' : '' }}>
                    {{ $m->nama }} ({{ $m->nim }})
                  </option>
                @endforeach
              </select>
            </div>
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
          <div class="specific-recipient {{ old('tujuan_role') === 'instansi' ? 'visible' : '' }}" id="dropInstansi">
            <label class="form-label" style="font-size:12px;margin-top:8px">Pilih Instansi *</label>
            <select name="tujuan_instansi_id" class="form-control">
              <option value="">-- Pilih instansi --</option>
              @foreach($listInstansi as $i)
                <option value="{{ $i->id }}" {{ old('tujuan_instansi_id') == $i->id ? 'selected' : '' }}>{{ $i->nama }}</option>
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
          <label class="form-label">Lampiran <span style="color:var(--gray-400);font-weight:400">(opsional, maksimal 10 file)</span></label>
          <input type="file" name="files[]" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>
          <p class="file-hint">PDF, DOC/DOCX, atau gambar — maksimal 10 MB per file. Semua lampiran dikirim ke setiap mahasiswa pada mode kirim ke semua.</p>
        </div>
        <button type="submit" class="btn btn-primary">Kirim Surat</button>
      </form>
    </div>
  </div>
</div>

{{-- ══ TAB RIWAYAT ══ --}}
<div class="tab-panel" id="panelRiwayat">

  <form method="GET" class="filter-row">
    <input type="hidden" name="tab" value="riwayat">
    <input type="text" name="search_riwayat" class="form-control" placeholder="🔍 Cari perihal/isi/mahasiswa..." value="{{ $searchRiwayat }}">
    <select name="jenis_riwayat" class="form-control">
      <option value="">Semua Jenis</option>
      <option value="{{ \App\Models\Surat::JENIS_PERMOHONAN }}" {{ $jenisRiwayat === \App\Models\Surat::JENIS_PERMOHONAN ? 'selected' : '' }}>Permohonan</option>
      <option value="{{ \App\Models\Surat::JENIS_PENGANTAR }}" {{ $jenisRiwayat === \App\Models\Surat::JENIS_PENGANTAR ? 'selected' : '' }}>Pengantar</option>
      <option value="{{ \App\Models\Surat::JENIS_BALASAN }}" {{ $jenisRiwayat === \App\Models\Surat::JENIS_BALASAN ? 'selected' : '' }}>Balasan</option>
      <option value="{{ \App\Models\Surat::JENIS_UMUM }}" {{ $jenisRiwayat === \App\Models\Surat::JENIS_UMUM ? 'selected' : '' }}>Umum</option>
    </select>
    <select name="status_riwayat" class="form-control">
      <option value="">Semua Status</option>
      <option value="{{ \App\Models\Surat::STATUS_PENDING }}" {{ $statusRiwayat === \App\Models\Surat::STATUS_PENDING ? 'selected' : '' }}>Pending</option>
      <option value="{{ \App\Models\Surat::STATUS_DISETUJUI }}" {{ $statusRiwayat === \App\Models\Surat::STATUS_DISETUJUI ? 'selected' : '' }}>Disetujui</option>
      <option value="{{ \App\Models\Surat::STATUS_DITOLAK }}" {{ $statusRiwayat === \App\Models\Surat::STATUS_DITOLAK ? 'selected' : '' }}>Ditolak</option>
      <option value="{{ \App\Models\Surat::STATUS_TERKIRIM }}" {{ $statusRiwayat === \App\Models\Surat::STATUS_TERKIRIM ? 'selected' : '' }}>Terkirim</option>
    </select>
    <select name="dari_riwayat" class="form-control">
      <option value="">Semua Pengirim</option>
      <option value="admin"     {{ $dariRiwayat === 'admin'     ? 'selected' : '' }}>Admin</option>
      <option value="mahasiswa" {{ $dariRiwayat === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
      <option value="dosen"     {{ $dariRiwayat === 'dosen'     ? 'selected' : '' }}>Dosen</option>
      <option value="instansi"  {{ $dariRiwayat === 'instansi'  ? 'selected' : '' }}>Instansi</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
    <a href="{{ route('admin.surat.index') }}#riwayat" class="btn btn-outline btn-sm">Reset</a>
  </form>

  <div class="card">
    <div class="card-header"><h3>Semua Riwayat Surat</h3><p>{{ $semuaRiwayat->count() }} surat</p></div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Mahasiswa</th><th>Perihal</th><th>Dari</th><th>Ke</th><th>Jenis</th><th>Lampiran</th><th>Status</th><th>Tanggal</th></tr>
        </thead>
        <tbody>
          @forelse($semuaRiwayat as $s)
          <tr class="{{ $s->parent_id ? 'riwayat-row-balasan' : '' }}">
            <td><strong>{{ $s->mahasiswa?->nama ?? '–' }}</strong></td>
            <td class="text-sm">
              {{ $s->perihal }}
              @if($s->parent_id)
                <div class="parent-hint">↩️ Balasan dari: {{ $s->parent?->perihal ?? '—' }}</div>
              @endif
            </td>
            <td class="text-sm text-muted">{{ $s->pengirim_nama }}</td>
            <td class="text-sm text-muted">{{ $s->penerima_nama }}</td>
            <td class="text-sm">{{ $s->jenis_label }}</td>
            <td>
              @forelse($s->lampiran_list as $lampiran)
                <a href="{{ $lampiran->file_url }}" target="_blank" class="file-link" style="margin:2px">📄 {{ $lampiran->nama_asli }}</a>
              @empty
                <span class="text-muted">–</span>
              @endforelse
            </td>
            <td>
              @if($s->jenis === \App\Models\Surat::JENIS_PERMOHONAN)
                <span class="badge {{ $s->status === 'disetujui' ? 'badge-selesai' : ($s->status === 'ditolak' ? '' : 'badge-proses') }}"
                  @if($s->status === 'ditolak') style="background:var(--red-100);color:var(--red-600)" @endif>
                  {{ ucfirst($s->status) }}
                </span>
              @else<span class="text-muted text-sm">–</span>@endif
            </td>
            <td class="text-sm text-muted">{{ $s->created_at->format('d M Y, H:i') }}</td>
          </tr>
          @empty
            <tr><td colspan="8" style="text-align:center;padding:28px;color:var(--gray-400)">Tidak ada riwayat yang cocok dengan filter.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- MODAL APPROVE --}}
<div class="modal-bg" id="modalApprove">
  <div class="modal-box">
    <div class="modal-title" id="apTitle">✅ Setujui & Upload Surat Pengantar</div>
    <form method="POST" id="approveForm" enctype="multipart/form-data">
      @csrf
      <div class="form-group">
        <label class="form-label">File Surat Pengantar <span style="color:var(--gray-400);font-weight:400">(opsional)</span></label>
        <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx">
        <p class="file-hint">Upload file surat pengantar resmi bila sudah tersedia — PDF/DOC/DOCX maks 10 MB</p>
      </div>
      <div class="form-group">
        <label class="form-label">Catatan untuk Mahasiswa <span style="color:var(--gray-400);font-weight:400">(opsional)</span></label>
        <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalApprove')">Batal</button>
        <button type="submit" class="btn btn-success">Setujui & Kirim</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL REJECT --}}
<div class="modal-bg" id="modalReject">
  <div class="modal-box">
    <div class="modal-title" id="rjTitle">✖️ Tolak Permohonan</div>
    <form method="POST" id="rejectForm">
      @csrf
      <div class="form-group">
        <label class="form-label">Alasan Penolakan *</label>
        <textarea name="catatan" class="form-control" rows="3" placeholder="Tulis alasan penolakan..." required></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalReject')">Batal</button>
        <button type="submit" class="btn btn-danger">Tolak</button>
      </div>
    </form>
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

@push('scripts')
<script>
function switchTab(tab) {
  ['permohonan','masuk','kirim','riwayat'].forEach(t => {
    const c = t[0].toUpperCase() + t.slice(1);
    document.getElementById('panel' + c).classList.toggle('active', t === tab);
    document.getElementById('tabBtn'  + c).classList.toggle('active', t === tab);
  });
}

function toggleThread(id, btn) {
  const el = document.getElementById(id);
  const showing = el.style.display !== 'none';
  el.style.display = showing ? 'none' : 'block';
  btn.textContent = btn.textContent.replace(showing ? '🔽' : '🗂️', showing ? '🗂️' : '🔽');
}

(function () {
  const params = new URLSearchParams(window.location.search);
  const hashTab = window.location.hash.replace('#', '');
  const tabFromQuery = params.get('tab');
  const initialTab = tabFromQuery || hashTab || 'permohonan';
  switchTab(['permohonan','masuk','kirim','riwayat'].includes(initialTab) ? initialTab : 'permohonan');
})();

function selectRecipient(el, role) {
  document.querySelectorAll('.recipient-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  ['dropMahasiswa','dropDosen','dropInstansi'].forEach(id =>
    document.getElementById(id).classList.remove('visible'));
  if (role === 'mahasiswa') document.getElementById('dropMahasiswa').classList.add('visible');
  if (role === 'dosen')     document.getElementById('dropDosen').classList.add('visible');
  if (role === 'instansi')  document.getElementById('dropInstansi').classList.add('visible');
}
document.querySelectorAll('.recipient-card').forEach(c => {
  if (c.querySelector('input:checked')) c.classList.add('selected');
});

function toggleKirimSemua(checkbox) {
  const wrap   = document.getElementById('pilihMahasiswaWrap');
  const select = document.getElementById('selectMahasiswa');
  if (checkbox.checked) {
    wrap.style.display = 'none';
    select.value = '';
  } else {
    wrap.style.display = '';
  }
}
(function () {
  const cb = document.getElementById('kirimSemuaMahasiswa');
  if (cb && cb.checked) toggleKirimSemua(cb);
})();

function openApprove(id, nama, perihal) {
  document.getElementById('approveForm').action = `{{ url('admin/surat') }}/${id}/approve`;
  document.getElementById('apTitle').textContent  = '✅ Setujui — ' + nama + ' (' + perihal + ')';
  openModal('modalApprove');
}
function openReject(id, nama) {
  document.getElementById('rejectForm').action = `{{ url('admin/surat') }}/${id}/reject`;
  document.getElementById('rjTitle').textContent  = '✖️ Tolak — ' + nama;
  openModal('modalReject');
}
function openBalas(id, nama, perihal) {
  document.getElementById('balasForm').action = `{{ url('admin/surat') }}/${id}/balas`;
  document.getElementById('balasTitle').textContent = '↩️ Balas — ' + nama + ' (' + perihal + ')';
  openModal('modalBalas');
}
@if($errors->approve->any())
  window.addEventListener('load', () => { openModal('modalApprove'); switchTab('permohonan'); });
@endif
@if($errors->balas->any())
  window.addEventListener('load', () => { openModal('modalBalas'); switchTab('masuk'); });
@endif
@if($errors->kirim->any())
  window.addEventListener('load', () => switchTab('kirim'));
@endif
</script>
@endpush
@endsection
