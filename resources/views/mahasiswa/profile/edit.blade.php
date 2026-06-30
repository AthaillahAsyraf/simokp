@extends('layouts.app')
@section('title','Edit Profil')

@section('content')
<div class="page-header page-header-row">
  <div>
    <h1>✏️ Edit Profil</h1>
    <p>Perbarui foto dan informasi profil Anda.</p>
  </div>
  <a href="{{ route('mahasiswa.profile.show') }}" class="btn btn-outline">← Kembali</a>
</div>

@if($errors->any())
  <div class="alert alert-danger" style="margin-bottom:16px">
    ❌
    <div>
      <strong>Terdapat kesalahan:</strong>
      <ul style="margin:4px 0 0 16px;font-size:12px">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  </div>
@endif

<form method="POST" action="{{ route('mahasiswa.profile.update') }}" enctype="multipart/form-data">
  @csrf @method('PUT')

  <div class="grid-2" style="align-items:start">

    {{-- KIRI: FOTO PROFIL --}}
    <div class="card">
      <div class="card-header"><h3>📷 Foto Profil</h3></div>
      <div class="card-body" style="text-align:center">

        <div id="fotoPreviewWrap" style="margin-bottom:16px">
          @if($mahasiswa->foto_profil)
            <img id="fotoPreview"
                 src="{{ Storage::url($mahasiswa->foto_profil) }}"
                 alt="Foto Profil"
                 style="width:140px;height:140px;border-radius:50%;object-fit:cover;
                        border:3px solid var(--purple-200);box-shadow:var(--shadow-md)">
          @else
            <div id="fotoPlaceholder"
                 style="width:140px;height:140px;border-radius:50%;
                        background:linear-gradient(135deg,var(--purple-100),var(--purple-50));
                        border:3px dashed var(--purple-300);
                        display:flex;align-items:center;justify-content:center;
                        font-size:56px;margin:0 auto;cursor:pointer"
                 onclick="document.getElementById('foto_profil').click()">🎓</div>
            <img id="fotoPreview" src="" alt=""
                 style="width:140px;height:140px;border-radius:50%;object-fit:cover;
                        border:3px solid var(--purple-200);box-shadow:var(--shadow-md);display:none">
          @endif
        </div>

        <div style="font-size:12px;color:var(--gray-400);margin-bottom:14px">
          Format: JPG, PNG, WEBP · Maks. 2MB
        </div>

        <label for="foto_profil"
               style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;
                      border-radius:7px;background:var(--purple-600);color:#fff;
                      font-size:13px;font-weight:600;cursor:pointer;transition:all .15s"
               onmouseover="this.style.background='var(--purple-500)'"
               onmouseout="this.style.background='var(--purple-600)'">
          📷 Pilih Foto
        </label>
        <input type="file" id="foto_profil" name="foto_profil"
               accept="image/jpg,image/jpeg,image/png,image/webp"
               style="display:none" onchange="previewFoto(this)">

        @if($mahasiswa->foto_profil)
          <div style="margin-top:12px">
            <label style="display:flex;align-items:center;justify-content:center;gap:6px;
                          font-size:12px;color:var(--red-500);cursor:pointer">
              <input type="checkbox" name="hapus_foto" value="1"
                     style="accent-color:var(--red-500)">
              Hapus foto profil
            </label>
          </div>
        @endif
      </div>
    </div>

    {{-- KANAN: INFO PROFIL --}}
    <div>
      <div class="card" style="margin-bottom:16px">
        <div class="card-header"><h3>ℹ️ Informasi Dasar</h3></div>
        <div class="card-body">

          <div class="form-group">
            <label class="form-label">Nama Lengkap</label>
            <input class="form-control" value="{{ $mahasiswa->nama }}" disabled
                   style="background:var(--gray-50);color:var(--gray-400);cursor:not-allowed">
            <div class="form-hint">Hubungi Admin untuk mengubah nama.</div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">NIM</label>
              <input class="form-control" value="{{ $mahasiswa->nim }}" disabled
                     style="background:var(--gray-50);color:var(--gray-400);cursor:not-allowed">
            </div>
            <div class="form-group">
              <label class="form-label">Angkatan</label>
              <input class="form-control" value="{{ $mahasiswa->angkatan }}" disabled
                     style="background:var(--gray-50);color:var(--gray-400);cursor:not-allowed">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="no_hp">📱 Nomor HP / WhatsApp</label>
            <input type="text" id="no_hp" name="no_hp"
                   class="form-control @error('no_hp') is-invalid @enderror"
                   value="{{ old('no_hp', $mahasiswa->no_hp) }}"
                   placeholder="cth: 0812-3456-7890">
            @error('no_hp')
              <div style="color:var(--red-500);font-size:11px;margin-top:2px">{{ $message }}</div>
            @enderror
          </div>

          <div class="form-group" style="margin-bottom:0">
            <label class="form-label" for="bio">📝 Bio Singkat</label>
            <textarea id="bio" name="bio"
                      class="form-control @error('bio') is-invalid @enderror"
                      rows="3"
                      maxlength="500"
                      placeholder="Ceritakan sedikit tentang diri Anda, minat, atau topik KP..."
                      oninput="updateBioCount(this)">{{ old('bio', $mahasiswa->bio) }}</textarea>
            <div style="display:flex;justify-content:space-between;margin-top:2px">
              <div class="form-hint">Terlihat oleh dosen dan instansi.</div>
              <div id="bioCount" style="font-size:11px;color:var(--gray-400)">
                {{ strlen($mahasiswa->bio ?? '') }}/500
              </div>
            </div>
            @error('bio')
              <div style="color:var(--red-500);font-size:11px;margin-top:2px">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      <div class="card" style="margin-bottom:16px">
        <div class="card-header"><h3>🔐 Akun</h3></div>
        <div class="card-body" style="font-size:13px">
          <div style="display:flex;align-items:center;justify-content:space-between">
            <div>
              <div style="font-size:11px;color:var(--gray-400)">Email login</div>
              <div style="font-weight:600">{{ $mahasiswa->user->email }}</div>
            </div>
            <a href="{{ route('ganti-password') }}" class="btn btn-outline btn-sm">🔐 Ganti Password</a>
          </div>
        </div>
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end">
        <a href="{{ route('mahasiswa.profile.show') }}" class="btn btn-outline">Batal</a>
        <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
      </div>
    </div>

  </div>
</form>
@endsection

@push('scripts')
<script>
function previewFoto(input) {
  if (!input.files || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = function(e) {
    const preview = document.getElementById('fotoPreview');
    const placeholder = document.getElementById('fotoPlaceholder');
    if (placeholder) placeholder.style.display = 'none';
    preview.src = e.target.result;
    preview.style.display = 'block';
  };
  reader.readAsDataURL(input.files[0]);
}

function updateBioCount(el) {
  document.getElementById('bioCount').textContent = el.value.length + '/500';
}
</script>
@endpush