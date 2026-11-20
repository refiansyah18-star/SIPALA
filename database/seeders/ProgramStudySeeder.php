<?php

namespace Database\Seeders;

use App\Models\ProgramStudy;
use Illuminate\Database\Seeder;

class ProgramStudySeeder extends Seeder
{
    public function run(): void
    {
        $studies = [
            // Program Sarjana (S1)
            ['code' => 'PAI-S1', 'name' => 'Pendidikan Agama Islam', 'level' => 'S1'],
            ['code' => 'PBA-S1', 'name' => 'Pendidikan Bahasa Arab', 'level' => 'S1'],
            ['code' => 'TBI-S1', 'name' => 'Tadris Bahasa Inggris', 'level' => 'S1'],
            ['code' => 'TIPS-S1', 'name' => 'Tadris Ilmu Pengetahuan Sosial', 'level' => 'S1'],
            ['code' => 'TMAT-S1', 'name' => 'Tadris Matematika', 'level' => 'S1'],
            ['code' => 'TBIO-S1', 'name' => 'Tadris Biologi', 'level' => 'S1'],
            ['code' => 'PGMI-S1', 'name' => 'Pendidikan Guru Madrasah Ibtidaiyah', 'level' => 'S1'],
            ['code' => 'PIAUD-S1', 'name' => 'Pendidikan Islam Anak Usia Dini', 'level' => 'S1'],
            ['code' => 'MPI-S1', 'name' => 'Manajemen Pendidikan Islam', 'level' => 'S1'],
            ['code' => 'TBIN-S1', 'name' => 'Tadris Bahasa Indonesia', 'level' => 'S1'],
            ['code' => 'TKIM-S1', 'name' => 'Tadris Kimia', 'level' => 'S1'],
            ['code' => 'INF-S1', 'name' => 'Informatika', 'level' => 'S1'],
            ['code' => 'MAT-S1', 'name' => 'Matematika', 'level' => 'S1'],
            ['code' => 'HK-S1', 'name' => 'Hukum Keluarga/Akhwal Syaksiyah', 'level' => 'S1'],
            ['code' => 'HES-S1', 'name' => 'Hukum Ekonomi Syariah/Muamalah', 'level' => 'S1'],
            ['code' => 'HTI-S1', 'name' => 'Hukum Tatanegara Islam', 'level' => 'S1'],
            ['code' => 'IF-S1', 'name' => 'Ilmu Falak', 'level' => 'S1'],
            ['code' => 'PBS-S1', 'name' => 'Perbankan Syariah', 'level' => 'S1'],
            ['code' => 'ES-S1', 'name' => 'Ekonomi Syariah', 'level' => 'S1'],
            ['code' => 'AKS-S1', 'name' => 'Akuntansi Syariah', 'level' => 'S1'],
            ['code' => 'PS-S1', 'name' => 'Pariwisata Syariah', 'level' => 'S1'],
            ['code' => 'BIO-S1', 'name' => 'Bioteknologi', 'level' => 'S1'],
            ['code' => 'AFI-S1', 'name' => 'Aqidah dan Filsafat Islam', 'level' => 'S1'],
            ['code' => 'IAT-S1', 'name' => 'Ilmu Al-Quran dan Tafsir', 'level' => 'S1'],
            ['code' => 'IH-S1', 'name' => 'Ilmu Hadis', 'level' => 'S1'],
            ['code' => 'BSA-S1', 'name' => 'Bahasa dan Sastra Arab', 'level' => 'S1'],
            ['code' => 'SPI-S1', 'name' => 'Sejarah Peradaban Islam', 'level' => 'S1'],
            ['code' => 'TP-S1', 'name' => 'Tasawuf dan Psikoterapi', 'level' => 'S1'],
            ['code' => 'KPI-S1', 'name' => 'Komunikasi dan Penyiaran Islam', 'level' => 'S1'],
            ['code' => 'PMI-S1', 'name' => 'Pengembangan Masyarakat Islam', 'level' => 'S1'],
            ['code' => 'BKI-S1', 'name' => 'Bimbingan dan Konseling Islam', 'level' => 'S1'],
            ['code' => 'SA-S1', 'name' => 'Sosiologi Agama', 'level' => 'S1'],

            // Program Pendidikan Jarak Jauh (PJJ S1)
            ['code' => 'PAI-PJJ', 'name' => 'PJJ Pendidikan Agama Islam', 'level' => 'PJJ'],
            ['code' => 'PBA-PJJ', 'name' => 'PJJ Pendidikan Bahasa Arab', 'level' => 'PJJ'],
            ['code' => 'PGMI-PJJ', 'name' => 'PJJ Pendidikan Guru Madrasah Ibtidaiyah', 'level' => 'PJJ'],
            ['code' => 'HK-PJJ', 'name' => 'PJJ Hukum Keluarga', 'level' => 'PJJ'],
            ['code' => 'SPI-PJJ', 'name' => 'PJJ Sejarah Peradaban Islam', 'level' => 'PJJ'],

            // Program Profesi (PPG)
            ['code' => 'PPG', 'name' => 'Pendidikan Profesi Guru', 'level' => 'PPG'],

            // Program Magister (S2)
            ['code' => 'MPI-S2', 'name' => 'Manajemen Pendidikan Islam', 'level' => 'S2'],
            ['code' => 'PAI-S2', 'name' => 'Pendidikan Agama Islam', 'level' => 'S2'],
            ['code' => 'HK-S2', 'name' => 'Hukum Keluarga (Akhwalul Syaksiyah)', 'level' => 'S2'],
            ['code' => 'ES-S2', 'name' => 'Ekonomi Syariah', 'level' => 'S2'],
            ['code' => 'PMI-S2', 'name' => 'Pengembangan Masyarakat Islam', 'level' => 'S2'],
            ['code' => 'SPI-S2', 'name' => 'Sejarah Peradaban Islam', 'level' => 'S2'],
            ['code' => 'PAI-S2-PJJ', 'name' => 'PJJ Pendidikan Agama Islam', 'level' => 'S2'],

            // Program Doktoral (S3)
            ['code' => 'PAI-S3', 'name' => 'Pendidikan Agama Islam', 'level' => 'S3'],
            ['code' => 'HK-S3', 'name' => 'Hukum Keluarga (Akhwalul Syaksiyah)', 'level' => 'S3'],
            ['code' => 'ES-S3', 'name' => 'Ekonomi Syariah', 'level' => 'S3'],
        ];

        $activeCodes = [];

        foreach ($studies as $study) {
            $activeCodes[] = $study['code'];

            ProgramStudy::updateOrCreate(
                ['code' => $study['code']],
                ['name' => $study['name'], 'level' => $study['level'], 'is_active' => true]
            );
        }

        // Nonaktifkan prodi lama yang tidak termasuk dalam daftar resmi agar tidak tampil di dropdown
        // tanpa merusak relasi user/tiket historis.
        ProgramStudy::whereNotIn('code', $activeCodes)->update(['is_active' => false]);
    }
}
