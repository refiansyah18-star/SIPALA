<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceField;
use Illuminate\Database\Seeder;

class ServiceFieldSeeder extends Seeder
{
    public function run(): void
    {
        // =============================================
        // Kesalahan Nilai — Custom Fields (PRD Section 17)
        // =============================================
        $nilai = Service::where('name', 'Kesalahan nilai')->first();
        if ($nilai) {
            $fields = [
                ['label' => 'Nama Mata Kuliah', 'field_type' => 'text', 'is_required' => true, 'sort_order' => 1, 'placeholder' => 'Masukkan nama mata kuliah'],
                ['label' => 'Semester', 'field_type' => 'select', 'is_required' => true, 'sort_order' => 2, 'options' => json_encode(['Semester 1', 'Semester 2', 'Semester 3', 'Semester 4', 'Semester 5', 'Semester 6', 'Semester 7', 'Semester 8'])],
                ['label' => 'Nilai Saat Ini', 'field_type' => 'text', 'is_required' => true, 'sort_order' => 3, 'placeholder' => 'Contoh: C+'],
                ['label' => 'Nilai Seharusnya', 'field_type' => 'text', 'is_required' => true, 'sort_order' => 4, 'placeholder' => 'Contoh: B+'],
            ];

            foreach ($fields as $field) {
                ServiceField::updateOrCreate(
                    ['service_id' => $nilai->id, 'label' => $field['label']],
                    array_merge($field, ['service_id' => $nilai->id])
                );
            }
        }

        // =============================================
        // Perbaikan Data PDDIKTI — Custom Fields (PRD Section 17)
        // =============================================
        $pddikti = Service::where('name', 'Perbaikan data pada PDDIKTI')->first();
        if ($pddikti) {
            $fields = [
                ['label' => 'Data Salah', 'field_type' => 'textarea', 'is_required' => true, 'sort_order' => 1, 'placeholder' => 'Jelaskan data yang salah pada PDDIKTI'],
                ['label' => 'Data Seharusnya', 'field_type' => 'textarea', 'is_required' => true, 'sort_order' => 2, 'placeholder' => 'Jelaskan data yang benar'],
                ['label' => 'Alasan Perubahan', 'field_type' => 'textarea', 'is_required' => true, 'sort_order' => 3, 'placeholder' => 'Jelaskan alasan perubahan data'],
            ];

            foreach ($fields as $field) {
                ServiceField::updateOrCreate(
                    ['service_id' => $pddikti->id, 'label' => $field['label']],
                    array_merge($field, ['service_id' => $pddikti->id])
                );
            }
        }

        // =============================================
        // Nilai belum masuk — Custom Fields
        // =============================================
        $nilaiBlmMasuk = Service::where('name', 'Nilai belum masuk')->first();
        if ($nilaiBlmMasuk) {
            $fields = [
                ['label' => 'Nama Mata Kuliah', 'field_type' => 'text', 'is_required' => true, 'sort_order' => 1, 'placeholder' => 'Masukkan nama mata kuliah'],
                ['label' => 'Nama Dosen', 'field_type' => 'text', 'is_required' => true, 'sort_order' => 2, 'placeholder' => 'Nama dosen pengampu'],
                ['label' => 'Semester', 'field_type' => 'select', 'is_required' => true, 'sort_order' => 3, 'options' => json_encode(['Semester 1', 'Semester 2', 'Semester 3', 'Semester 4', 'Semester 5', 'Semester 6', 'Semester 7', 'Semester 8'])],
            ];

            foreach ($fields as $field) {
                ServiceField::updateOrCreate(
                    ['service_id' => $nilaiBlmMasuk->id, 'label' => $field['label']],
                    array_merge($field, ['service_id' => $nilaiBlmMasuk->id])
                );
            }
        }
    }
}
