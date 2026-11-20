@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    {{-- Dashboard Greeting Hero Banner (Inspired by Reference) --}}
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
                Selamat datang, <span class="highlight-name">{{ $user->nama }}</span>!
            </h1>
            <p class="hero-desc">
                @if($stats['total'] === 0)
                    Belum ada pengaduan. Laporkan kendala layanan akademik atau administrasi Anda melalui SIPALA.
                @else
                    Anda memiliki <b>{{ $stats['diajukan'] }} pengaduan diajukan</b> dan <b>{{ $stats['diproses'] }} sedang diproses</b> oleh staf.
                @endif
            </p>
            <div class="hero-badges">
                <span class="hero-badge">
                    <i class="fas fa-graduation-cap"></i> Layanan Terpadu Sivitas Akademika
                </span>
                <span class="hero-badge">
                    <i class="fas fa-user-tag"></i> {{ $user->userType->label }}
                </span>
                @if($user->nomor_identitas)
                    <span class="hero-badge">
                        <i class="fas fa-id-card"></i> {{ $user->nomor_identitas }}
                    </span>
                @endif
                @if($user->programStudy)
                    <span class="hero-badge">
                        <i class="fas fa-university"></i> {{ $user->programStudy->name }}
                    </span>
                @endif
            </div>
        </div>

        <div class="hero-action" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
            @if($user->isAlumni())
                <a href="{{ route('user.alumni-profile.edit') }}" class="btn" style="background: rgba(255,255,255,0.14); color: #fff; border: 1px solid rgba(255,255,255,0.28);">
                    <i class="fas fa-user-cog"></i> Profil &amp; Password
                </a>
            @endif
            <a href="{{ route('user.tickets.create') }}" class="btn btn-cta">
                <i class="fas fa-plus"></i> Buat Pengaduan
            </a>
        </div>
    </section>

    {{-- Stats Cards --}}
    <div class="stats-grid">
        <div class="stat-card animate-in" style="--stat-color: var(--primary);">
            <div class="stat-icon" style="background: var(--primary-soft); color: var(--primary);">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Pengaduan</div>
        </div>

        <div class="stat-card animate-in" style="--stat-color: var(--status-diajukan-dot);">
            <div class="stat-icon" style="background: var(--status-diajukan-bg); color: var(--status-diajukan-fg);">
                <i class="fas fa-hourglass-start"></i>
            </div>
            <div class="stat-value">{{ $stats['diajukan'] }}</div>
            <div class="stat-label">Diajukan</div>
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
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-value">{{ $stats['selesai'] }}</div>
            <div class="stat-label">Selesai</div>
        </div>

        <div class="stat-card animate-in" style="--stat-color: var(--status-ditolak-dot);">
            <div class="stat-icon" style="background: var(--status-ditolak-bg); color: var(--status-ditolak-fg);">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-value">{{ $stats['ditolak'] }}</div>
            <div class="stat-label">Ditolak</div>
        </div>
    </div>

    {{-- Ticket List --}}
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-list" style="margin-right: 8px; color: var(--primary);"></i> Daftar Pengaduan</h2>
            @if($tickets->count() > 0)
                <a href="{{ route('user.tickets.index') }}" class="btn btn-sm btn-secondary">
                    Lihat Semua
                </a>
            @endif
        </div>

        @if($tickets->count() > 0)
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No. Tiket</th>
                            <th>Layanan</th>
                            <th>Status</th>
                            <th>Deadline</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                        <tr>
                            <td>
                                <span class="ticket-num">{{ $ticket->ticket_number }}</span>
                            </td>
                            <td>{{ $ticket->service->name }}</td>
                            <td>
                                <span class="badge-status {{ strtolower($ticket->status->name) }}">
                                    {{ $ticket->status->label }}
                                </span>
                            </td>
                            <td>
                                <span class="badge-sla {{ strtolower($ticket->sla_status) }}">
                                    @if($ticket->sla_status === 'OVERDUE')
                                        <i class="fas fa-exclamation-triangle"></i>
                                    @endif
                                    {{ $ticket->deadline->format('d M Y') }}
                                </span>
                            </td>
                            <td style="color: var(--muted); font-size: 13px;">
                                {{ $ticket->created_at->format('d M Y H:i') }}
                            </td>
                            <td>
                                <a href="{{ route('user.tickets.show', $ticket) }}" class="btn btn-sm btn-secondary">
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
                <h3>Belum ada pengaduan</h3>
                <p>Buat pengaduan pertama Anda untuk memulai.</p>
            </div>
        @endif
    </div>
@endsection
