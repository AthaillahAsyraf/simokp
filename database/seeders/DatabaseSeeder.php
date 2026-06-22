<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{User,Dosen,Instansi,Mahasiswa,ProgressBab,Seminar};

class DatabaseSeeder extends Seeder {
    public function run(): void {
        // ADMIN
        User::create(['name'=>'Administrator','email'=>'admin@cs.unila.ac.id','password'=>Hash::make('password'),'role'=>'admin']);

        // DOSEN
        $uD1 = User::create(['name'=>'Dr. Ahmad Rifai, M.Kom','email'=>'ahmad.rifai@cs.unila.ac.id','password'=>Hash::make('password'),'role'=>'dosen']);
        $d1  = Dosen::create(['user_id'=>$uD1->id,'nip'=>'197501012000031001','nama'=>'Dr. Ahmad Rifai, M.Kom','no_hp'=>'081234567890']);

        $uD2 = User::create(['name'=>'Ir. Sartika Dewi, M.T','email'=>'sartika.dewi@cs.unila.ac.id','password'=>Hash::make('password'),'role'=>'dosen']);
        $d2  = Dosen::create(['user_id'=>$uD2->id,'nip'=>'198003152005012002','nama'=>'Ir. Sartika Dewi, M.T','no_hp'=>'082345678901']);

        // INSTANSI
        $uI1 = User::create(['name'=>'PT Teknologi Nusantara','email'=>'hrd@teknusantara.co.id','password'=>Hash::make('password'),'role'=>'instansi']);
        $i1  = Instansi::create(['user_id'=>$uI1->id,'nama'=>'PT Teknologi Nusantara','bidang'=>'Software Development','alamat'=>'Jl. Ahmad Yani No.5, Bandar Lampung','kontak_person'=>'Bpk. Hendra','no_hp'=>'081111222233']);

        $uI2 = User::create(['name'=>'BPJS Ketenagakerjaan','email'=>'it@bpjstk-lampung.go.id','password'=>Hash::make('password'),'role'=>'instansi']);
        $i2  = Instansi::create(['user_id'=>$uI2->id,'nama'=>'BPJS Ketenagakerjaan Cab. Lampung','bidang'=>'IT & Sistem Informasi','alamat'=>'Jl. Kartini No.12, Bandar Lampung','kontak_person'=>'Ibu Sri','no_hp'=>'082222333344']);

        $uI3 = User::create(['name'=>'Dinas Kominfo Lampung','email'=>'it@kominfo.lampungprov.go.id','password'=>Hash::make('password'),'role'=>'instansi']);
        $i3  = Instansi::create(['user_id'=>$uI3->id,'nama'=>'Dinas Kominfo Provinsi Lampung','bidang'=>'Jaringan & Infrastruktur','alamat'=>'Jl. Wolter Monginsidi, Bandar Lampung','kontak_person'=>'Bpk. Anton','no_hp'=>'083333444455']);

        $babs = ['BAB I','BAB II','BAB III','BAB IV','BAB V'];

        // MHS 1 — proses, BAB I & II selesai
        $uM1 = User::create(['name'=>'Budi Santoso','email'=>'2017061001@students.cs.unila.ac.id','password'=>Hash::make('password'),'role'=>'mahasiswa']);
        $m1  = Mahasiswa::create(['user_id'=>$uM1->id,'nim'=>'2017061001','nama'=>'Budi Santoso','angkatan'=>'2020','dosen_id'=>$d1->id,'instansi_id'=>$i1->id,'tanggal_mulai'=>'2026-01-15','status'=>'proses']);
        foreach ($babs as $idx => $bab) {
            ProgressBab::create(['mahasiswa_id'=>$m1->id,'bab'=>$bab,'status'=>$idx < 2 ? 'selesai' : 'belum','tanggal_selesai'=>$idx < 2 ? '2026-02-'.sprintf('%02d',$idx*10+10) : null]);
        }

        // MHS 2 — seminar, semua BAB selesai
        $uM2 = User::create(['name'=>'Siti Rahayu','email'=>'2017061002@students.cs.unila.ac.id','password'=>Hash::make('password'),'role'=>'mahasiswa']);
        $m2  = Mahasiswa::create(['user_id'=>$uM2->id,'nim'=>'2017061002','nama'=>'Siti Rahayu','angkatan'=>'2020','dosen_id'=>$d2->id,'instansi_id'=>$i2->id,'tanggal_mulai'=>'2025-12-01','status'=>'seminar']);
        foreach ($babs as $idx => $bab) {
            ProgressBab::create(['mahasiswa_id'=>$m2->id,'bab'=>$bab,'status'=>'selesai','tanggal_selesai'=>'2026-0'.($idx+1).'-15']);
        }
        Seminar::create(['mahasiswa_id'=>$m2->id,'tanggal'=>'2026-05-15','jam'=>'09:00:00','ruangan'=>'Lab A-301','dosen_penguji'=>'Dr. Ahmad Rifai, M.Kom','status'=>'terjadwal']);

        // MHS 3 — selesai, semua BAB selesai
        $uM3 = User::create(['name'=>'Reza Firmansyah','email'=>'2017061003@students.cs.unila.ac.id','password'=>Hash::make('password'),'role'=>'mahasiswa']);
        $m3  = Mahasiswa::create(['user_id'=>$uM3->id,'nim'=>'2017061003','nama'=>'Reza Firmansyah','angkatan'=>'2021','dosen_id'=>$d1->id,'instansi_id'=>$i1->id,'tanggal_mulai'=>'2025-09-01','tanggal_selesai'=>'2026-01-31','status'=>'selesai']);
        foreach ($babs as $idx => $bab) {
            ProgressBab::create(['mahasiswa_id'=>$m3->id,'bab'=>$bab,'status'=>'selesai','tanggal_selesai'=>'2025-'.($idx+10).'-10']);
        }
        Seminar::create(['mahasiswa_id'=>$m3->id,'tanggal'=>'2026-02-10','jam'=>'13:00:00','ruangan'=>'Lab A-302','dosen_penguji'=>'Ir. Sartika Dewi, M.T','status'=>'selesai']);

        // MHS 4 — proses, BAB I selesai saja
        $uM4 = User::create(['name'=>'Dewi Lestari','email'=>'2017061004@students.cs.unila.ac.id','password'=>Hash::make('password'),'role'=>'mahasiswa']);
        $m4  = Mahasiswa::create(['user_id'=>$uM4->id,'nim'=>'2017061004','nama'=>'Dewi Lestari','angkatan'=>'2021','dosen_id'=>$d2->id,'instansi_id'=>$i3->id,'tanggal_mulai'=>'2026-02-01','status'=>'proses']);
        foreach ($babs as $idx => $bab) {
            ProgressBab::create(['mahasiswa_id'=>$m4->id,'bab'=>$bab,'status'=>$idx < 1 ? 'selesai' : 'belum','tanggal_selesai'=>$idx < 1 ? '2026-03-05' : null]);
        }
    }
}