@extends('layouts.app')

@section('title', 'Sistem & Maintenance (Pustikom)')
@section('page-title', 'Pusat Kontrol Sistem & Maintenance')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 22px; font-weight: 700; color: var(--gray-900); margin-bottom: 4px;">
                <i class="fas fa-server" style="color: var(--primary); margin-right: 8px;"></i>
                Status & Pemeliharaan Sistem (Pustikom)
            </h2>
            <p style="color: var(--gray-500); font-size: 14px; margin: 0;">
                Panel khusus Super Admin untuk memantau performa, membersihkan cache, dan mengelola mode pemeliharaan (maintenance mode).
            </p>
        </div>
    </div>

    <!-- Status Mode Maintenance Banner -->
    <div class="card" style="margin-bottom: 24px; border-left: 5px solid {{ $systemInfo['is_maintenance'] ? 'var(--status-ditolak)' : 'var(--status-selesai)' }};">
        <div style="padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: {{ $systemInfo['is_maintenance'] ? 'rgba(239,68,68,0.1)' : 'rgba(16,185,129,0.1)' }}; display: flex; align-items: center; justify-content: center; font-size: 22px; color: {{ $systemInfo['is_maintenance'] ? 'var(--status-ditolak)' : 'var(--status-selesai)' }};">
                    <i class="fas {{ $systemInfo['is_maintenance'] ? 'fa-tools' : 'fa-check-circle' }}"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--gray-900);">
                        Mode Sistem: {{ $systemInfo['is_maintenance'] ? 'MAINTENANCE MODE (AKTIF)' : 'OPERASIONAL NORMAL' }}
                    </h3>
                    <p style="margin: 4px 0 0 0; font-size: 14px; color: var(--gray-600);">
                        {{ $systemInfo['is_maintenance'] 
                            ? 'Sistem sedang dalam perbaikan oleh Pustikom. Pengguna biasa tidak dapat login/mengakses.' 
                            : 'Seluruh pengguna (Mahasiswa, Dosen, Staf, Admin) dapat mengakses sistem secara normal.' }}
                    </p>
                </div>
            </div>

            <form action="{{ route('admin.maintenance.toggle') }}" method="POST"
                  onsubmit="return confirm('{{ $systemInfo['is_maintenance'] ? 'Aktifkan sistem kembali untuk semua pengguna?' : 'PERINGATAN: Mengaktifkan maintenance mode akan membatasi akses pengguna biasa. Lanjutkan?' }}');">
                @csrf
                <button type="submit" class="btn {{ $systemInfo['is_maintenance'] ? 'btn-primary' : 'btn-outline' }}" 
                        style="{{ $systemInfo['is_maintenance'] ? 'background: var(--status-selesai); border-color: var(--status-selesai);' : 'color: var(--status-ditolak); border-color: var(--status-ditolak);' }} padding: 10px 20px; font-weight: 600;">
                    <i class="fas {{ $systemInfo['is_maintenance'] ? 'fa-play' : 'fa-power-off' }}"></i>
                    {{ $systemInfo['is_maintenance'] ? 'Nonaktifkan Maintenance Mode' : 'Aktifkan Maintenance Mode' }}
                </button>
            </form>
        </div>
    </div>

    <!-- System Metrics Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="card" style="padding: 16px 20px;">
            <div style="font-size: 12px; color: var(--gray-500); font-weight: 600; margin-bottom: 6px;">PHP Version</div>
            <div style="font-size: 22px; font-weight: 700; color: var(--gray-900); display: flex; align-items: center; gap: 8px;">
                <i class="fab fa-php" style="color: #777bb4;"></i>
                {{ $systemInfo['php_version'] }}
            </div>
        </div>

        <div class="card" style="padding: 16px 20px;">
            <div style="font-size: 12px; color: var(--gray-500); font-weight: 600; margin-bottom: 6px;">Laravel Framework</div>
            <div style="font-size: 22px; font-weight: 700; color: var(--gray-900); display: flex; align-items: center; gap: 8px;">
                <i class="fab fa-laravel" style="color: #ff2d20;"></i>
                v{{ $systemInfo['laravel_version'] }}
            </div>
        </div>

        <div class="card" style="padding: 16px 20px;">
            <div style="font-size: 12px; color: var(--gray-500); font-weight: 600; margin-bottom: 6px;">Status Database</div>
            <div style="font-size: 18px; font-weight: 700; color: var(--status-selesai); display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-database"></i>
                {{ $dbStatus }}
            </div>
        </div>

        <div class="card" style="padding: 16px 20px;">
            <div style="font-size: 12px; color: var(--gray-500); font-weight: 600; margin-bottom: 6px;">Environment</div>
            <div style="font-size: 18px; font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-code-branch"></i>
                {{ strtoupper($systemInfo['app_env']) }}
            </div>
        </div>
    </div>

    <!-- Quick Tools Card -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h2><i class="fas fa-wrench" style="color: var(--primary); margin-right: 8px;"></i> Tindakan Pemeliharaan (Maintenance Tools)</h2>
        </div>
        <div class="card-body" style="display: flex; gap: 16px; flex-wrap: wrap;">
            <form action="{{ route('admin.maintenance.cache') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary" onclick="return confirm('Bersihkan seluruh cache aplikasi, route, dan view?')">
                    <i class="fas fa-broom"></i> Bersihkan Cache Sistem (Cache Clear)
                </button>
            </form>

            <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline">
                <i class="fas fa-history"></i> Lihat Audit Trail / Activity Log
            </a>

            <a href="{{ route('admin.admins.index') }}" class="btn btn-outline">
                <i class="fas fa-user-shield"></i> Kelola Akun Admin (Manajer)
            </a>
        </div>
    </div>

    <!-- Recent Activity Log Preview -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2><i class="fas fa-list-alt" style="color: var(--primary); margin-right: 8px;"></i> Log Aktivitas Sistem Terkini</h2>
            <a href="{{ route('admin.activity-logs.index') }}" style="font-size: 14px; color: var(--primary); font-weight: 600;">
                Lihat Semua Log <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 170px;">Waktu</th>
                        <th>Pengguna</th>
                        <th style="text-align: center;">Aksi</th>
                        <th>Modul</th>
                        <th>Keterangan</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs as $log)
                    <tr>
                        <td style="font-size: 12px; color: var(--gray-500);">
                            {{ $log->created_at->translatedFormat('d M Y, H:i') }}
                        </td>
                        <td>
                            @if($log->user)
                                <span style="font-weight: 600; color: var(--gray-900);">{{ $log->user->nama }}</span>
                                <span style="font-size: 12px; color: var(--gray-500); display: block;">{{ $log->user->userType->label }}</span>
                            @else
                                <span style="color: var(--gray-400);">Sistem</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <span style="font-size: 12px; font-weight: 700; padding: 2px 8px; border-radius: 4px; background: var(--gray-100); color: var(--gray-800);">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td style="font-weight: 500; font-size: 14px;">{{ $log->module }}</td>
                        <td style="font-size: 14px; color: var(--gray-700);">{{ $log->description ?: '—' }}</td>
                        <td style="font-variant-numeric: tabular-nums; font-size: 12px; color: var(--gray-500);">{{ $log->ip_address }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: var(--gray-400);">
                            Belum ada catatan aktivitas sistem.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
