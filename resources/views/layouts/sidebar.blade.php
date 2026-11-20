{{-- Sidebar Navigation --}}
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="sidebar-brand-wrap">
            <div class="sidebar-brand-logo">
                <x-logo width="28" height="28" cutColor="#071A0E" />
            </div>
            <div class="sidebar-brand-text">
                <h1>SIPALA</h1>
                <p>Layanan Akademik</p>
            </div>
        </a>
    </div>

    <nav class="sidebar-nav">
        {{-- Dashboard --}}
        <div class="nav-section">
            <div class="nav-section-title">Menu Utama</div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
        </div>

        {{-- User Menu --}}
        @if(auth()->user()->isEndUser())
        <div class="nav-section">
            <div class="nav-section-title">Pengaduan</div>
            <a href="{{ route('user.tickets.create') }}" class="nav-link {{ request()->routeIs('user.tickets.create') ? 'active' : '' }}">
                <i class="fas fa-plus-circle"></i>
                Buat Pengaduan
            </a>
            <a href="{{ route('user.tickets.index') }}" class="nav-link {{ request()->routeIs('user.tickets.index') || request()->routeIs('user.tickets.show') ? 'active' : '' }}">
                <i class="fas fa-ticket-alt"></i>
                Tiket Saya
            </a>
            <a href="{{ route('user.ratings.index') }}" class="nav-link {{ request()->routeIs('user.ratings.*') ? 'active' : '' }}">
                <i class="fas fa-star"></i>
                Penilaian Layanan
                @php
                    $sidebarUnratedCount = \App\Models\Ticket::byUser(auth()->id())
                        ->byStatus(\App\Models\Status::SELESAI)
                        ->doesntHave('rating')
                        ->count();
                @endphp
                @if($sidebarUnratedCount > 0)
                    <span style="background: #BB8F32; color: #fff; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 9999px; margin-left: auto;">{{ $sidebarUnratedCount }}</span>
                @endif
            </a>
            @if(auth()->user()->isAlumni())
            <a href="{{ route('user.alumni-profile.edit') }}" class="nav-link {{ request()->routeIs('user.alumni-profile.*') ? 'active' : '' }}">
                <i class="fas fa-user-cog"></i>
                Profil &amp; Password
            </a>
            @endif
        </div>
        @endif

        {{-- Staff Menu (Hanya untuk Staf) --}}
        @if(auth()->user()->isStaff())
        <div class="nav-section">
            <div class="nav-section-title">Penugasan</div>
            <a href="{{ route('staff.tickets.index') }}" class="nav-link {{ request()->routeIs('staff.tickets.*') ? 'active' : '' }}">
                <i class="fas fa-tasks"></i>
                Tiket Tugas Staf
            </a>
        </div>
        @endif

        {{-- Admin & Super Admin Menu --}}
        @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
        <div class="nav-section">
            <div class="nav-section-title">Manajemen Tiket</div>
            <a href="{{ route('admin.tickets.index') }}" class="nav-link {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
                <i class="fas fa-inbox"></i>
                Semua Tiket
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Master Data</div>
            <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i>
                Kategori
            </a>
            <a href="{{ route('admin.services.index') }}" class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                <i class="fas fa-cogs"></i>
                Layanan
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Pengguna</div>
            <a href="{{ route('admin.staff.index') }}" class="nav-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                <i class="fas fa-user-tie"></i>
                Staf & Spesialisasi
            </a>
            <a href="{{ route('admin.alumni.index') }}" class="nav-link {{ request()->routeIs('admin.alumni.*') ? 'active' : '' }}">
                <i class="fas fa-user-graduate"></i>
                Verifikasi Alumni
                @php
                    $sidebarPendingAlumni = \App\Models\AlumniRegistration::pending()->count();
                @endphp
                @if($sidebarPendingAlumni > 0)
                    <span style="background: #BB8F32; color: #fff; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 9999px; margin-left: auto;">{{ $sidebarPendingAlumni }}</span>
                @endif
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Monitoring</div>
            <a href="{{ route('admin.ratings.index') }}" class="nav-link {{ request()->routeIs('admin.ratings.*') ? 'active' : '' }}">
                <i class="fas fa-star"></i>
                Penilaian Layanan
            </a>
            <a href="{{ route('admin.activity-logs.index') }}" class="nav-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
                <i class="fas fa-history"></i>
                Activity Log
            </a>
        </div>
        @endif

        {{-- Super Admin Only: Sistem --}}
        @if(auth()->user()->isSuperAdmin())
        <div class="nav-section">
            <div class="nav-section-title">Sistem (Pustikom)</div>
            <a href="{{ route('admin.maintenance.index') }}" class="nav-link {{ request()->routeIs('admin.maintenance.*') ? 'active' : '' }}">
                <i class="fas fa-server"></i>
                Maintenance
            </a>
            <a href="{{ route('admin.admins.index') }}" class="nav-link {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
                <i class="fas fa-user-shield"></i>
                Kelola Admin
            </a>
        </div>
        @endif
    </nav>

    {{-- User Info --}}
    <div class="sidebar-user">
        <div class="sidebar-user-avatar">
            {{ strtoupper(substr(auth()->user()->nama, 0, 2)) }}
        </div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name">{{ auth()->user()->nama }}</div>
            <div class="sidebar-user-role">{{ auth()->user()->userType->label }}</div>
        </div>
        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
            @csrf
            <button type="submit" style="background:none; border:none; cursor:pointer; color:var(--muted); padding:6px; font-size:15px;" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </form>
    </div>
</aside>
