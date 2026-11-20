<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceTarget;
use App\Models\UserType;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // =============================================
        // CATEGORIES
        // =============================================
        $categories = [
            'akademik' => Category::updateOrCreate(
                ['name' => 'Layanan Akademik'],
                ['description' => 'Layanan terkait kegiatan akademik', 'icon' => 'book-open', 'sort_order' => 1, 'is_active' => true]
            ),
            'data' => Category::updateOrCreate(
                ['name' => 'Data & Dokumen'],
                ['description' => 'Layanan terkait data dan dokumen akademik', 'icon' => 'folder', 'sort_order' => 2, 'is_active' => true]
            ),
            'sistem' => Category::updateOrCreate(
                ['name' => 'Kendala Sistem'],
                ['description' => 'Kendala dan permasalahan sistem akademik', 'icon' => 'desktop', 'sort_order' => 3, 'is_active' => true]
            ),
            'kelulusan' => Category::updateOrCreate(
                ['name' => 'Kelulusan & Wisuda'],
                ['description' => 'Layanan terkait yudisium, wisuda, dan kelulusan', 'icon' => 'award', 'sort_order' => 4, 'is_active' => true]
            ),
            'alumni' => Category::updateOrCreate(
                ['name' => 'Layanan Alumni'],
                ['description' => 'Layanan khusus untuk alumni', 'icon' => 'users', 'sort_order' => 5, 'is_active' => true]
            ),
            'dosen' => Category::updateOrCreate(
                ['name' => 'Layanan Dosen/Pegawai'],
                ['description' => 'Layanan khusus untuk dosen dan pegawai', 'icon' => 'briefcase', 'sort_order' => 6, 'is_active' => true]
            ),
        ];

        // Get user type IDs
        $mahasiswa = UserType::where('name', 'MAHASISWA')->first();
        $alumni = UserType::where('name', 'ALUMNI')->first();
        $dosenPegawai = UserType::where('name', 'DOSEN_PEGAWAI')->first();

        // =============================================
        // MAHASISWA SERVICES (PRD Section 9)
        // =============================================
        $mahasiswaServices = [
            ['name' => 'KRS / KPRS', 'category' => 'akademik', 'sla' => 3, 'desc' => 'Pengaduan terkait Kartu Rencana Studi atau perubahan KRS'],
            ['name' => 'KHS', 'category' => 'akademik', 'sla' => 3, 'desc' => 'Pengaduan terkait Kartu Hasil Studi'],
            ['name' => 'Nilai belum masuk', 'category' => 'akademik', 'sla' => 5, 'desc' => 'Pelaporan nilai mata kuliah yang belum diinput oleh dosen'],
            ['name' => 'Kesalahan nilai', 'category' => 'akademik', 'sla' => 5, 'desc' => 'Pelaporan kesalahan pada nilai yang sudah tertera di sistem'],
            ['name' => 'Jadwal kuliah', 'category' => 'akademik', 'sla' => 2, 'desc' => 'Kendala atau perubahan jadwal perkuliahan'],
            ['name' => 'Jadwal ujian', 'category' => 'akademik', 'sla' => 2, 'desc' => 'Kendala atau informasi jadwal ujian'],
            ['name' => 'Presensi/perkuliahan', 'category' => 'akademik', 'sla' => 3, 'desc' => 'Kendala presensi atau perkuliahan'],
            ['name' => 'Dosen pengampu', 'category' => 'akademik', 'sla' => 3, 'desc' => 'Informasi atau kendala terkait dosen pengampu mata kuliah'],
            ['name' => 'Kartu mahasiswa', 'category' => 'data', 'sla' => 5, 'desc' => 'Permohonan cetak ulang atau perbaikan kartu mahasiswa'],
            ['name' => 'Transkrip akademik', 'category' => 'data', 'sla' => 5, 'desc' => 'Permohonan atau perbaikan transkrip akademik'],
            ['name' => 'Surat/keterangan akademik', 'category' => 'data', 'sla' => 3, 'desc' => 'Permohonan surat atau keterangan akademik'],
            ['name' => 'Pendaftaran mata kuliah', 'category' => 'akademik', 'sla' => 2, 'desc' => 'Kendala pendaftaran atau pengambilan mata kuliah'],
            ['name' => 'Tugas akhir / skripsi', 'category' => 'akademik', 'sla' => 5, 'desc' => 'Kendala terkait proses tugas akhir atau skripsi'],
            ['name' => 'Kerja Praktik (KP)', 'category' => 'akademik', 'sla' => 5, 'desc' => 'Kendala terkait kegiatan kerja praktik'],
            ['name' => 'Yudisium', 'category' => 'kelulusan', 'sla' => 5, 'desc' => 'Kendala atau informasi terkait yudisium'],
            ['name' => 'Wisuda', 'category' => 'kelulusan', 'sla' => 5, 'desc' => 'Kendala atau informasi terkait wisuda'],
            ['name' => 'Data akademik mahasiswa', 'category' => 'data', 'sla' => 3, 'desc' => 'Perbaikan atau perubahan data akademik mahasiswa'],
            ['name' => 'Kendala sistem akademik', 'category' => 'sistem', 'sla' => 2, 'desc' => 'Kendala teknis pada sistem akademik'],
        ];

        foreach ($mahasiswaServices as $s) {
            $service = Service::updateOrCreate(
                ['name' => $s['name'], 'category_id' => $categories[$s['category']]->id],
                [
                    'description' => $s['desc'],
                    'sla_duration' => $s['sla'],
                    'sla_unit' => 'hari_kerja',
                    'is_active' => true,
                ]
            );
            ServiceTarget::updateOrCreate(
                ['service_id' => $service->id, 'user_type_id' => $mahasiswa->id]
            );
        }

        // =============================================
        // DOSEN/PEGAWAI SERVICES (PRD Section 10)
        // =============================================
        $dosenServices = [
            ['name' => 'Data dosen pada sistem akademik', 'category' => 'dosen', 'sla' => 3, 'desc' => 'Pengelolaan data dosen pada sistem akademik'],
            ['name' => 'Kesalahan data dosen', 'category' => 'dosen', 'sla' => 3, 'desc' => 'Pelaporan kesalahan data dosen'],
            ['name' => 'Input/perubahan nilai mahasiswa', 'category' => 'dosen', 'sla' => 3, 'desc' => 'Kendala input atau perubahan nilai mahasiswa'],
            ['name' => 'KRS/KHS mahasiswa', 'category' => 'dosen', 'sla' => 3, 'desc' => 'Kendala terkait KRS/KHS mahasiswa dari sisi dosen'],
            ['name' => 'Jadwal mengajar', 'category' => 'dosen', 'sla' => 2, 'desc' => 'Kendala atau perubahan jadwal mengajar'],
            ['name' => 'Presensi perkuliahan', 'category' => 'dosen', 'sla' => 3, 'desc' => 'Kendala presensi perkuliahan dari sisi dosen'],
            ['name' => 'Data mata kuliah', 'category' => 'dosen', 'sla' => 3, 'desc' => 'Pengelolaan data mata kuliah'],
            ['name' => 'Pengelolaan kelas', 'category' => 'dosen', 'sla' => 3, 'desc' => 'Kendala pengelolaan kelas'],
            ['name' => 'Kendala sistem akademik dosen', 'category' => 'sistem', 'sla' => 2, 'desc' => 'Kendala teknis sistem akademik dari sisi dosen'],
            ['name' => 'Beban mengajar / pembagian mata kuliah', 'category' => 'dosen', 'sla' => 5, 'desc' => 'Kendala terkait beban mengajar atau pembagian mata kuliah'],
            ['name' => 'Validasi data akademik', 'category' => 'dosen', 'sla' => 3, 'desc' => 'Permohonan validasi data akademik'],
            ['name' => 'Kendala pengisian laporan akademik', 'category' => 'dosen', 'sla' => 3, 'desc' => 'Kendala dalam pengisian laporan akademik'],
        ];

        foreach ($dosenServices as $s) {
            $service = Service::updateOrCreate(
                ['name' => $s['name'], 'category_id' => $categories[$s['category']]->id],
                [
                    'description' => $s['desc'],
                    'sla_duration' => $s['sla'],
                    'sla_unit' => 'hari_kerja',
                    'is_active' => true,
                ]
            );
            ServiceTarget::updateOrCreate(
                ['service_id' => $service->id, 'user_type_id' => $dosenPegawai->id]
            );
        }

        // =============================================
        // ALUMNI SERVICES (PRD Section 11)
        // =============================================
        $alumniServices = [
            ['name' => 'Perbaikan data alumni', 'category' => 'alumni', 'sla' => 5, 'desc' => 'Permohonan perbaikan data alumni'],
            ['name' => 'Perbaikan data pada PDDIKTI', 'category' => 'alumni', 'sla' => 7, 'desc' => 'Permohonan perbaikan data pada PDDIKTI'],
            ['name' => 'Kesalahan nama/data identitas', 'category' => 'alumni', 'sla' => 5, 'desc' => 'Perbaikan kesalahan nama atau data identitas alumni'],
            ['name' => 'Perbaikan riwayat pendidikan', 'category' => 'alumni', 'sla' => 5, 'desc' => 'Perbaikan riwayat pendidikan alumni'],
            ['name' => 'Data kelulusan', 'category' => 'alumni', 'sla' => 5, 'desc' => 'Kendala atau perbaikan data kelulusan'],
            ['name' => 'Data ijazah', 'category' => 'alumni', 'sla' => 5, 'desc' => 'Kendala terkait data ijazah'],
            ['name' => 'Data transkrip', 'category' => 'alumni', 'sla' => 5, 'desc' => 'Kendala terkait data transkrip alumni'],
            ['name' => 'Verifikasi data akademik alumni', 'category' => 'alumni', 'sla' => 5, 'desc' => 'Permohonan verifikasi data akademik alumni'],
            ['name' => 'Surat/keterangan akademik alumni', 'category' => 'alumni', 'sla' => 3, 'desc' => 'Permohonan surat atau keterangan akademik untuk alumni'],
            ['name' => 'Permintaan dokumen akademik', 'category' => 'alumni', 'sla' => 5, 'desc' => 'Permohonan dokumen akademik alumni'],
            ['name' => 'Kendala pencatatan status kelulusan', 'category' => 'alumni', 'sla' => 5, 'desc' => 'Kendala pada pencatatan status kelulusan'],
            ['name' => 'Data wisuda', 'category' => 'alumni', 'sla' => 5, 'desc' => 'Kendala atau perbaikan data wisuda'],
            ['name' => 'Data program studi/fakultas', 'category' => 'alumni', 'sla' => 5, 'desc' => 'Kendala data program studi atau fakultas alumni'],
        ];

        foreach ($alumniServices as $s) {
            $service = Service::updateOrCreate(
                ['name' => $s['name'], 'category_id' => $categories[$s['category']]->id],
                [
                    'description' => $s['desc'],
                    'sla_duration' => $s['sla'],
                    'sla_unit' => 'hari_kerja',
                    'is_active' => true,
                ]
            );
            ServiceTarget::updateOrCreate(
                ['service_id' => $service->id, 'user_type_id' => $alumni->id]
            );
        }
    }
}
