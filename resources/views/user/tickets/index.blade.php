@extends('layouts.app')

@section('title', 'Tiket Pengaduan Saya')
@section('page-title', 'Tiket Saya')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 22px; font-weight: 700; color: var(--gray-900); margin-bottom: 4px;">
                <i class="fas fa-ticket-alt" style="color: var(--primary); margin-right: 8px;"></i>
                Riwayat Pengaduan & Layanan Anda
            </h2>
            <p style="color: var(--gray-500); font-size: 14px; margin: 0;">
                Pantau proses verifikasi, penanganan oleh staf, dan hasil penyelesaian tiket Anda.
            </p>
        </div>
        <a href="{{ route('user.tickets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Pengaduan Baru
        </a>
    </div>

    <!-- Filter Status -->
    <div class="card" style="margin-bottom: 24px; padding: 14px 20px;">
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <span style="font-size: 14px; font-weight: 600; color: var(--gray-700);">Filter Status:</span>
            <a href="{{ route('user.tickets.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline' }}">
                Semua
            </a>
            @foreach($statuses as $st)
                <a href="{{ route('user.tickets.index', ['status' => $st->name]) }}" 
                   class="btn btn-sm {{ request('status') == $st->name ? 'btn-primary' : 'btn-outline' }}">
                    {{ $st->label }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Tabel Tiket -->
    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 170px;">No. Tiket</th>
                        <th>Layanan Akademik</th>
                        <th>Judul Pengaduan</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Perkiraan Selesai (SLA)</th>
                        <th style="width: 110px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td>
                            <a href="{{ route('user.tickets.show', $ticket) }}" class="ticket-num">
                                {{ $ticket->ticket_number }}
                            </a>
                            <div style="font-size: 12px; color: var(--gray-400);">
                                {{ $ticket->created_at->translatedFormat('d M Y, H:i') }}
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--gray-900);">{{ $ticket->service->name }}</div>
                            <div style="font-size: 12px; color: var(--gray-500);">{{ $ticket->category->name }}</div>
                        </td>
                        <td style="font-size: 14px; color: var(--gray-800);">
                            {{ $ticket->subject }}
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
                            <span class="badge-status {{ $badgeClass }}">
                                {{ $ticket->status->label }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            @if($ticket->status->name === 'SELESAI')
                                <span class="badge-status selesai"><i class="fas fa-check"></i> Selesai</span>
                            @elseif($ticket->status->name === 'DITOLAK')
                                <span class="badge-status ditolak">Ditolak</span>
                            @else
                                <span style="font-size: 12px; font-weight: 600; color: var(--gray-700);">
                                    {{ $ticket->deadline ? $ticket->deadline->translatedFormat('d M Y, H:i') : '—' }}
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('user.tickets.show', $ticket) }}" class="btn btn-sm btn-outline">
                                Detail <i class="fas fa-arrow-right" style="font-size: 12px;"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--gray-400);">
                            <i class="fas fa-inbox" style="font-size: 36px; margin-bottom: 12px; display: block; opacity: 0.4;"></i>
                            Anda belum pernah mengajukan tiket pengaduan.
                            <div style="margin-top: 14px;">
                                <a href="{{ route('user.tickets.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Buat Pengaduan Sekarang
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
        <div style="padding: 14px 20px; border-top: 1px solid var(--gray-200);">
            {{ $tickets->links() }}
        </div>
        @endif
    </div>
@endsection
