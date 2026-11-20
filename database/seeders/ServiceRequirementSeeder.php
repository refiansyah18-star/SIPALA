<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceRequirement;
use Illuminate\Database\Seeder;

class ServiceRequirementSeeder extends Seeder
{
    public function run(): void
    {
        // =============================================
        // Perbaikan Data PDDIKTI — Requirements (PRD Section 15)
        // =============================================
        $pddikti = Service::where('name', 'Perbaikan data pada PDDIKTI')->first();
        if ($pddikti) {
            $requirements = [
                ['name' => 'KTP', 'description' => 'Scan KTP yang masih berlaku', 'is_required' => true, 'sort_order' => 1],
                ['name' => 'Ijazah', 'description' => 'Scan ijazah asli', 'is_required' => true, 'sort_order' => 2],
                ['name' => 'KK/Akta Kelahiran', 'description' => 'Scan Kartu Keluarga atau Akta Kelahiran', 'is_required' => true, 'sort_order' => 3],
                ['name' => 'Transkrip', 'description' => 'Scan transkrip akademik', 'is_required' => true, 'sort_order' => 4],
                ['name' => 'Surat Pernyataan', 'description' => 'Surat pernyataan bermaterai', 'is_required' => true, 'sort_order' => 5],
            ];

            foreach ($requirements as $req) {
                ServiceRequirement::updateOrCreate(
                    ['service_id' => $pddikti->id, 'name' => $req['name']],
                    array_merge($req, [
                        'service_id' => $pddikti->id,
                        'allowed_file_types' => 'pdf,jpg,jpeg,png',
                        'max_file_size' => 2048,
                        'max_file_count' => 1,
                    ])
                );
            }
        }

        // =============================================
        // Kesalahan Nilai — Requirements
        // =============================================
        $nilai = Service::where('name', 'Kesalahan nilai')->first();
        if ($nilai) {
            $requirements = [
                ['name' => 'KRS Semester Berjalan', 'description' => 'Bukti KRS semester terkait', 'is_required' => true, 'sort_order' => 1],
                ['name' => 'Bukti Nilai', 'description' => 'Screenshot atau foto bukti nilai dari dosen', 'is_required' => false, 'sort_order' => 2],
            ];

            foreach ($requirements as $req) {
                ServiceRequirement::updateOrCreate(
                    ['service_id' => $nilai->id, 'name' => $req['name']],
                    array_merge($req, [
                        'service_id' => $nilai->id,
                        'allowed_file_types' => 'pdf,jpg,jpeg,png',
                        'max_file_size' => 2048,
                        'max_file_count' => 1,
                    ])
                );
            }
        }

        // =============================================
        // Transkrip Akademik — Requirements
        // =============================================
        $transkrip = Service::where('name', 'Transkrip akademik')->first();
        if ($transkrip) {
            ServiceRequirement::updateOrCreate(
                ['service_id' => $transkrip->id, 'name' => 'Kartu Mahasiswa'],
                [
                    'service_id' => $transkrip->id,
                    'name' => 'Kartu Mahasiswa',
                    'description' => 'Scan kartu mahasiswa aktif',
                    'is_required' => true,
                    'allowed_file_types' => 'pdf,jpg,jpeg,png',
                    'max_file_size' => 2048,
                    'max_file_count' => 1,
                    'sort_order' => 1,
                ]
            );
        }

        // =============================================
        // Kesalahan nama/data identitas — Requirements
        // =============================================
        $identitas = Service::where('name', 'Kesalahan nama/data identitas')->first();
        if ($identitas) {
            $requirements = [
                ['name' => 'KTP', 'description' => 'Scan KTP yang masih berlaku', 'is_required' => true, 'sort_order' => 1],
                ['name' => 'Ijazah', 'description' => 'Scan ijazah sebelumnya', 'is_required' => true, 'sort_order' => 2],
                ['name' => 'Akta Kelahiran', 'description' => 'Scan akta kelahiran', 'is_required' => true, 'sort_order' => 3],
            ];

            foreach ($requirements as $req) {
                ServiceRequirement::updateOrCreate(
                    ['service_id' => $identitas->id, 'name' => $req['name']],
                    array_merge($req, [
                        'service_id' => $identitas->id,
                        'allowed_file_types' => 'pdf,jpg,jpeg,png',
                        'max_file_size' => 2048,
                        'max_file_count' => 1,
                    ])
                );
            }
        }
    }
}
