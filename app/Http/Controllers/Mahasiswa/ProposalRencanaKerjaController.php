<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\ProposalRencanaKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use ZipArchive;

class ProposalRencanaKerjaController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa->load('proposalRencanaKerja');

        return view('mahasiswa.proposal-rencana-kerja.index', compact('mahasiswa'));
    }

    public function template()
    {
        if (!class_exists(ZipArchive::class)) {
            abort(500, 'Ekstensi ZIP PHP diperlukan untuk membuat template DOCX.');
        }

        $directory = storage_path('app/temp');
        File::ensureDirectoryExists($directory);
        $path = $directory.'/form-proposal-rencana-kerja-'.uniqid().'.docx';
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');
        $mahasiswa = Auth::user()->mahasiswa->load('instansi');
        $document = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'
            .'<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="28"/></w:rPr><w:t>FORM PROPOSAL RENCANA KERJA PRAKTIK</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Isi seluruh bagian berikut secara jelas, lalu unggah kembali dalam format DOCX melalui SIMOKP.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:rPr><w:b/></w:rPr><w:t>A. IDENTITAS MAHASISWA</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Nama : ................................................................................................</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>NIM : ....................................................................................................</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Program Studi : Ilmu Komputer</w:t></w:r></w:p>'
            .'<w:p><w:r><w:rPr><w:b/></w:rPr><w:t>B. RENCANA KERJA</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>1. Judul / topik rencana kerja:</w:t></w:r></w:p><w:p><w:r><w:t>............................................................................................................</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>2. Latar belakang dan tujuan:</w:t></w:r></w:p><w:p><w:r><w:t>............................................................................................................</w:t></w:r></w:p><w:p><w:r><w:t>............................................................................................................</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>3. Rencana kegiatan, target, dan jadwal pelaksanaan:</w:t></w:r></w:p><w:p><w:r><w:t>............................................................................................................</w:t></w:r></w:p><w:p><w:r><w:t>............................................................................................................</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>4. Luaran yang diharapkan:</w:t></w:r></w:p><w:p><w:r><w:t>............................................................................................................</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Mengetahui,</w:t></w:r></w:p><w:p><w:r><w:t>Mahasiswa,                                      Dosen Pembimbing,</w:t></w:r></w:p><w:p><w:r><w:t>(....................................)                 (....................................)</w:t></w:r></w:p>'
            .'<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440"/></w:sectPr></w:body></w:document>';
        // Format tabel resmi proposal rencana kerja.
        $xml = static fn (?string $value): string => htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $cell = static function (string $value, int $width, ?string $merge = null, ?int $height = null) use ($xml): string {
            $mergeXml = $merge ? '<w:vMerge w:val="'.$merge.'"/>' : '';
            $heightXml = $height ? '<w:tcPr><w:tcW w:w="'.$width.'" w:type="dxa"/>'.$mergeXml.'<w:tcMar><w:top w:w="70" w:type="dxa"/><w:bottom w:w="70" w:type="dxa"/><w:left w:w="90" w:type="dxa"/></w:tcMar></w:tcPr>' : '<w:tcPr><w:tcW w:w="'.$width.'" w:type="dxa"/>'.$mergeXml.'</w:tcPr>';
            return '<w:tc>'.$heightXml.'<w:p><w:r><w:rPr><w:sz w:val="18"/></w:rPr><w:t xml:space="preserve">'.$xml($value).'</w:t></w:r></w:p></w:tc>';
        };
        $row = static fn (string $left, string $right = '', ?string $merge = null, ?int $height = null): string => '<w:tr>'.($height ? '<w:trPr><w:trHeight w:val="'.$height.'" w:hRule="atLeast"/></w:trPr>' : '').$cell($left, 3000, $merge, $height).$cell($right, 6500, null, $height).'</w:tr>';
        $instansi = $mahasiswa->instansi;
        $document = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'
            .'<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="28"/></w:rPr><w:t>FORM PROPOSAL RENCANA KERJA PRAKTIK</w:t></w:r></w:p>'
            .'<w:tbl><w:tblPr><w:tblBorders><w:top w:val="single" w:sz="8"/><w:left w:val="single" w:sz="8"/><w:bottom w:val="single" w:sz="8"/><w:right w:val="single" w:sz="8"/><w:insideH w:val="single" w:sz="8"/><w:insideV w:val="single" w:sz="8"/></w:tblBorders><w:tblW w:w="9500" w:type="dxa"/></w:tblPr><w:tblGrid><w:gridCol w:w="3000"/><w:gridCol w:w="6500"/></w:tblGrid>'
            .$row('Nama Mahasiswa', $mahasiswa->nama)
            .$row('NPM', $mahasiswa->nim)
            .$row('Telpon / no.HP', $mahasiswa->no_hp ?? '')
            .$row('Email', Auth::user()->email)
            .$row('Nama Instansi Tempat KP', $instansi?->nama ?? '')
            .$row('Nama Pimpinan/Kepala Bagian', $instansi?->kontak_person ?? '')
            .$row('Telp/no.HP', $instansi?->no_hp ?? '')
            .$row('Nama Proyek')
            .$row('Rencana Jenis Pekerjaan KP', '1.', 'restart')
            .$row('', '2.', 'continue')
            .$row('', '3.', 'continue')
            .$row('', '4.', 'continue')
            .$row('Rencana mulai KP', 'Tgl.')
            .$row('Rencana selesai KP', 'Tgl.')
            .$row('Tuliskan Rencana Singkat Kegiatan KP yang akan saudara lakukan di bawah ini (bila perlu ditambahkan di kertas lain)', '', null, 2600)
            .'</w:tbl><w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1000" w:right="1000" w:bottom="1000" w:left="1000"/></w:sectPr></w:body></w:document>';
        $zip->addFromString('word/document.xml', $document);
        $zip->close();

        return response()->download($path, 'Form-Proposal-Rencana-Kerja.docx')->deleteFileAfterSend(true);
    }

    public function upload(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $proposal = $mahasiswa->proposalRencanaKerja;

        if ($proposal && $proposal->status !== 'revisi') {
            return back()->with('error', 'Proposal sudah dikirim dan sedang/final diverifikasi. Upload ulang hanya tersedia bila dosen meminta revisi.');
        }

        $validator = Validator::make($request->all(), ['file' => 'required|file|mimes:docx|max:10240'], [
            'file.required' => 'Pilih file proposal terlebih dahulu.',
            'file.mimes' => 'Proposal harus berformat DOCX.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator, 'proposal')->withInput();
        }

        if ($proposal?->file) {
            Storage::disk('public')->delete($proposal->file);
        }
        $uploaded = $request->file('file');
        $data = [
            'file' => $uploaded->store('proposal_rencana_kerja/'.$mahasiswa->id, 'public'),
            'file_asli' => $uploaded->getClientOriginalName(), 'status' => 'menunggu',
            'catatan' => null, 'uploaded_at' => now(), 'diverifikasi_at' => null,
        ];
        $mahasiswa->proposalRencanaKerja()->updateOrCreate([], $data);

        return back()->with('success', 'Proposal rencana kerja berhasil dikirim dan menunggu verifikasi dosen pembimbing. Anda tetap dapat melanjutkan BAB I.');
    }
}
