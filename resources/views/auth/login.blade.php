<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0A5E3D">
    <title>Masuk — SIPALA</title>
    <meta name="description" content="Masuk ke SIPALA — Sistem Pengaduan dan Layanan Akademik UIN Siber Syekh Nurjati Cirebon">
    <link rel="preload" as="image" href="{{ asset('images/gedung-kampus.jpg') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @font-face { font-family: 'Plus Jakarta Sans'; src: url('/fonts/plus-jakarta-sans/plus-jakarta-sans-latin-400-normal.woff2') format('woff2'); font-weight: 400; font-style: normal; font-display: swap; }
        @font-face { font-family: 'Plus Jakarta Sans'; src: url('/fonts/plus-jakarta-sans/plus-jakarta-sans-latin-500-normal.woff2') format('woff2'); font-weight: 500; font-style: normal; font-display: swap; }
        @font-face { font-family: 'Plus Jakarta Sans'; src: url('/fonts/plus-jakarta-sans/plus-jakarta-sans-latin-600-normal.woff2') format('woff2'); font-weight: 600; font-style: normal; font-display: swap; }
        @font-face { font-family: 'Plus Jakarta Sans'; src: url('/fonts/plus-jakarta-sans/plus-jakarta-sans-latin-700-normal.woff2') format('woff2'); font-weight: 700; font-style: normal; font-display: swap; }

        :root {
            --primary: #0A5E3D;
            --primary-hover: #084A30;
            --primary-soft: #E2EFE7;
            --accent: #BB8F32;
            --accent-soft: #F4EAD3;
            --accent-ink: #7C5418;
            --cream: #F3E2B5;
            --canvas: #F6F3EA;
            --surface: #FFFFFF;
            --surface-alt: #FAF8F2;
            --sunken: #F1EEE4;
            --line: rgba(14, 66, 62, 0.12);
            --line-strong: #C5CEC9;
            --ink: #13261F;
            --muted: #52625A;
            --subtle: #7D8B84;
            --danger-bg: #FBE2DF;
            --danger-fg: #972A22;
            --danger-dot: #D8463B;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 15px;
            line-height: 1.55;
            color: var(--ink);
            min-height: 100vh;
            min-height: 100dvh;
            background-color: var(--canvas);
            background-image:
                radial-gradient(55rem 35rem at 0% 0%, rgba(10, 94, 61, 0.10), transparent 60%),
                radial-gradient(50rem 35rem at 100% 100%, rgba(187, 143, 50, 0.12), transparent 60%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 24px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        a { color: inherit; text-decoration: none; }

        /* ---------- Top Bar ---------- */
        .top-bar {
            width: 100%;
            max-width: 980px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 11px;
        }
        .brand-logo {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            padding: 5px;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .brand-logo svg { width: 100%; height: 100%; }
        .brand-name {
            font-size: 15.5px;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.15;
            display: block;
        }
        .brand-sub {
            font-size: 11.5px;
            font-weight: 500;
            color: var(--muted);
            display: block;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--muted);
            background: rgba(255, 255, 255, 0.75);
            border: 1px solid var(--line);
            transition: background .15s ease, color .15s ease;
        }
        .back-btn:hover {
            background: var(--surface);
            color: var(--primary);
        }

        /* ---------- Main Split Container ---------- */
        .auth-stage {
            width: 100%;
            max-width: 980px;
            margin: 20px auto;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 22px;
            box-shadow: 0 20px 48px -20px rgba(14, 66, 62, 0.16), 0 1px 3px rgba(14, 66, 62, 0.05);
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            overflow: hidden;
        }

        /* ---------- Left: Form Side ---------- */
        .form-pane {
            padding: 44px 44px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-head h1 {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--ink);
            margin-bottom: 6px;
        }
        .form-head p {
            font-size: 14px;
            color: var(--muted);
        }

        /* Role info strip */
        .role-tags {
            margin-top: 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .role-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 8px;
            background: var(--surface-alt);
            border: 1px solid var(--line);
            font-size: 12px;
            color: var(--muted);
        }
        .role-tag strong {
            color: var(--ink);
            font-weight: 600;
        }

        .alert-error {
            margin-top: 18px;
            background: var(--danger-bg);
            border: 1px solid rgba(216, 70, 59, 0.28);
            color: var(--danger-fg);
            padding: 11px 14px;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 500;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .alert-error i {
            margin-top: 3px;
            color: var(--danger-dot);
        }

        .alert-success-box {
            margin-top: 18px;
            background: #DDEFE4;
            border: 1px solid rgba(10, 94, 61, 0.28);
            color: #0A5E3D;
            padding: 11px 14px;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 500;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .alert-success-box i {
            margin-top: 3px;
            color: #1E9E63;
        }

        .forgot-link {
            font-size: 13px;
            font-weight: 600;
            color: var(--primary);
            transition: color .15s ease;
        }
        .forgot-link:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        .alumni-cta-box {
            margin-top: 18px;
            padding: 12px 14px;
            border-radius: 12px;
            background: var(--surface-alt);
            border: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            font-size: 12.5px;
            color: var(--muted);
        }
        .alumni-cta-links {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .alumni-cta-links a {
            font-weight: 700;
            color: var(--primary);
        }
        .alumni-cta-links a:hover {
            text-decoration: underline;
        }

        .login-form {
            margin-top: 22px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 6px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--subtle);
            font-size: 14px;
            pointer-events: none;
            transition: color .15s ease;
        }

        .form-input {
            width: 100%;
            height: 46px;
            padding: 0 14px 0 40px;
            border: 1.5px solid var(--line-strong);
            border-radius: 11px;
            background: var(--surface);
            color: var(--ink);
            font-family: inherit;
            font-size: 14.5px;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .form-input::placeholder {
            color: #95A09A;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(10, 94, 61, 0.12);
        }
        .form-input:focus ~ .input-icon {
            color: var(--primary);
        }
        .form-input.is-invalid {
            border-color: var(--danger-dot);
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--subtle);
            font-size: 14px;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s ease, color .15s ease;
        }
        .toggle-password:hover {
            background: var(--sunken);
            color: var(--ink);
        }

        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 16px 0 22px;
        }
        .form-check {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
        }
        .form-check input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
            cursor: pointer;
        }
        .form-check span {
            font-size: 13.5px;
            font-weight: 500;
            color: var(--muted);
        }

        .btn-submit {
            width: 100%;
            height: 46px;
            border: none;
            border-radius: 11px;
            background: var(--primary);
            color: #fff;
            font-family: inherit;
            font-size: 14.5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: background .15s ease, transform .15s ease;
        }
        .btn-submit:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }
        .btn-submit:active {
            transform: translateY(0);
        }

        .sso-note {
            margin-top: 22px;
            padding-top: 16px;
            border-top: 1px solid var(--line);
            font-size: 12.5px;
            color: var(--muted);
            line-height: 1.55;
        }
        .sso-note strong {
            color: var(--ink);
            font-weight: 600;
        }

        /* ---------- Right: Framed Building Photo ---------- */
        .visual-pane {
            padding: 12px 12px 12px 0;
            display: flex;
        }
        .visual-frame {
            position: relative;
            width: 100%;
            min-height: 480px;
            border-radius: 16px;
            overflow: hidden;
            background: #0B0F19;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 28px;
            color: #fff;
        }
        .visual-img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: 58% 35%;
        }
        .visual-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                180deg,
                rgba(11, 15, 25, 0.35) 0%,
                rgba(11, 15, 25, 0.20) 40%,
                rgba(11, 15, 25, 0.88) 100%
            );
        }

        .visual-top,
        .visual-bottom {
            position: relative;
            z-index: 1;
        }

        .visual-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(11, 15, 25, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            font-size: 12px;
            font-weight: 600;
            color: var(--cream);
        }
        .visual-tag span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--accent);
        }

        .visual-bottom h2 {
            font-size: 22px;
            font-weight: 700;
            line-height: 1.25;
            letter-spacing: -0.015em;
            margin-bottom: 8px;
        }
        .visual-bottom p {
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.82);
            line-height: 1.55;
            margin-bottom: 18px;
        }

        .visual-steps {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            padding-top: 14px;
            border-top: 1px solid rgba(255, 255, 255, 0.16);
        }
        .visual-step {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.88);
        }
        .visual-step small {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--cream);
            margin-bottom: 2px;
        }

        /* ---------- Page Footer ---------- */
        .page-foot {
            width: 100%;
            max-width: 980px;
            margin: 0 auto;
            text-align: center;
            font-size: 12.5px;
            color: var(--subtle);
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 860px) {
            body { padding: 16px; }
            .auth-stage {
                grid-template-columns: 1fr;
                max-width: 460px;
            }
            .visual-pane {
                display: none;
            }
            .top-bar, .page-foot {
                max-width: 460px;
            }
            .form-pane {
                padding: 32px 26px;
            }
        }
    </style>
