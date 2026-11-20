<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // =============================================
        // SETTINGS
        // =============================================
        $settings = [
            'ticket_prefix' => 'SIPALA',
            'max_chat_words' => '50',
            'default_sla_duration' => '3',
            'default_sla_unit' => 'hari_kerja',
            'app_name' => 'SIPALA',
            'app_description' => 'Sistem Pengaduan dan Layanan Akademik',
        ];

        foreach ($settings as $key => $value) {
            Setting::setValue($key, $value);
        }

        // =============================================
        // DEFAULT SUPER ADMIN (Pustikom)
        // =============================================
        $superAdminType = UserType::where('name', UserType::SUPER_ADMIN)->first();
        User::updateOrCreate(
            ['email' => 'pustikom@sipala.ac.id'],
            [
                'user_type_id' => $superAdminType->id,
                'nama' => 'Super Admin Pustikom',
                'nomor_identitas' => 'SA001',
                'password' => Hash::make('password'),
                'auth_source' => User::AUTH_SOURCE_LOCAL,
                'is_active' => true,
            ]
        );

        // =============================================
        // DEFAULT ADMIN (Manajer)
        // =============================================
        $adminType = UserType::where('name', UserType::ADMIN)->first();
        User::updateOrCreate(
            ['email' => 'admin@sipala.ac.id'],
            [
                'user_type_id' => $adminType->id,
                'nama' => 'Administrator SIPALA',
                'nomor_identitas' => 'ADM001',
                'password' => Hash::make('password'),
                'auth_source' => User::AUTH_SOURCE_LOCAL,
                'is_active' => true,
            ]
        );

        // =============================================
        // DEFAULT STAFF USERS & SPECIALTIES
        // =============================================
        $staffType = UserType::where('name', UserType::STAFF)->first();

        $staffUsers = [
            [
                'nama' => 'Staff Akademik 1',
                'email' => 'staff1@sipala.ac.id',
                'nomor_identitas' => 'STF001',
                'specialties' => ['KRS / KPRS', 'KHS', 'Nilai belum masuk', 'Kesalahan nilai', 'Jadwal kuliah'],
            ],
            [
                'nama' => 'Staff Akademik 2',
                'email' => 'staff2@sipala.ac.id',
                'nomor_identitas' => 'STF002',
                'specialties' => ['Jadwal ujian', 'Presensi/perkuliahan', 'Tugas akhir / skripsi', 'Kerja Praktik (KP)', 'Yudisium', 'Wisuda'],
            ],
            [
                'nama' => 'Staff Data & PDDIKTI',
                'email' => 'staff3@sipala.ac.id',
                'nomor_identitas' => 'STF003',
                'specialties' => ['Perbaikan data pada PDDIKTI', 'Kesalahan nama/data identitas', 'Data akademik mahasiswa', 'Kendala sistem akademik'],
            ],
        ];

        foreach ($staffUsers as $staffData) {
            $specialties = $staffData['specialties'] ?? [];
            unset($staffData['specialties']);

            $user = User::updateOrCreate(
                ['email' => $staffData['email']],
                array_merge($staffData, [
                    'user_type_id' => $staffType->id,
                    'password' => Hash::make('password'),
                    'auth_source' => User::AUTH_SOURCE_LOCAL,
                    'is_active' => true,
                ])
            );

            // Sync specialties
            $serviceIds = \App\Models\Service::whereIn('name', $specialties)->pluck('id');
            if ($serviceIds->isNotEmpty()) {
                $user->staffSpecialties()->sync($serviceIds);
            }
        }

        // =============================================
        // DEMO END-USERS (for testing)
        // =============================================
        $defaultProdiId = \App\Models\ProgramStudy::where('code', 'INF-S1')->value('id')
            ?? \App\Models\ProgramStudy::active()->value('id');

        $mahasiswaType = UserType::where('name', UserType::MAHASISWA)->first();
        User::updateOrCreate(
            ['email' => 'mahasiswa@sipala.ac.id'],
            [
                'user_type_id' => $mahasiswaType->id,
                'nama' => 'Ahmad Mahasiswa',
                'nomor_identitas' => '20260001',
                'password' => Hash::make('password'),
                'auth_source' => User::AUTH_SOURCE_BRIDGE,
                'program_study_id' => $defaultProdiId,
                'is_active' => true,
            ]
        );

        $alumniType = UserType::where('name', UserType::ALUMNI)->first();
        User::updateOrCreate(
            ['email' => 'alumni@sipala.ac.id'],
            [
                'user_type_id' => $alumniType->id,
                'nama' => 'Budi Alumni',
                'nomor_identitas' => '20200001',
                'password' => Hash::make('password'),
                'auth_source' => User::AUTH_SOURCE_LOCAL,
                'program_study_id' => $defaultProdiId,
                'is_active' => true,
            ]
        );

        $dosenType = UserType::where('name', UserType::DOSEN_PEGAWAI)->first();
        User::updateOrCreate(
            ['email' => 'dosen@sipala.ac.id'],
            [
                'user_type_id' => $dosenType->id,
                'nama' => 'Dr. Citra Dosen',
                'nomor_identitas' => '198501012010011001',
                'password' => Hash::make('password'),
                'auth_source' => User::AUTH_SOURCE_BRIDGE,
                'unit_id' => 1,
                'is_active' => true,
            ]
        );
    }
}
