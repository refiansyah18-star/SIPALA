@extends('layouts.auth_public')

@section('title', 'Cek Status Verifikasi Alumni')
@section('max-width', '620px')

@section('content')
    <div class="card-head">
        <span class="card-badge"><i class="fas fa-magnifying-glass"></i> Pelacakan Pendaftaran</span>
        <h1>Cek Status Verifikasi Alumni</h1>
        <p>Masukkan NIM dan Email pribadi yang Anda gunakan saat mendaftar untuk melihat status konfirmasi Admin.</p>
    </div>

    @if(session('status_success'))
        <div class="alert-box alert-success" role="status">
            <i class="fas fa-circle-check"></i>
            <div>{{ session('status_success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert-box alert-error" role="alert">
            <i class="fas fa-circle-exclamation"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('alumni.status.check') }}">
        @csrf
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="nim">NIM <span class="required">*</span></label>
                <input type="text" id="nim" name="nim" class="form-input" value="{{ old('nim', $nim) }}" placeholder="NIM Anda" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email Pendaftaran <span class="required">*</span></label>
                <input type="email" id="email" name="email" class="form-input" value="{{ old('email', $email) }}" placeholder="Email yang didaftarkan" required>
            </div>
        </div>

        <button type="submit" class="btn-submit" style="margin-top: 16px;">
            <i class="fas fa-search"></i> Periksa Status Pengajuan
        </button>
    </form>

    @if($registration)
        <div style="margin-top: 24px; padding: 20px; border-radius: 14px; border: 1px solid var(--line); background: var(--surface-alt);">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 14px;">
                <div>
                    <div style="font-size: 16px; font-weight: 700; color: var(--ink);">{{ $registration->nama }}</div>
                    <div style="font-size: 13px; color: var(--muted);">NIM: {{ $registration->nim }} &middot; {{ $registration->programStudy?->name ?? '-' }} ({{ $registration->tahun_lulus }})</div>
                </div>
                @if($registration->isPending())
                    <span style="padding: 5px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; background: var(--warn-bg); color: var(--warn-fg);">
                        Menunggu Verifikasi Admin
                    </span>
                @elseif($registration->isApproved())
                    <span style="padding: 5px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; background: var(--success-bg); color: var(--success-fg);">
                        Disetujui &amp; Aktif
                    </span>
                @else
                    <span style="padding: 5px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; background: var(--danger-bg); color: var(--danger-fg);">
                        Ditolak
                    </span>
                @endif
            </div>

            @if($registration->isPending())
                <p style="font-size: 13.5px; color: var(--muted); margin: 0;">
                    Pengajuan diterima pada <strong>{{ $registration->created_at->translatedFormat('d M Y, H:i') }}</strong>. Notifikasi email akan dikirim otomatis setelah Admin memproses pengajuan Anda.
                </p>
            @elseif($registration->isApproved())
                <p style="font-size: 13.5px; color: var(--success-fg); margin-bottom: 12px;">
                    Akun alumni Anda telah aktif! Silakan masuk ke SIPALA menggunakan Email atau NIM serta kata sandi pendaftaran Anda.
                </p>
                <a href="{{ route('login') }}" class="btn-submit" style="margin-top: 0;">
                    <i class="fas fa-sign-in-alt"></i> Masuk ke SIPALA Sekarang
                </a>
            @else
                <div class="alert-box alert-error" style="margin-bottom: 14px;">
                    <i class="fas fa-ban"></i>
                    <div>
                        <strong>Alasan Penolakan:</strong><br>
                        {{ $registration->rejection_reason }}
                    </div>
                </div>
                <a href="{{ route('alumni.register') }}" class="btn-submit" style="margin-top: 0;">
                    <i class="fas fa-redo"></i> Ajukan Pendaftaran Ulang
                </a>
            @endif
        </div>
    @endif

    <div class="foot-links">
        <span>Belum mendaftar sebagai Alumni? <a href="{{ route('alumni.register') }}">Daftar di sini</a></span>
        <a href="{{ route('login') }}">Halaman Login</a>
    </div>
@endsection

