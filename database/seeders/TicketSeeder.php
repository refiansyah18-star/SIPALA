<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\TicketAssignment;
use App\Models\TicketMessage;
use App\Models\TicketStatusHistory;
use App\Models\User;
use App\Models\UserType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $mahasiswa = User::where('email', 'mahasiswa@sipala.ac.id')->first();
        $admin = User::where('email', 'admin@sipala.ac.id')->first();
        $staff1 = User::where('email', 'staff1@sipala.ac.id')->first();
        $staff3 = User::where('email', 'staff3@sipala.ac.id')->first();

        if (!$mahasiswa || !$admin || !$staff1 || !$staff3) {
            return;
        }

        $diajukanStatus = Status::where('name', Status::DIAJUKAN)->first();
        $diverifikasiStatus = Status::where('name', Status::DIVERIFIKASI)->first();
        $diprosesStatus = Status::where('name', Status::DIPROSES)->first();
        $selesaiStatus = Status::where('name', Status::SELESAI)->first();

        // 1. Ticket Baru (DIAJUKAN - belum diverifikasi / belum diassign)
        $srv1 = Service::where('name', 'KRS / KPRS')->first();
        if ($srv1 && $diajukanStatus) {
            $t1 = Ticket::updateOrCreate(
                ['ticket_number' => 'SIPALA-2026-000001'],
                [
                    'user_id' => $mahasiswa->id,
                    'service_id' => $srv1->id,
                    'category_id' => $srv1->category_id,
                    'status_id' => $diajukanStatus->id,
                    'subject' => 'Kendala Pengambilan SKS Matakuliah Pilihan',
                    'description' => 'Saya ingin mengambil matakuliah Pilihan AI namun di portal tertulis kapasitas kelas penuh padahal masih ada kursi.',
                    'sla_duration' => $srv1->sla_duration,
                    'sla_unit' => $srv1->sla_unit,
                    'deadline' => Carbon::now()->addDays($srv1->sla_duration),
                ]
            );

            TicketStatusHistory::firstOrCreate(
                ['ticket_id' => $t1->id, 'to_status_id' => $diajukanStatus->id],
                [
                    'changed_by' => $mahasiswa->id,
                    'note' => 'Tiket pengaduan diajukan oleh mahasiswa.',
                    'created_at' => Carbon::now()->subHours(2),
                ]
            );
        }

        // 2. Ticket Sedang Diproses (DIPROSES - ditugaskan ke Staff Akademik 1 yang ahli Kesalahan Nilai)
        $srv2 = Service::where('name', 'Kesalahan nilai')->first();
        if ($srv2 && $diprosesStatus) {
            $t2 = Ticket::updateOrCreate(
                ['ticket_number' => 'SIPALA-2026-000002'],
                [
                    'user_id' => $mahasiswa->id,
                    'service_id' => $srv2->id,
                    'category_id' => $srv2->category_id,
                    'status_id' => $diprosesStatus->id,
                    'subject' => 'Revisi Nilai Akhir Mata Kuliah Basis Data',
                    'description' => 'Nilai di portal tercatat C+, padahal dosen pengampu sudah memberikan bukti nilai A- di lembar revisi.',
                    'sla_duration' => $srv2->sla_duration,
                    'sla_unit' => $srv2->sla_unit,
                    'deadline' => Carbon::now()->addDays($srv2->sla_duration),
                ]
            );

            // Assignment oleh Admin ke Staff1
            TicketAssignment::firstOrCreate(
                ['ticket_id' => $t2->id, 'staff_id' => $staff1->id],
                [
                    'assigned_by' => $admin->id,
                    'notes' => 'Tolong dicek ke dosen pengampu dan sinkronkan dengan berkas berita acara.',
                    'assigned_at' => Carbon::now()->subHours(5),
                ]
            );

            TicketStatusHistory::firstOrCreate(
                ['ticket_id' => $t2->id, 'to_status_id' => $diprosesStatus->id],
                [
                    'from_status_id' => $diverifikasiStatus?->id,
                    'changed_by' => $staff1->id,
                    'note' => 'Tiket mulai diproses oleh staf.',
                    'created_at' => Carbon::now()->subHours(4),
                ]
            );

            // Chat pesan demo
            TicketMessage::firstOrCreate(
                ['ticket_id' => $t2->id, 'message' => 'Baik mas, sedang kami konfirmasi ke bagian prodi.'],
                [
                    'user_id' => $staff1->id,
                    'created_at' => Carbon::now()->subHours(3),
                ]
            );
        }

        // 3. Ticket PDDIKTI (DIPROSES - ditugaskan ke Staff Data & PDDIKTI)
        $srv3 = Service::where('name', 'Perbaikan data pada PDDIKTI')->first();
        if ($srv3 && $diprosesStatus) {
            $t3 = Ticket::updateOrCreate(
                ['ticket_number' => 'SIPALA-2026-000003'],
                [
                    'user_id' => $mahasiswa->id,
                    'service_id' => $srv3->id,
                    'category_id' => $srv3->category_id,
                    'status_id' => $diprosesStatus->id,
                    'subject' => 'Perbaikan NIK KTP pada Pangkalan Data Dikti',
                    'description' => 'NIK di data PDDIKTI berbeda 1 digit dengan KTP asli sehingga terkendala beasiswa.',
                    'sla_duration' => $srv3->sla_duration,
                    'sla_unit' => $srv3->sla_unit,
                    'deadline' => Carbon::now()->addDays($srv3->sla_duration),
                ]
            );

            TicketAssignment::firstOrCreate(
                ['ticket_id' => $t3->id, 'staff_id' => $staff3->id],
                [
                    'assigned_by' => $admin->id,
                    'notes' => 'Spesialis data PDDIKTI tolong validasi berkas KTP dan update di Feeder PDDIKTI.',
                    'assigned_at' => Carbon::now()->subDays(1),
                ]
            );
        }

        // 4. Ticket Selesai (SELESAI)
        $srv4 = Service::where('name', 'Transkrip akademik')->first();
        if ($srv4 && $selesaiStatus) {
            Ticket::updateOrCreate(
                ['ticket_number' => 'SIPALA-2026-000004'],
                [
                    'user_id' => $mahasiswa->id,
                    'service_id' => $srv4->id,
                    'category_id' => $srv4->category_id,
                    'status_id' => $selesaiStatus->id,
                    'subject' => 'Permintaan Transkrip Akademik Sementara untuk Magang',
                    'description' => 'Membutuhkan transkrip akademik sementara bertanda tangan basah untuk pendaftaran MSIB.',
                    'sla_duration' => $srv4->sla_duration,
                    'sla_unit' => $srv4->sla_unit,
                    'deadline' => Carbon::now()->subDay(),
                    'resolution_note' => 'Transkrip telah selesai dicetak dan dapat diambil di loket pelayanan akademik.',
                    'resolved_at' => Carbon::now()->subHours(6),
                ]
            );
        }
    }
}
