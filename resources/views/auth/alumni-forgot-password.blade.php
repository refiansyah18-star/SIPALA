@extends('layouts.auth_public')

@section('title', 'Lupa Kata Sandi Alumni')
@section('max-width', '540px')

@section('content')
    <div class="card-head">
        <span class="card-badge"><i class="fas fa-key"></i> Pemulihan Akun Alumni</span>
        <h1>Lupa Kata Sandi Alumni</h1>
        <p>Masukkan email pribadi yang terdaftar pada akun <strong>Alumni SIPALA</strong> Anda. Kami akan mengirimkan tautan untuk membuat kata sandi baru.</p>
    </div>

    @if(session('status'))
        <div class="alert-box alert-success" role="status">
            <i class="fas fa-circle-check"></i>
            <div>{{ session('status') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert-box alert-error" role="alert">
            <i class="fas fa-circle-exclamation"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <div class="alert-box alert-warn">
        <i class="fas fa-info-circle"></i>
        <div><strong>Catatan:</strong> Fitur ini khusus untuk <strong>Alumni</strong>. Mahasiswa dan Dosen/Pegawai menggunakan kata sandi SSO Portal Kampus.</div>
    </div>

    <form method="POST" action="{{ route('alumni.password.email') }}">
        @csrf
        <div class="form-group">
            <label class="form-label" for="email">Email Akun Alumni <span class="required">*</span></label>
            <input type="email" id="email" name="email" class="form-input @error('email') is-invalid @enderror"
                value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
        </div>

        <button type="submit" class="btn-submit">
            <i class="fas fa-envelope"></i> Kirim Tautan Reset Kata Sandi
        </button>
    </form>

    <div class="foot-links">
        <span>Ingat kata sandi? <a href="{{ route('login') }}">Masuk sekarang</a></span>
        <a href="{{ route('alumni.register') }}">Daftar Akun Alumni</a>
    </div>
@endsection

