<?php

namespace Database\Seeders;

use App\Models\UserType;
use Illuminate\Database\Seeder;

class UserTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'MAHASISWA', 'label' => 'Mahasiswa Aktif'],
            ['name' => 'ALUMNI', 'label' => 'Alumni'],
            ['name' => 'DOSEN_PEGAWAI', 'label' => 'Dosen/Pegawai'],
            ['name' => 'STAFF', 'label' => 'Staff'],
            ['name' => 'ADMIN', 'label' => 'Admin'],
            ['name' => 'SUPER_ADMIN', 'label' => 'Super Admin (Pustikom)'],
        ];

        foreach ($types as $type) {
            UserType::updateOrCreate(
                ['name' => $type['name']],
                ['label' => $type['label'], 'is_active' => true]
            );
        }
    }
}
