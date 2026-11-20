@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

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
                Dashboard <span class="highlight-name">Administrator</span>
            </h1>
            <p class="hero-desc">
                Monitoring seluruh tiket, penugasan staf, dan performa layanan akademik universitas.
                Terdapat <b>{{ $stats['unassigned'] }} tiket belum ditugaskan</b> ke staf.
            </p>
            <div class="hero-badges">
                <span class="hero-badge">
                    <i class="fas fa-graduation-cap"></i> Layanan Terpadu Sivitas Akademika
                </span>
                <span class="hero-badge">
                    <i class="fas fa-shield-alt"></i> Administrator
                </span>
                <span class="hero-badge">
                    <i class="fas fa-inbox"></i> {{ $stats['total'] }} Total Tiket
                </span>
            </div>
        </div>

        <div class="hero-action">
            <a href="{{ route('admin.tickets.index') }}" class="btn btn-cta">
                <i class="fas fa-inbox"></i> Kelola Tiket
            </a>
        </div>
    </section>

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card animate-in" style="--stat-color: var(--primary);">
            <div class="stat-icon" style="background: var(--primary-soft); color: var(--primary);">
                <i class="fas fa-inbox"></i>
            </div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Tiket</div>
        </div>

        <div class="stat-card animate-in" style="--stat-color: var(--status-diajukan-dot);">
            <div class="stat-icon" style="background: var(--status-diajukan-bg); color: var(--status-diajukan-fg);">
                <i class="fas fa-plus-circle"></i>
            </div>
            <div class="stat-value">{{ $stats['baru'] }}</div>
            <div class="stat-label">Tiket Baru</div>
        </div>

        <div class="stat-card animate-in" style="--stat-color: var(--status-diverifikasi-dot);">
            <div class="stat-icon" style="background: var(--status-diverifikasi-bg); color: var(--status-diverifikasi-fg);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-value">{{ $stats['diverifikasi'] }}</div>
            <div class="stat-label">Diverifikasi</div>
        </div>

        <div class="stat-card animate-in" style="--stat-color: var(--status-diproses-dot);">
            <div class="stat-icon" style="background: var(--status-diproses-bg); color: var(--status-diproses-fg);">
                <i class="fas fa-sync-alt"></i>
            </div>
            <div class="stat-value">{{ $stats['diproses'] }}</div>
            <div class="stat-label">Diproses</div>
        </div>

        <div class="stat-card animate-in" style="--stat-color: var(--status-selesai-dot);">
            <div class="stat-icon" style="background: var(--status-selesai-bg); color: var(--status-selesai-fg);">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="stat-value">{{ $stats['selesai'] }}</div>
            <div class="stat-label">Selesai</div>
        </div>

        <div class="stat-card animate-in" style="--stat-color: var(--status-ditolak-dot);">
            <div class="stat-icon" style="background: var(--status-ditolak-bg); color: var(--status-ditolak-fg);">
                <i class="fas fa-ban"></i>
            </div>
            <div class="stat-value">{{ $stats['ditolak'] }}</div>
            <div class="stat-label">Ditolak</div>
        </div>

        <div class="stat-card animate-in" style="--stat-color: var(--sla-overdue);">
            <div class="stat-icon" style="background: var(--status-ditolak-bg); color: var(--sla-overdue);">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-value">{{ $stats['overdue'] }}</div>
            <div class="stat-label">Overdue</div>
        </div>

        <div class="stat-card animate-in" style="--stat-color: var(--sla-near);">
            <div class="stat-icon" style="background: var(--status-diajukan-bg); color: var(--sla-near);">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-value">{{ $stats['near_deadline'] }}</div>
            <div class="stat-label">Mendekati Deadline</div>
        </div>

        <div class="stat-card animate-in" style="--stat-color: var(--accent); cursor: pointer;" onclick="window.location='{{ route('admin.tickets.index', ['assignment' => 'unassigned']) }}'">
            <div class="stat-icon" style="background: var(--accent-soft); color: var(--accent-ink);">
                <i class="fas fa-user-clock"></i>
            </div>
            <div class="stat-value">{{ $stats['unassigned'] }}</div>
            <div class="stat-label">Belum Ditugaskan</div>
        </div>

        <div class="stat-card animate-in" style="--stat-color: var(--primary); cursor: pointer;" onclick="window.location='{{ route('admin.alumni.index') }}'">
            <div class="stat-icon" style="background: var(--primary-soft); color: var(--primary);">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-value">{{ $stats['pending_alumni'] ?? 0 }}</div>
            <div class="stat-label">Verifikasi Alumni Pending</div>
        </div>
    </div>

    {{-- Recent Tickets --}}
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-clock" style="margin-right: 8px; color: var(--primary);"></i> Tiket Terbaru</h2>
            <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm btn-secondary">
                Lihat Semua <i class="fas fa-arrow-right" style="margin-left: 4px;"></i>
            </a>
        </div>

        @if($recentTickets->count() > 0)
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No. Tiket</th>
                            <th>Pengirim</th>
                            <th>Tipe</th>
                            <th>Layanan</th>
                            <th>Status</th>
                            <th>SLA</th>
                            <th>Staff</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTickets as $ticket)
                        <tr>
                            <td>
                                <a href="{{ route('admin.tickets.show', $ticket) }}" style="text-decoration: none;">
                                    <span class="ticket-num">{{ $ticket->ticket_number }}</span>
                                </a>
                            </td>
                            <td>{{ $ticket->user->nama }}</td>
                            <td>
                                <span style="font-size: 12px; background: var(--sunken); padding: 3px 8px; border-radius: var(--radius-full); font-weight: 600; color: var(--muted);">
                                    {{ $ticket->user->userType->label }}
                                </span>
                            </td>
                            <td>{{ Str::limit($ticket->service->name, 30) }}</td>
                            <td>
                                <span class="badge-status {{ strtolower($ticket->status->name) }}">
                                    {{ $ticket->status->label }}
                                </span>
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
                            <td style="font-size: 13px; color: var(--muted);">
                                @php
                                    $assignedNames = $ticket->assignments->unique('staff_id')->map(fn($a) => $a->staff?->nama)->filter()->values();
                                @endphp
                                @if($assignedNames->count() > 0)
                                    <div style="display: flex; flex-direction: column; gap: 2px;">
                                        @foreach($assignedNames as $sName)
                                            <span style="font-weight: 600; color: var(--ink); font-size: 12.5px;">{{ $sName }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-sm btn-secondary">
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
                <i class="fas fa-inbox"></i>
                <h3>Belum ada tiket</h3>
                <p>Tiket yang masuk akan ditampilkan di sini.</p>
            </div>
        @endif
    </div>
@endsection
