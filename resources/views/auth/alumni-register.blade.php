@extends('layouts.auth_public')

@section('title', 'Pendaftaran Akun Alumni')
@section('max-width', '780px')

@section('content')
    <div class="card-head">
        <span class="card-badge"><i class="fas fa-user-graduate"></i> Khusus Alumni UINSSC</span>
        <h1>Daftar Akun Alumni SIPALA</h1>
        <p>Ajukan aktivasi akun layanan alumni dengan mengunggah bukti kelulusan. Admin akan memverifikasi data Anda sebelum akun dapat digunakan.</p>
    </div>

    @if($errors->any())
        <div class="alert-box alert-error" role="alert">
            <i class="fas fa-circle-exclamation"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('alumni.register.submit') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="nim">NIM Saat Kuliah <span class="required">*</span></label>
                <input type="text" id="nim" name="nim" class="form-input @error('nim') is-invalid @enderror"
                    value="{{ old('nim') }}" placeholder="Contoh: 2108102029" required autofocus>
                @error('nim')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="nama">Nama Lengkap (Sesuai Ijazah) <span class="required">*</span></label>
                <input type="text" id="nama" name="nama" class="form-input @error('nama') is-invalid @enderror"
                    value="{{ old('nama') }}" placeholder="Masukkan nama lengkap" required>
                @error('nama')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="program_study_id">Program Studi <span class="required">*</span></label>
                @php
                    $levelGroups = [
                        'S1'  => 'Program Sarjana (S1)',
                        'PJJ' => 'Program Pendidikan Jarak Jauh (PJJ)',
                        'PPG' => 'Program Profesi (PPG)',
                        'S2'  => 'Program Magister (S2)',
                        'S3'  => 'Program Doktoral (S3)',
                    ];
                    $groupedProdi = $programStudies->groupBy('level');
                @endphp
                <select id="program_study_id" name="program_study_id" class="form-select @error('program_study_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Program Studi --</option>
                    @foreach($levelGroups as $levelKey => $levelLabel)
                        @if($groupedProdi->has($levelKey))
                            <optgroup label="{{ $levelLabel }}">
                                @foreach($groupedProdi->get($levelKey) as $prodi)
                                    <option value="{{ $prodi->id }}" {{ old('program_study_id') == $prodi->id ? 'selected' : '' }}>
                                        Prodi {{ $prodi->name }} ({{ $prodi->level }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
                @error('program_study_id')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="tahun_lulus">Tahun Lulus / Wisuda <span class="required">*</span></label>
                <input type="number" id="tahun_lulus" name="tahun_lulus" class="form-input @error('tahun_lulus') is-invalid @enderror"
                    value="{{ old('tahun_lulus') }}" min="1970" max="{{ date('Y') }}" placeholder="Contoh: 2024" required>
                @error('tahun_lulus')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Pribadi Aktif <span class="required">*</span></label>
                <input type="email" id="email" name="email" class="form-input @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" placeholder="nama@email.com" required>
                <div class="form-hint">Digunakan untuk login dan menerima notifikasi verifikasi/reset sandi.</div>
                @error('email')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="no_hp">Nomor HP / WhatsApp Aktif <span class="required">*</span></label>
                <input type="text" id="no_hp" name="no_hp" class="form-input @error('no_hp') is-invalid @enderror"
                    value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx" required>
                @error('no_hp')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group col-span-2">
                <label class="form-label" for="proof_file">Bukti Kelulusan (Ijazah / SKL / Transkrip Nilai) <span class="required">*</span></label>
                <div class="upload-box">
                    <input type="file" id="proof_file" name="proof_file" accept=".pdf,.jpg,.jpeg,.png" required>
                    <div class="form-hint">Format: PDF, JPG, JPEG, atau PNG (maksimal 2 MB). Berkas disimpan aman secara privat hanya untuk verifikasi Admin.</div>
                </div>
                @error('proof_file')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Kata Sandi Akun <span class="required">*</span></label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" class="form-input @error('password') is-invalid @enderror"
                        style="padding-right: 42px;" placeholder="Minimal 8 karakter" required autocomplete="new-password">
                    <button type="button" class="toggle-password" onclick="toggleVis('password', this)" tabindex="-1">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                @error('password')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi <span class="required">*</span></label>
                <div class="input-wrapper">
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input"
                        style="padding-right: 42px;" placeholder="Ulangi kata sandi" required autocomplete="new-password">
                    <button type="button" class="toggle-password" onclick="toggleVis('password_confirmation', this)" tabindex="-1">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-submit" style="margin-top: 22px;">
            <i class="fas fa-paper-plane"></i> Kirim Pendaftaran Alumni
        </button>
    </form>

    <div class="foot-links">
        <span>Sudah mendaftar? <a href="{{ route('alumni.status') }}">Cek Status Verifikasi</a></span>
        <span>Sudah punya akun aktif? <a href="{{ route('login') }}">Masuk ke SIPALA</a></span>
    </div>
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

