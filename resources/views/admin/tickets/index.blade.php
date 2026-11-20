@extends('layouts.app')

@section('title', 'Manajemen Tiket Layanan')
@section('page-title', 'Semua Tiket Pengaduan & Layanan')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 22px; font-weight: 700; color: var(--gray-900); margin-bottom: 4px;">
                <i class="fas fa-inbox" style="color: var(--primary); margin-right: 8px;"></i>
                Manajemen Tiket Pengaduan & Layanan
            </h2>
            <p style="color: var(--gray-500); font-size: 14px; margin: 0;">
                Pantau seluruh tiket, verifikasi berkas pengajuan, dan tugaskan pengerjaan tiket ke staf yang berkompeten.
            </p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom: 24px; padding: 16px 20px;">
        <form method="GET" action="{{ route('admin.tickets.index') }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 220px; position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--gray-400);"></i>
                <input type="text" name="search" class="form-control" style="padding-left: 36px;" 
                    placeholder="Cari nomor tiket, judul, pemohon..." value="{{ request('search') }}">
            </div>

            <div style="min-width: 150px;">
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Semua Status --</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st->name }}" {{ request('status') == $st->name ? 'selected' : '' }}>
                            {{ $st->label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="min-width: 170px;">
                <select name="category_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="min-width: 160px;">
                <select name="assignment" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Status Penugasan --</option>
                    <option value="unassigned" {{ request('assignment') === 'unassigned' ? 'selected' : '' }}>Belum Ditugaskan</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="padding: 9px 18px;">
                <i class="fas fa-filter"></i> Filter
            </button>

            @if(request()->hasAny(['search', 'status', 'category_id', 'assignment']))
                <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline" style="padding: 9px 18px;">
                    <i class="fas fa-times"></i> Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Tabel Tiket -->
    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 160px;">No. Tiket</th>
                        <th>Pemohon</th>
                        <th>Layanan & Judul</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Batas Waktu (SLA)</th>
                        <th>Staf Pelaksana</th>
                        <th style="width: 100px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td>
                            <a href="{{ route('admin.tickets.show', $ticket) }}" class="ticket-num">
                                {{ $ticket->ticket_number }}
                            </a>
                            <div style="font-size: 12px; color: var(--gray-400);">
                                {{ $ticket->created_at->translatedFormat('d M Y, H:i') }}
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--gray-900);">{{ $ticket->user->nama }}</div>
                            <div style="font-size: 12px; color: var(--gray-500);">
                                <span class="badge" style="background: var(--gray-100); color: var(--gray-700); padding: 1px 6px; font-size: 12px;">
                                    {{ $ticket->user->userType->label }}
                                </span>
                                <span style="font-variant-numeric: tabular-nums;">{{ $ticket->user->nomor_identitas }}</span>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--gray-800); font-size: 14px;">{{ $ticket->subject }}</div>
                            <div style="font-size: 12px; color: var(--primary); font-weight: 500;">
                                <i class="fas fa-tag" style="font-size: 12px;"></i> {{ $ticket->service->name }}
                            </div>
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
                            @php
                                $slaClass = match($ticket->sla_status) {
                                    'OVERDUE' => 'overdue',
                                    'NEAR_DEADLINE' => 'near',
                                    'COMPLETED' => 'completed',
                                    default => 'normal'
                                };
                            @endphp
                            <span class="badge-sla {{ $slaClass }}">
                                @if($ticket->sla_status === 'OVERDUE')
                                    <i class="fas fa-exclamation-triangle"></i> Terlambat
                                @elseif($ticket->sla_status === 'NEAR_DEADLINE')
                                    <i class="fas fa-clock"></i> < 24 Jam
                                @elseif($ticket->sla_status === 'COMPLETED')
                                    <i class="fas fa-check"></i> Selesai
                                @else
                                    <i class="fas fa-calendar-check"></i> Tepat Waktu
                                @endif
                            </span>
                            <div style="font-size: 12px; color: var(--gray-500); margin-top: 2px;">
                                {{ $ticket->deadline ? $ticket->deadline->translatedFormat('d M, H:i') : '—' }}
                            </div>
                        </td>
                        <td>
                            @php
                                $assignedStaffList = $ticket->assignments->unique('staff_id')->values();
                            @endphp
                            @if($assignedStaffList->count() > 0)
                                <div style="display: flex; flex-direction: column; gap: 5px;">
                                    @foreach($assignedStaffList as $asg)
                                        @if($asg->staff)
                                            <div style="display: flex; align-items: center; gap: 7px;">
                                                <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--primary-100); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0;">
                                                    {{ strtoupper(substr($asg->staff->nama, 0, 2)) }}
                                                </div>
                                                <div style="font-weight: 600; font-size: 13px; color: var(--gray-800); line-height: 1.2;">
                                                    {{ $asg->staff->nama }}
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <span style="font-size: 12px; color: #f59e0b; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="fas fa-user-clock"></i> Belum ditugaskan
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-sm btn-primary" style="padding: 5px 12px; font-size: 12px;">
                                Detail <i class="fas fa-chevron-right" style="font-size: 12px; margin-left: 2px;"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--gray-400);">
                            <i class="fas fa-ticket-alt" style="font-size: 36px; margin-bottom: 12px; display: block; opacity: 0.4;"></i>
                            Tidak ada tiket yang sesuai dengan kriteria filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--gray-200);">
            {{ $tickets->links() }}
        </div>
        @endif
    </div>
@endsection
