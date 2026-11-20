<?php

namespace Database\Seeders;

use App\Models\ServiceUnit;
use Illuminate\Database\Seeder;

class ServiceUnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Rektorat', 'type' => 'Rektorat'],
            ['name' => 'Lembaga Penjaminan Mutu', 'type' => 'Lembaga'],
            ['name' => 'Lembaga Penelitian dan Pengabdian Masyarakat', 'type' => 'Lembaga'],
            ['name' => 'Satuan Pengawas Internal', 'type' => 'Lembaga'],
            ['name' => 'Pusat Pengembangan Bahasa', 'type' => 'Pusat'],
            ['name' => 'Pusat Teknologi Informasi dan Pangkalan Data', 'type' => 'Pusat'],
            ['name' => 'Pusat Pengembangan Bisnis', 'type' => 'Pusat'],
            ['name' => 'Perpustakaan', 'type' => 'Pusat'],
            ['name' => 'Mahad Aljamiah', 'type' => 'Unit'],
            ['name' => 'Pusat Laboratorium Terpadu', 'type' => 'Pusat'],
            ['name' => 'Fakultas Tarbiyah dan Ilmu Keguruan', 'type' => 'Fakultas'],
            ['name' => 'Fakultas Syariah', 'type' => 'Fakultas'],
            ['name' => 'Fakultas Ekonomi dan Bisnis Islam', 'type' => 'Fakultas'],
            ['name' => 'Fakultas Ushuluddin, Adab, dan Dakwah', 'type' => 'Fakultas'],
            ['name' => 'Fakultas Sains dan Teknologi', 'type' => 'Fakultas'],
            ['name' => 'Biro Akademik dan Kemahasiswaan', 'type' => 'Biro'],
            ['name' => 'Biro Administrasi Umum, Perencanaan, dan Keuangan', 'type' => 'Biro'],
        ];

        foreach ($units as $unit) {
            ServiceUnit::updateOrCreate(
                ['name' => $unit['name']],
                ['type' => $unit['type'], 'is_active' => true]
            );
        }
    }
}
