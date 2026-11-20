<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'DIAJUKAN',
                'label' => 'Diajukan',
                'color' => '#3b82f6',    // blue
                'icon' => 'clock',
                'sort_order' => 1,
                'is_default' => true,
                'is_terminal' => false,
            ],
            [
                'name' => 'DIVERIFIKASI',
                'label' => 'Diverifikasi',
                'color' => '#8b5cf6',    // purple
                'icon' => 'check-circle',
                'sort_order' => 2,
                'is_default' => false,
                'is_terminal' => false,
            ],
            [
                'name' => 'DIPROSES',
                'label' => 'Diproses',
                'color' => '#f59e0b',    // amber
                'icon' => 'refresh',
                'sort_order' => 3,
                'is_default' => false,
                'is_terminal' => false,
            ],
            [
                'name' => 'SELESAI',
                'label' => 'Selesai',
                'color' => '#10b981',    // green
                'icon' => 'check-double',
                'sort_order' => 4,
                'is_default' => false,
                'is_terminal' => true,
            ],
            [
                'name' => 'DITOLAK',
                'label' => 'Ditolak',
                'color' => '#ef4444',    // red
                'icon' => 'x-circle',
                'sort_order' => 5,
                'is_default' => false,
                'is_terminal' => true,
            ],
        ];

        foreach ($statuses as $status) {
            Status::updateOrCreate(
                ['name' => $status['name']],
                $status
            );
        }
    }
}
