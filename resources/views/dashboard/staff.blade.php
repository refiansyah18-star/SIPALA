@extends('layouts.app')

@section('title', 'Staff Dashboard')
@section('page-title', 'Staff Dashboard')

@section('content')
    {{-- Dashboard Greeting Hero Banner --}}
    <section class="dashboard-hero animate-in">
        <div class="hero-stars" aria-hidden="true">
            <i class="fas fa-star"></i>
            <i class="fas fa-star" style="margin-top: 10px; font-size: 11px;"></i>
            <i class="fas fa-star" style="font-size: 18px;"></i>
        </div>
        <div class="hero-watermark" aria-hidden="true">
            <x-logo cutColor="#0A5E3D" />
        </div>

        <div class="hero-content">
            <div class="hero-date">
                <i class="fas fa-calendar-alt"></i>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
            <h1 class="hero-title">
                Halo, <span class="highlight-name">{{ $user->nama }}</span>!
            </h1>
            <p class="hero-desc">
                Anda memiliki <b>{{ $stats['belum_diproses'] }} tiket belum diproses</b> dan <b>{{ $stats['diproses'] }} sedang diproses</b>.
            </p>
            <div class="hero-badges">
                <span class="hero-badge">
                    <i class="fas fa-graduation-cap"></i> Layanan Terpadu Sivitas Akademika
                </span>
                <span class="hero-badge">
                    <i class="fas fa-user-shield"></i> Staf Penanganan
                </span>
                @if($user->nomor_identitas)
                    <span class="hero-badge">
                        <i class="fas fa-id-card"></i> {{ $user->nomor_identitas }}
                    </span>
                @endif
            </div>
        </div>

        <div class="hero-action">
            <a href="{{ route('staff.tickets.index') }}" class="btn btn-cta">
                <i class="fas fa-tasks"></i> Daftar Tugas
            </a>
        </div>
    </section>

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card animate-in" style="--stat-color: var(--primary);">
            <div class="stat-icon" style="background: var(--primary-soft); color: var(--primary);">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Tiket Saya</div>
        </div>

        <div class="stat-card animate-in" style="--stat-color: var(--status-diajukan-dot);">
            <div class="stat-icon" style="background: var(--status-diajukan-bg); color: var(--status-diajukan-fg);">
                <i class="fas fa-folder-open"></i>
            </div>
            <div class="stat-value">{{ $stats['belum_diproses'] }}</div>
            <div class="stat-label">Belum Diproses</div>
        </div>

        <div class="stat-card animate-in" style="--stat-color: var(--status-diproses-dot);">
            <div class="stat-icon" style="background: var(--status-diproses-bg); color: var(--status-diproses-fg);">
                <i class="fas fa-sync-alt"></i>
            </div>
            <div class="stat-value">{{ $stats['diproses'] }}</div>
            <div class="stat-label">Diproses</div>
        </div>

        <div class="stat-card animate-in" style="--stat-color: var(--sla-near);">
            <div class="stat-icon" style="background: var(--status-diajukan-bg); color: var(--sla-near);">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-value">{{ $stats['near_deadline'] }}</div>
            <div class="stat-label">Mendekati Deadline</div>
        </div>

        <div class="stat-card animate-in" style="--stat-color: var(--status-selesai-dot);">
            <div class="stat-icon" style="background: var(--status-selesai-bg); color: var(--status-selesai-fg);">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="stat-value">{{ $stats['selesai'] }}</div>
            <div class="stat-label">Selesai</div>
        </div>
    </div>

    {{-- Assigned Tickets --}}
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-tasks" style="margin-right: 8px; color: var(--primary);"></i> Tiket Tugas Staf</h2>
            @if($assignedTickets->count() > 0)
                <a href="{{ route('staff.tickets.index') }}" class="btn btn-sm btn-secondary">
                    Lihat Semua
                </a>
            @endif
        </div>

        @if($assignedTickets->count() > 0)
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No. Tiket</th>
                            <th>Pengirim</th>
                            <th>Layanan</th>
                            <th>Status</th>
                            <th>Deadline</th>
                            <th>SLA</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignedTickets as $ticket)
                        <tr>
                            <td>
                                <span class="ticket-num">{{ $ticket->ticket_number }}</span>
                            </td>
                            <td>{{ $ticket->user->nama }}</td>
                            <td>{{ Str::limit($ticket->service->name, 30) }}</td>
                            <td>
                                <span class="badge-status {{ strtolower($ticket->status->name) }}">
                                    {{ $ticket->status->label }}
                                </span>
                            </td>
                            <td style="font-size: 13px; color: var(--muted);">
                                {{ $ticket->deadline->format('d M Y') }}
                            </td>
                            <td>
                                <span class="badge-sla {{ strtolower($ticket->sla_status) }}">
                                    @if($ticket->sla_status === 'OVERDUE')
                                        <i class="fas fa-exclamation-triangle"></i> Overdue
                                    @elseif($ticket->sla_status === 'NEAR_DEADLINE')
                                        <i class="fas fa-hourglass-half"></i> Segera
                                    @else
                                        <i class="fas fa-check"></i> Normal
                                    @endif
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('staff.tickets.show', $ticket) }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-tasks"></i>
                <h3>Belum ada tiket tugas</h3>
                <p>Tiket yang ditugaskan kepada Anda akan ditampilkan di sini.</p>
            </div>
        @endif
    </div>
@endsection
