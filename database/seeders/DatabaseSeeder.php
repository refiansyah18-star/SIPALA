<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Order matters! Dependencies must be seeded first.
     */
    public function run(): void
    {
        $this->call([
            // Phase 1: Master Data
            UserTypeSeeder::class,
            ProgramStudySeeder::class,
            ServiceUnitSeeder::class,
            StatusSeeder::class,

            // Phase 2: Service Management
            ServiceCatalogSeeder::class,       // Categories + Services + Targets
            ServiceRequirementSeeder::class,   // Document requirements
            ServiceFieldSeeder::class,         // Custom form fields

            // Phase 3: Users & Settings
            UserSeeder::class,                 // Admin, Staff, Demo users, Settings

            // Phase 4: Tickets & Workflows
            TicketSeeder::class,               // Demo tickets & assignments
        ]);
    }
}