</head>
<body>

    {{-- Top Navigation --}}
    <header class="top-bar">
        <a href="{{ route('landing') }}" class="brand">
            <span class="brand-logo"><x-logo /></span>
            <span>
                <span class="brand-name">SIPALA UINSSC</span>
                <span class="brand-sub">Layanan &amp; Pengaduan Akademik</span>
            </span>
        </a>

        <a href="{{ route('landing') }}" class="back-btn">
            <i class="fas fa-arrow-left"></i> Beranda
        </a>
    </header>

    {{-- Main Split Card --}}
    <main class="auth-stage">
        {{-- Left: Form --}}
        <section class="form-pane">
            <div class="form-head">
                <h1>Masuk ke akun</h1>
                <p>Silakan masuk untuk mengajukan atau memantau status tiket layanan.</p>
            </div>

            <div class="role-tags" aria-label="Petunjuk jenis akun">
                <span class="role-tag"><strong>Mahasiswa:</strong> NIM</span>
                <span class="role-tag"><strong>Dosen/Pegawai:</strong> NIP</span>
                <span class="role-tag"><strong>Alumni:</strong> Email</span>
            </div>

            @if(session('status') || session('success'))
                <div class="alert-success-box" role="status">
                    <i class="fas fa-circle-check"></i>
                    <span>{{ session('status') ?? session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert-error" role="alert">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" class="login-form">
                @csrf

                <div class="form-group">
                    <label for="login-input" class="form-label">NIM / NIP / Email</label>
                    <div class="input-wrapper">
                        <input
                            type="text"
                            name="login"
                            id="login-input"
                            class="form-input @error('login') is-invalid @enderror"
                            placeholder="Masukkan NIM, NIP, atau email"
                            value="{{ old('login') }}"
                            autocomplete="username"
                            autocapitalize="none"
                            spellcheck="false"
                            required
                            autofocus
                        >
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password-input" class="form-label">Kata Sandi</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            name="password"
                            id="password-input"
                            class="form-input @error('password') is-invalid @enderror"
                            placeholder="Masukkan kata sandi"
                            autocomplete="current-password"
                            style="padding-right: 44px;"
                            required
                        >
                        <i class="fas fa-lock input-icon"></i>
                        <button type="button" class="toggle-password" onclick="togglePassword()" id="togglePasswordBtn" tabindex="-1" title="Tampilkan kata sandi">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-check" for="remember">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>Ingat sesi saya</span>
                    </label>
                    <a href="{{ route('alumni.password.request') }}" class="forgot-link">
                        Lupa kata sandi alumni?
                    </a>
                </div>

                <button type="submit" class="btn-submit" id="btn-login">
                    Masuk ke SIPALA <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="alumni-cta-box">
                <span><strong>Alumni</strong> belum punya akun?</span>
                <span class="alumni-cta-links">
                    <a href="{{ route('alumni.register') }}"><i class="fas fa-user-plus"></i> Daftar Akun Alumni</a>
                    <span style="color: var(--line-strong);">&middot;</span>
                    <a href="{{ route('alumni.status') }}">Cek Status</a>
                </span>
            </div>

            <p class="sso-note">
                <strong>Terintegrasi Portal Kampus:</strong> mahasiswa dan dosen menggunakan kata sandi akun SSO UIN Siber Syekh Nurjati Cirebon.
            </p>
        </section>

        {{-- Right: Framed Campus Photo --}}
        <aside class="visual-pane" aria-hidden="true">
            <div class="visual-frame">
                <img src="{{ asset('images/gedung-kampus.jpg') }}" alt="" class="visual-img">
                <div class="visual-overlay"></div>

                <div class="visual-top">
                    <span class="visual-tag">
                        <span></span> UIN Siber Syekh Nurjati Cirebon
                    </span>
                </div>

                <div class="visual-bottom">
                    <h2>Sistem Pengaduan &amp; Layanan Akademik</h2>
                    <p>Satu pintu pengajuan administrasi akademik, dokumen kelulusan, dan pelaporan kendala sistem informasi kampus.</p>

                    <div class="visual-steps">
                        <div class="visual-step">
                            <small>01</small>
                            Pilih layanan &amp; unggah berkas
                        </div>
                        <div class="visual-step">
                            <small>02</small>
                            Diverifikasi &amp; diproses petugas
                        </div>
                        <div class="visual-step">
                            <small>03</small>
                            Terima hasil &amp; beri rating
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </main>

    <footer class="page-foot">
        &copy; {{ date('Y') }} UIN Siber Syekh Nurjati Cirebon &middot; Sistem Pengaduan dan Layanan Akademik (SIPALA)
    </footer>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password-input');
            const eyeIcon = document.getElementById('eyeIcon');
            const toggleBtn = document.getElementById('togglePasswordBtn');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
                toggleBtn.title = 'Sembunyikan kata sandi';
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
                toggleBtn.title = 'Tampilkan kata sandi';
            }
        }

        // Sinkronisasi token CSRF otomatis saat berpindah tab atau kembali dengan tombol Back (mencegah 419 Page Expired)
        function syncCsrfToken() {
            fetch('{{ route('csrf.token') }}', {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store'
            })
            .then(function(res) { return res.ok ? res.json() : null; })
            .then(function(data) {
                if (data && data.token) {
                    document.querySelectorAll('input[name="_token"]').forEach(function(el) {
                        el.value = data.token;
                    });
                }
            })
            .catch(function() {});
        }

        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            } else {
                syncCsrfToken();
            }
        });
        window.addEventListener('focus', syncCsrfToken);
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                syncCsrfToken();
            }
        });
    </script>
</body>
</html>
