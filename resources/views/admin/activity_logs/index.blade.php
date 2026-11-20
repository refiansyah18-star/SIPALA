@extends('layouts.app')

@section('title', 'Audit Trail / Log Aktivitas')
@section('page-title', 'Log Aktivitas Sistem (Audit Trail)')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 22px; font-weight: 700; color: var(--gray-900); margin-bottom: 4px;">
                <i class="fas fa-history" style="color: var(--primary); margin-right: 8px;"></i>
                Riwayat Aktivitas & Log Sistem
            </h2>
            <p style="color: var(--gray-500); font-size: 14px; margin: 0;">
                Catatan riwayat seluruh aktivitas penting yang dilakukan pengguna dan sistem dalam SIPALA.
            </p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom: 24px; padding: 16px 20px;">
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 220px;">
                <input type="text" name="search" class="form-control" placeholder="Cari aksi, pengguna, atau IP..." value="{{ request('search') }}">
            </div>
            <div style="min-width: 160px;">
                <select name="action" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Semua Aksi --</option>
                    @foreach(['LOGIN', 'CREATE', 'UPDATE', 'DELETE', 'VERIFY', 'REJECT', 'ASSIGN', 'COMPLETE'] as $act)
                        <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width: 160px;">
                <select name="module" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Semua Modul --</option>
                    @foreach(['AUTH', 'TICKET', 'SERVICE', 'CATEGORY', 'STAFF', 'USER', 'ALUMNI', 'SYSTEM'] as $mod)
                        <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 9px 18px;">
                <i class="fas fa-filter"></i> Filter
            </button>
            @if(request()->hasAny(['search', 'action', 'module']))
                <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline" style="padding: 9px 18px;">
                    <i class="fas fa-times"></i> Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 160px;">Waktu</th>
                        <th>Pengguna</th>
                        <th style="text-align: center;">Aksi</th>
                        <th>Modul</th>
                        <th>Keterangan / Detail</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td style="font-size: 12px; color: var(--gray-500); white-space: nowrap;">
                            {{ $log->created_at ? $log->created_at->translatedFormat('d M Y, H:i:s') : '—' }}
                        </td>
                        <td>
                            @if($log->user)
                                <div style="font-weight: 600; color: var(--gray-900);">{{ $log->user->nama }}</div>
                                <div style="font-size: 12px; color: var(--gray-500);">{{ $log->user->userType->label ?? $log->user->email }}</div>
                            @else
                                <span style="color: var(--gray-400); font-style: italic;">Sistem</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @php
                                $badgeColor = match($log->action) {
                                    'LOGIN' => 'var(--primary)',
                                    'CREATE' => 'var(--status-selesai)',
                                    'UPDATE' => 'var(--status-diproses)',
                                    'DELETE', 'REJECT' => 'var(--status-ditolak)',
                                    'VERIFY', 'ASSIGN' => 'var(--accent)',
                                    default => 'var(--gray-600)'
                                };
                            @endphp
                            <span style="font-size: 12px; font-weight: 700; padding: 3px 8px; border-radius: 4px; background: rgba(0,0,0,0.05); color: {{ $badgeColor }}; border: 1px solid rgba(0,0,0,0.08);">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td>
                            <span style="font-size: 12px; font-weight: 600; color: var(--gray-700);">{{ $log->module }}</span>
                        </td>
                        <td style="font-size: 14px; color: var(--gray-800);">
                            {{ $log->description ?: '—' }}
                        </td>
                        <td style="font-variant-numeric: tabular-nums; font-size: 12px; color: var(--gray-500);">
                            {{ $log->ip_address ?: '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--gray-400);">
                            <i class="fas fa-history" style="font-size: 32px; margin-bottom: 12px; display: block; opacity: 0.4;"></i>
                            Tidak ada data log yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--gray-200);">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
@endsection
