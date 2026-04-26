<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Dosen;
use App\Models\Instansi;
use App\Models\Mahasiswa;
use App\Models\ProgressBab;
use App\Models\Nilai;
use App\Models\Logbook;
use App\Models\Seminar;
use App\Models\Surat;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── ADMIN ───────────────────────────────────────────
        User::create([
            'name'     => 'Administrator',
            'email'    => 'admin@cs.unila.ac.id',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // ─── DOSEN ───────────────────────────────────────────
        $userDosen1 = User::create([
            'name'     => 'Dr. Ahmad Rifai, M.Kom',
            'email'    => 'ahmad.rifai@cs.unila.ac.id',
            'password' => Hash::make('password'),
            'role'     => 'dosen',
        ]);
        $dosen1 = Dosen::create([
            'user_id' => $userDosen1->id,
            'nip'     => '197501012000031001',
            'nama'    => 'Dr. Ahmad Rifai, M.Kom',
            'bidang'  => 'Rekayasa Perangkat Lunak',
            'no_hp'   => '081234567890',
        ]);

        $userDosen2 = User::create([
            'name'     => 'Ir. Sartika Dewi, M.T',
            'email'    => 'sartika.dewi@cs.unila.ac.id',
            'password' => Hash::make('password'),
            'role'     => 'dosen',
        ]);
        $dosen2 = Dosen::create([
            'user_id' => $userDosen2->id,
            'nip'     => '198003152005012002',
            'nama'    => 'Ir. Sartika Dewi, M.T',
            'bidang'  => 'Sistem Informasi',
            'no_hp'   => '082345678901',
        ]);

        // ─── INSTANSI ─────────────────────────────────────────
        $userInst1 = User::create([
            'name'     => 'PT Teknologi Nusantara',
            'email'    => 'hrd@teknusantara.co.id',
            'password' => Hash::make('password'),
            'role'     => 'instansi',
        ]);
        $inst1 = Instansi::create([
            'user_id'       => $userInst1->id,
            'nama'          => 'PT Teknologi Nusantara',
            'bidang'        => 'Software Development',
            'alamat'        => 'Jl. Ahmad Yani No.5, Bandar Lampung',
            'kontak_person' => 'Bpk. Hendra',
            'no_hp'         => '081234567891',
        ]);

        $userInst2 = User::create([
            'name'     => 'BPJS Ketenagakerjaan Cab. Lampung',
            'email'    => 'it@bpjstk-lampung.go.id',
            'password' => Hash::make('password'),
            'role'     => 'instansi',
        ]);
        $inst2 = Instansi::create([
            'user_id'       => $userInst2->id,
            'nama'          => 'BPJS Ketenagakerjaan Cab. Lampung',
            'bidang'        => 'IT & Sistem Informasi',
            'alamat'        => 'Jl. Kartini No.12, Bandar Lampung',
            'kontak_person' => 'Ibu Sri',
            'no_hp'         => '082345678902',
        ]);

        // ─── MAHASISWA ───────────────────────────────────────
        $babs = ['BAB I', 'BAB II', 'BAB III', 'BAB IV', 'BAB V', 'LAPORAN LENGKAP'];

        // Mahasiswa 1 — proses, progress 50%
        $uMhs1 = User::create([
            'name'     => 'Budi Santoso',
            'email'    => '2017061001@students.cs.unila.ac.id',
            'password' => Hash::make('password'),
            'role'     => 'mahasiswa',
        ]);
        $mhs1 = Mahasiswa::create([
            'user_id'       => $uMhs1->id,
            'nim'           => '2017061001',
            'nama'          => 'Budi Santoso',
            'angkatan'      => '2020',
            'no_hp'         => '085678901234',
            'dosen_id'      => $dosen1->id,
            'instansi_id'   => $inst1->id,
            'tanggal_mulai' => '2026-01-15',
            'status'        => 'proses',
        ]);
        $progress1 = [
            ['BAB I','selesai','2026-02-10','Sudah sesuai format'],
            ['BAB II','selesai','2026-02-25','Revisi minor sudah diperbaiki'],
            ['BAB III','proses',null,'Masih dalam pengerjaan'],
            ['BAB IV','belum',null,null],
            ['BAB V','belum',null,null],
            ['LAPORAN LENGKAP','belum',null,null],
        ];
        foreach ($progress1 as [$bab,$status,$tgl,$cat]) {
            ProgressBab::create(['mahasiswa_id'=>$mhs1->id,'bab'=>$bab,'status'=>$status,'tanggal_update'=>$tgl,'catatan'=>$cat]);
        }
        Nilai::create(['mahasiswa_id' => $mhs1->id]);
        Logbook::create(['mahasiswa_id'=>$mhs1->id,'tanggal'=>'2026-04-24','kegiatan'=>'Mengimplementasikan fitur login','jam_mulai'=>'08:00','jam_selesai'=>'16:00','status_instansi'=>'disetujui']);
        Logbook::create(['mahasiswa_id'=>$mhs1->id,'tanggal'=>'2026-04-23','kegiatan'=>'Desain UI halaman dashboard','jam_mulai'=>'08:00','jam_selesai'=>'16:00','status_instansi'=>'disetujui']);
        Logbook::create(['mahasiswa_id'=>$mhs1->id,'tanggal'=>'2026-04-25','kegiatan'=>'Meeting dengan tim backend membahas API','jam_mulai'=>'08:00','jam_selesai'=>'12:00','status_instansi'=>'pending']);
        Surat::create(['mahasiswa_id'=>$mhs1->id,'jenis'=>'permohonan','status'=>'disetujui']);

        // Mahasiswa 2 — seminar, progress 100%
        $uMhs2 = User::create([
            'name'     => 'Siti Rahayu',
            'email'    => '2017061002@students.cs.unila.ac.id',
            'password' => Hash::make('password'),
            'role'     => 'mahasiswa',
        ]);
        $mhs2 = Mahasiswa::create([
            'user_id'       => $uMhs2->id,
            'nim'           => '2017061002',
            'nama'          => 'Siti Rahayu',
            'angkatan'      => '2020',
            'no_hp'         => '086789012345',
            'dosen_id'      => $dosen2->id,
            'instansi_id'   => $inst2->id,
            'tanggal_mulai' => '2025-12-01',
            'status'        => 'seminar',
        ]);
        foreach ($babs as $i => $bab) {
            ProgressBab::create(['mahasiswa_id'=>$mhs2->id,'bab'=>$bab,'status'=>'selesai','tanggal_update'=>'2026-0'.($i+1).'-10','catatan'=>'Selesai']);
        }
        Nilai::create(['mahasiswa_id'=>$mhs2->id,'nilai_instansi'=>88,'catatan_instansi'=>'Mahasiswa rajin dan disiplin']);
        Seminar::create(['mahasiswa_id'=>$mhs2->id,'tanggal'=>'2026-05-10','jam'=>'09:00:00','ruangan'=>'Lab Komputer A-301','dosen_penguji'=>'Dr. Ahmad Rifai, M.Kom','status'=>'terjadwal']);
        Surat::create(['mahasiswa_id'=>$mhs2->id,'jenis'=>'permohonan','status'=>'disetujui']);

        // Mahasiswa 3 — selesai
        $uMhs3 = User::create([
            'name'     => 'Reza Firmansyah',
            'email'    => '2017061003@students.cs.unila.ac.id',
            'password' => Hash::make('password'),
            'role'     => 'mahasiswa',
        ]);
        $mhs3 = Mahasiswa::create([
            'user_id'        => $uMhs3->id,
            'nim'            => '2017061003',
            'nama'           => 'Reza Firmansyah',
            'angkatan'       => '2021',
            'no_hp'          => '087890123456',
            'dosen_id'       => $dosen1->id,
            'instansi_id'    => $inst1->id,
            'tanggal_mulai'  => '2025-10-01',
            'tanggal_selesai'=> '2026-01-31',
            'status'         => 'selesai',
        ]);
        foreach ($babs as $i => $bab) {
            ProgressBab::create(['mahasiswa_id'=>$mhs3->id,'bab'=>$bab,'status'=>'selesai','tanggal_update'=>'2025-1'.($i+1 > 2 ? '2' : '1').'-'.sprintf('%02d',$i*4+5)]);
        }
        Nilai::create(['mahasiswa_id'=>$mhs3->id,'nilai_instansi'=>92,'catatan_instansi'=>'Sangat baik dan proaktif','nilai_pembimbing'=>88,'catatan_pembimbing'=>'Laporan sangat rapi','nilai_seminar'=>90,'nilai_akhir'=>90.4]);
        Seminar::create(['mahasiswa_id'=>$mhs3->id,'tanggal'=>'2026-02-05','jam'=>'13:00:00','ruangan'=>'Lab Komputer A-302','dosen_penguji'=>'Ir. Sartika Dewi, M.T','status'=>'hadir','nilai'=>90]);
        Surat::create(['mahasiswa_id'=>$mhs3->id,'jenis'=>'keterangan','status'=>'disetujui']);
    }
}