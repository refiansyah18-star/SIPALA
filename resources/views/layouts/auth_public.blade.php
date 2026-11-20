<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0A5E3D">
    <title>@yield('title') — SIPALA</title>
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
            --success-bg: #DDEFE4;
            --success-fg: #0A5E3D;
            --warn-bg: #FBF0D6;
            --warn-fg: #7A4E0E;
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
        }

        a { color: inherit; text-decoration: none; }

        .top-bar {
            width: 100%;
            max-width: @yield('max-width', '760px');
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .brand { display: inline-flex; align-items: center; gap: 11px; }
        .brand-logo {
            width: 38px; height: 38px; border-radius: 11px; padding: 5px;
            background: var(--primary); display: flex; align-items: center; justify-content: center;
        }
        .brand-logo svg { width: 100%; height: 100%; }
        .brand-name { font-size: 15.5px; font-weight: 700; color: var(--ink); line-height: 1.15; display: block; }
        .brand-sub { font-size: 11.5px; font-weight: 500; color: var(--muted); display: block; }

        .back-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 14px; border-radius: 10px; font-size: 13.5px; font-weight: 600;
            color: var(--muted); background: rgba(255, 255, 255, 0.8);
            border: 1px solid var(--line); transition: background .15s ease, color .15s ease;
        }
        .back-btn:hover { background: var(--surface); color: var(--primary); }

        .auth-card-wrap {
            width: 100%;
            max-width: @yield('max-width', '760px');
            margin: 24px auto;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 22px;
            box-shadow: 0 20px 48px -20px rgba(14, 66, 62, 0.16), 0 1px 3px rgba(14, 66, 62, 0.05);
            padding: 36px 40px;
        }

        .card-head { margin-bottom: 22px; }
        .card-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 11px; border-radius: 999px;
            background: var(--primary-soft); color: var(--primary);
            font-size: 12px; font-weight: 700; margin-bottom: 10px;
        }
        .card-head h1 { font-size: 24px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 6px; }
        .card-head p { font-size: 14px; color: var(--muted); }

        .alert-box {
            padding: 12px 14px; border-radius: 12px; font-size: 13.5px; font-weight: 500;
            display: flex; align-items: flex-start; gap: 10px; margin-bottom: 18px;
            border: 1px solid transparent;
        }
        .alert-box i { margin-top: 3px; }
        .alert-error { background: var(--danger-bg); border-color: rgba(216, 70, 59, 0.28); color: var(--danger-fg); }
        .alert-error i { color: var(--danger-dot); }
        .alert-success { background: var(--success-bg); border-color: rgba(10, 94, 61, 0.28); color: var(--success-fg); }
        .alert-warn { background: var(--warn-bg); border-color: rgba(210, 154, 30, 0.35); color: var(--warn-fg); }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        .form-group { margin-bottom: 16px; }
        .form-grid .form-group { margin-bottom: 0; }
        .col-span-2 { grid-column: span 2; }

        .form-label { display: block; font-size: 13.5px; font-weight: 600; color: var(--ink); margin-bottom: 6px; }
        .required { color: var(--danger-dot); }
        .form-hint { font-size: 12px; color: var(--muted); margin-top: 5px; }
        .field-error { font-size: 12px; color: var(--danger-fg); margin-top: 5px; font-weight: 500; }

        .form-input, .form-select {
            width: 100%; height: 44px; padding: 0 14px;
            border: 1.5px solid var(--line-strong); border-radius: 11px;
            background: var(--surface); color: var(--ink); font-family: inherit; font-size: 14px;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .form-input:focus, .form-select:focus {
            outline: none; border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(10, 94, 61, 0.12);
        }
        .form-input.is-invalid, .form-select.is-invalid { border-color: var(--danger-dot); }

        .input-wrapper { position: relative; }
        .toggle-password {
            position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: var(--subtle);
            width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
        }
        .toggle-password:hover { background: var(--sunken); color: var(--ink); }

        .upload-box {
            border: 1.5px dashed var(--line-strong); border-radius: 12px;
            padding: 14px 16px; background: var(--surface-alt);
        }
        .upload-box input[type="file"] { width: 100%; font-family: inherit; font-size: 13.5px; }

        .btn-submit {
            width: 100%; height: 46px; border: none; border-radius: 11px;
            background: var(--primary); color: #fff; font-family: inherit;
            font-size: 14.5px; font-weight: 600; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            transition: background .15s ease, transform .15s ease;
            margin-top: 6px;
        }
        .btn-submit:hover { background: var(--primary-hover); transform: translateY(-1px); }

        .foot-links {
            margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--line);
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 10px; font-size: 13px; color: var(--muted);
        }
        .foot-links a { font-weight: 600; color: var(--primary); }
        .foot-links a:hover { text-decoration: underline; }

        .page-foot {
            width: 100%; max-width: @yield('max-width', '760px'); margin: 0 auto;
            text-align: center; font-size: 12.5px; color: var(--subtle);
        }

        @media (max-width: 680px) {
            body { padding: 14px; }
            .auth-card-wrap { padding: 26px 20px; }
            .form-grid { grid-template-columns: 1fr; }
            .col-span-2 { grid-column: span 1; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <header class="top-bar">
        <a href="{{ route('landing') }}" class="brand">
            <span class="brand-logo"><x-logo /></span>
            <span>
                <span class="brand-name">SIPALA UINSSC</span>
                <span class="brand-sub">Layanan &amp; Pengaduan Akademik</span>
            </span>
        </a>

        <a href="{{ route('login') }}" class="back-btn">
            <i class="fas fa-arrow-left"></i> Kembali ke Login
        </a>
    </header>

    <main class="auth-card-wrap">
        @yield('content')
    </main>

    <footer class="page-foot">
        &copy; {{ date('Y') }} UIN Siber Syekh Nurjati Cirebon &middot; Sistem Pengaduan dan Layanan Akademik (SIPALA)
    </footer>

    <script>
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
    @stack('scripts')
</body>
</html>

