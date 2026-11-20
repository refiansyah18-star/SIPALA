@extends('layouts.auth_public')

@section('title', 'Atur Ulang Kata Sandi Alumni')
@section('max-width', '540px')

@section('content')
    <div class="card-head">
        <span class="card-badge"><i class="fas fa-lock"></i> Kata Sandi Baru</span>
        <h1>Atur Ulang Kata Sandi</h1>
        <p>Buat kata sandi baru untuk akun Alumni SIPALA Anda.</p>
    </div>

    @if($errors->any())
        <div class="alert-box alert-error" role="alert">
            <i class="fas fa-circle-exclamation"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('alumni.password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group">
            <label class="form-label" for="email">Email Akun Alumni <span class="required">*</span></label>
            <input type="email" id="email" name="email" class="form-input @error('email') is-invalid @enderror"
                value="{{ old('email', $email) }}" placeholder="nama@email.com" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Kata Sandi Baru <span class="required">*</span></label>
            <div class="input-wrapper">
                <input type="password" id="password" name="password" class="form-input @error('password') is-invalid @enderror"
                    style="padding-right: 42px;" placeholder="Minimal 8 karakter" required autocomplete="new-password">
                <button type="button" class="toggle-password" onclick="toggleVis('password', this)" tabindex="-1">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi Baru <span class="required">*</span></label>
            <div class="input-wrapper">
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-input"
                    style="padding-right: 42px;" placeholder="Ulangi kata sandi baru" required autocomplete="new-password">
                <button type="button" class="toggle-password" onclick="toggleVis('password_confirmation', this)" tabindex="-1">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-submit">
            <i class="fas fa-check-circle"></i> Simpan Kata Sandi Baru
        </button>
    </form>
@endsection

@push('scripts')
<script>
    function toggleVis(id, btn) {
        const input = document.getElementById(id);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>
@endpush

