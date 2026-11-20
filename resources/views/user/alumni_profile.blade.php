@extends('layouts.app')

@section('title', 'Profil & Kata Sandi Alumni')
@section('page-title', 'Profil & Keamanan Akun Alumni')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="font-size: 22px; font-weight: 700; color: var(--gray-900); margin-bottom: 4px;">
                <i class="fas fa-user-cog" style="color: var(--primary); margin-right: 8px;"></i>
                Profil &amp; Kata Sandi Alumni
            </h2>
            <p style="color: var(--gray-500); font-size: 14px; margin: 0;">
                Kelola informasi kontak dan kata sandi akun Alumni SIPALA Anda.
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 22px; align-items: start;">
        {{-- Card 1: Informasi Akademik & Kontak --}}
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-id-card" style="color: var(--primary); margin-right: 8px;"></i> Data Alumni &amp; Kontak</h2>
                <span class="badge-status selesai">Terverifikasi</span>
            </div>
            <div class="card-body">
                <div style="background: var(--surface-alt); border: 1px solid var(--line); border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; display: grid; gap: 8px; font-size: 13.5px;">
                    <div style="display: flex; justify-content: space-between; gap: 10px;">
                        <span style="color: var(--muted);">Nama Lengkap</span>
                        <strong style="color: var(--ink); text-align: right;">{{ $user->nama }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; gap: 10px;">
                        <span style="color: var(--muted);">NIM</span>
                        <strong style="color: var(--ink); font-variant-numeric: tabular-nums;">{{ $user->nomor_identitas }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; gap: 10px;">
                        <span style="color: var(--muted);">Program Studi</span>
                        <strong style="color: var(--ink); text-align: right;">{{ $user->programStudy?->name ?? '-' }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; gap: 10px;">
                        <span style="color: var(--muted);">Tahun Lulus</span>
                        <strong style="color: var(--ink);">{{ $user->tahun_lulus ?? '-' }}</strong>
                    </div>
                </div>

                <form method="POST" action="{{ route('user.alumni-profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label" for="email">Email Login / Pribadi <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $user->email) }}" required>
                        <div style="font-size: 12px; color: var(--muted); margin-top: 4px;">Digunakan untuk masuk ke SIPALA dan menerima notifikasi tiket atau reset sandi.</div>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="no_hp">Nomor HP / WhatsApp <span class="required">*</span></label>
                        <input type="text" id="no_hp" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror"
                            value="{{ old('no_hp', $user->no_hp) }}" placeholder="08xxxxxxxxxx" required>
                        @error('no_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan Kontak
                    </button>
                </form>
            </div>
        </div>

        {{-- Card 2: Ganti Kata Sandi --}}
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-key" style="color: var(--accent); margin-right: 8px;"></i> Ubah Kata Sandi</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('user.alumni-profile.password') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label" for="current_password">Kata Sandi Saat Ini <span class="required">*</span></label>
                        <input type="password" id="current_password" name="current_password"
                            class="form-control @error('current_password') is-invalid @enderror"
                            placeholder="Masukkan kata sandi saat ini" required autocomplete="current-password">
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Kata Sandi Baru <span class="required">*</span></label>
                        <input type="password" id="password" name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Minimal 8 karakter" required autocomplete="new-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi Baru <span class="required">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="form-control" placeholder="Ulangi kata sandi baru" required autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-cta">
                        <i class="fas fa-lock"></i> Perbarui Kata Sandi
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

