@extends('layouts.app')

@section('title', 'Tugas Tiket Saya')
@section('page-title', 'Tiket Penugasan & Spesialisasi')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 22px; font-weight: 700; color: var(--gray-900); margin-bottom: 4px;">
                <i class="fas fa-tasks" style="color: var(--primary); margin-right: 8px;"></i>
                Tiket Ditugaskan Kepada Saya
            </h2>
            <p style="color: var(--gray-500); font-size: 14px; margin: 0;">
                Kelola dan tindak lanjuti tiket-tiket yang didelegasikan oleh Manajer Layanan kepada Anda.
            </p>
        </div>
    </div>

    <!-- Spesialisasi Saya Info Banner -->
    <div class="card" style="margin-bottom: 24px; padding: 16px 20px; background: linear-gradient(to right, var(--surface), var(--primary-50));">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="font-size: 12px; color: var(--primary); font-weight: 700;">
                    <i class="fas fa-certificate"></i> Bidang Spesialisasi Anda:
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px;">
                    @forelse(auth()->user()->staffSpecialties as $spec)
                        <span style="background: white; border: 1px solid var(--primary-200); color: var(--primary-dark); font-weight: 600; font-size: 12px; padding: 3px 10px; border-radius: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                            <i class="fas fa-check" style="font-size: 12px;"></i> {{ $spec->name }}
                        </span>
                    @empty
                        <span style="font-size: 14px; color: var(--gray-500); font-style: italic;">
                            Belum ada spesialisasi khusus yang ditetapkan manajer.
                        </span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Status -->
    <div class="card" style="margin-bottom: 24px; padding: 14px 20px;">
        <form method="GET" action="{{ route('staff.tickets.index') }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <span style="font-size: 14px; font-weight: 600; color: var(--gray-700);">Filter Status:</span>
            <a href="{{ route('staff.tickets.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline' }}">
                Semua
            </a>
            @foreach($statuses as $st)
                <a href="{{ route('staff.tickets.index', ['status' => $st->name]) }}" 
                   class="btn btn-sm {{ request('status') == $st->name ? 'btn-primary' : 'btn-outline' }}">
                    {{ $st->label }}
                </a>
            @endforeach
        </form>
    </div>

    <!-- Tabel Tiket Ditugaskan -->
    <div class="card" style="margin-bottom: 30px;">
        <div class="card-header">
            <h2>Daftar Tiket Aktif Saya</h2>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 150px;">No. Tiket</th>
                        <th>Pemohon</th>
                        <th>Layanan & Judul</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Batas SLA</th>
                        <th style="width: 110px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignedTickets as $ticket)
                    <tr>
                        <td>
                            <a href="{{ route('staff.tickets.show', $ticket) }}" class="ticket-num">
                                {{ $ticket->ticket_number }}
                            </a>
                            <div style="font-size: 12px; color: var(--gray-400);">
                                {{ $ticket->created_at->translatedFormat('d M, H:i') }}
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--gray-900);">{{ $ticket->user->nama }}</div>
                            <div style="font-size: 12px; color: var(--gray-500);"><span style="font-variant-numeric: tabular-nums;">{{ $ticket->user->nomor_identitas }}</span></div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--gray-800); font-size: 14px;">{{ $ticket->subject }}</div>
                            <div style="font-size: 12px; color: var(--primary);">{{ $ticket->service->name }}</div>
                        </td>
                        <td style="text-align: center;">
                            @php
                                $badgeClass = match($ticket->status->name) {
                                    'DIAJUKAN' => 'diajukan',
                                    'DIVERIFIKASI' => 'diverifikasi',
                                    'DIPROSES' => 'diproses',
                                    'SELESAI' => 'selesai',
                                    'DITOLAK' => 'ditolak',
                                    default => 'diajukan'
                                };
                            @endphp
                            <span class="badge-status {{ $badgeClass }}">{{ $ticket->status->label }}</span>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge-sla {{ match($ticket->sla_status) { 'OVERDUE' => 'overdue', 'NEAR_DEADLINE' => 'near', 'COMPLETED' => 'completed', default => 'normal' } }}">
                                {{ $ticket->deadline ? $ticket->deadline->translatedFormat('d M, H:i') : '—' }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('staff.tickets.show', $ticket) }}" class="btn btn-sm btn-primary" style="padding: 5px 12px;">
                                Proses <i class="fas fa-arrow-right" style="font-size: 12px;"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 36px; color: var(--gray-400);">
                            <i class="fas fa-check-circle" style="font-size: 32px; color: var(--status-selesai); margin-bottom: 8px; display: block;"></i>
                            Tidak ada tiket yang sedang ditugaskan ke Anda saat ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assignedTickets->hasPages())
        <div style="padding: 14px 20px; border-top: 1px solid var(--gray-200);">
            {{ $assignedTickets->links() }}
        </div>
        @endif
    </div>

    <!-- Section Tiket Masuk Sesuai Spesialisasi Anda (Belum Selesai) -->
    @if($specialtyTickets->count() > 0)
    <div class="card">
        <div class="card-header" style="background: rgba(79, 70, 229, 0.03);">
            <div>
                <h2><i class="fas fa-bullhorn" style="color: var(--primary); margin-right: 8px;"></i> Tiket Masuk di Bidang Spesialisasi Anda</h2>
                <p style="margin: 2px 0 0 0; font-size: 12px; color: var(--gray-500);">
                    Tiket-tiket ini sesuai keahlian Anda dan sedang menunggu penugasan atau proses verifikasi dari manajer.
                </p>
            </div>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No. Tiket</th>
                        <th>Pemohon</th>
                        <th>Layanan</th>
                        <th>Judul Pengaduan</th>
                        <th style="text-align: center;">Status</th>
                        <th style="width: 100px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($specialtyTickets as $stk)
                    <tr>
                        <td><span class="ticket-num">{{ $stk->ticket_number }}</span></td>
                        <td>{{ $stk->user->nama }}</td>
                        <td>
                            <span style="background: var(--primary-50); color: var(--primary); padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 12px;">
                                {{ $stk->service->name }}
                            </span>
                        </td>
                        <td style="font-size: 14px;">{{ $stk->subject }}</td>
                        <td style="text-align: center;">
                            <span class="badge-status {{ $stk->status->name === 'DIAJUKAN' ? 'diajukan' : 'diverifikasi' }}">
                                {{ $stk->status->label }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('staff.tickets.show', $stk) }}" class="btn btn-sm btn-outline">
                                Lihat
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
@endsection
