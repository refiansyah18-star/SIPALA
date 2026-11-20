<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPALA — Sistem Pengaduan dan Layanan Akademik</title>
    <meta name="description" content="SIPALA adalah sistem layanan dan pengaduan akademik universitas untuk mahasiswa, dosen, tenaga kependidikan, dan alumni.">
    <link rel="preload" as="image" href="{{ asset('images/gedung-kampus.jpg') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @font-face { font-family: 'Plus Jakarta Sans'; src: url('/fonts/plus-jakarta-sans/plus-jakarta-sans-latin-400-normal.woff2') format('woff2'); font-weight: 400; font-display: swap; }
        @font-face { font-family: 'Plus Jakarta Sans'; src: url('/fonts/plus-jakarta-sans/plus-jakarta-sans-latin-500-normal.woff2') format('woff2'); font-weight: 500; font-display: swap; }
        @font-face { font-family: 'Plus Jakarta Sans'; src: url('/fonts/plus-jakarta-sans/plus-jakarta-sans-latin-600-normal.woff2') format('woff2'); font-weight: 600; font-display: swap; }
        @font-face { font-family: 'Plus Jakarta Sans'; src: url('/fonts/plus-jakarta-sans/plus-jakarta-sans-latin-700-normal.woff2') format('woff2'); font-weight: 700; font-display: swap; }

        /* Palet & tipografi konsisten dengan dashboard (layouts/app.blade.php) */
        :root {
            --primary: #0A5E3D;
            --primary-hover: #084A30;
            --primary-light: #0D7048;
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
            --ink: #13261F;
            --muted: #52625A;
            --subtle: #7D8B84;
            --night: #0B0F19;
            --shadow-card: 0 1px 2px rgba(14, 66, 62, 0.04), 0 8px 24px -12px rgba(14, 66, 62, 0.12);
            --radius: 18px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; scroll-padding-top: 32px; }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 15px;
            line-height: 1.6;
            color: var(--ink);
            background-color: var(--canvas);
            background-image:
                radial-gradient(60rem 40rem at -10% -15%, rgba(10, 94, 61, 0.10), transparent 60%),
                radial-gradient(50rem 36rem at 110% 20%, rgba(187, 143, 50, 0.12), transparent 60%);
            background-attachment: fixed;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        a { color: inherit; text-decoration: none; }
        img { display: block; max-width: 100%; }

        .container { max-width: 1180px; margin: 0 auto; padding: 0 24px; }

        /* ---------- Buttons ---------- */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 11px 22px; border-radius: 12px;
            font-family: inherit; font-size: 14.5px; font-weight: 600;
            border: 1px solid transparent; cursor: pointer;
            transition: background .15s ease, box-shadow .15s ease, transform .15s ease, filter .15s ease;
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-gold {
            background: linear-gradient(135deg, #ECD18E 0%, #D4AE5A 50%, #BB8F32 100%);
            color: #1A1305; font-weight: 700;
            box-shadow: 0 10px 24px -10px rgba(140, 96, 59, 0.65);
        }
        .btn-gold:hover { filter: brightness(1.05); transform: translateY(-1px); }
        .btn-glass {
            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.22);
            color: #fff; font-weight: 600;
            backdrop-filter: blur(14px) saturate(140%);
            -webkit-backdrop-filter: blur(14px) saturate(140%);
        }
        .btn-glass:hover { background: rgba(255, 255, 255, 0.18); }

        /* ---------- Header + Hero (Gedung Kampus) ---------- */
        .hero-banner {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            background-color: var(--night);
            color: #fff;
        }
        .hero-bg {
            position: absolute;
            inset: 0;
            z-index: -2;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: 60% 40%;
        }
        .hero-overlay {
            position: absolute;
            inset: 0;
            z-index: -1;
            background-image:
                linear-gradient(180deg, rgba(11, 15, 25, 0.58) 0%, rgba(11, 15, 25, 0.38) 35%, rgba(11, 15, 25, 0.88) 100%),
                linear-gradient(90deg, rgba(11, 15, 25, 0.78) 0%, rgba(11, 15, 25, 0.18) 70%);
        }

        .header-inner {
            display: flex; align-items: center; justify-content: space-between;
            height: 76px; gap: 24px;
        }
        .brand { display: flex; align-items: center; gap: 12px; }
        .brand-logo {
            width: 40px; height: 40px; border-radius: 12px; padding: 5px;
            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.20);
            display: flex; align-items: center; justify-content: center;
        }
        .brand-logo svg { width: 100%; height: 100%; }
        .brand-name { font-size: 16px; font-weight: 700; color: #fff; letter-spacing: .02em; line-height: 1.15; display: block; }
        .brand-sub { font-size: 11.5px; font-weight: 500; color: rgba(255, 255, 255, 0.78); display: block; }

        .main-nav { display: flex; gap: 4px; margin-left: auto; }
        .main-nav a {
            padding: 8px 14px; border-radius: 10px;
            font-size: 14px; font-weight: 600; color: rgba(255, 255, 255, 0.85);
            transition: background .15s ease, color .15s ease;
        }
        .main-nav a:hover { background: rgba(255, 255, 255, 0.12); color: #fff; }

        .hero-content {
            padding: 68px 0 76px;
            max-width: 720px;
        }
        .hero-eyebrow {
            display: flex; align-items: center; gap: 12px;
            font-size: 13.5px; font-weight: 600; color: rgba(255, 255, 255, 0.88);
            margin-bottom: 16px;
        }
        .hero-eyebrow::before {
            content: '';
            width: 38px; height: 2px; border-radius: 999px;
            background: var(--accent);
        }
        .hero-content h1 {
            font-size: 46px; font-weight: 700; line-height: 1.12; letter-spacing: -0.025em;
            color: #fff; margin-bottom: 16px;
        }
        .hero-content h1 span {
            display: block; margin-top: 8px;
            font-size: 26px; font-weight: 600; letter-spacing: -0.01em;
            color: var(--cream); line-height: 1.25;
        }
        .hero-lead {
            font-size: 16px; color: rgba(255, 255, 255, 0.88);
            max-width: 600px; margin-bottom: 28px;
        }
        .hero-actions { display: flex; gap: 12px; flex-wrap: wrap; }

        /* ---------- Section base ---------- */
        .section { padding: 72px 0; }
        .section-white { background: var(--surface); border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); }
        .section-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 24px; margin-bottom: 32px; }
        .section-head h2 { font-size: 28px; font-weight: 700; letter-spacing: -0.02em; line-height: 1.25; margin-bottom: 6px; }
        .section-head p { color: var(--muted); max-width: 560px; font-size: 14.5px; }
        .section-meta { font-size: 13px; color: var(--subtle); white-space: nowrap; }

        /* ---------- Stat cards (pola dashboard) ---------- */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
        .stat-card {
            position: relative; overflow: hidden;
            background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius);
            box-shadow: var(--shadow-card); padding: 20px;
        }
        .stat-card::before { content: ''; position: absolute; inset: 0 0 auto 0; height: 3px; background: var(--c, var(--primary)); }
        .stat-icon {
            width: 40px; height: 40px; border-radius: 12px; margin-bottom: 12px;
            display: flex; align-items: center; justify-content: center; font-size: 15px;
        }
        .stat-value { font-size: 28px; font-weight: 700; letter-spacing: -0.02em; line-height: 1.15; }
        .stat-label { font-size: 13.5px; color: var(--muted); margin-top: 2px; }

        .panel-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .panel {
            background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius);
            box-shadow: var(--shadow-card);
        }
        .panel-head { padding: 18px 22px; border-bottom: 1px solid var(--line); display: flex; align-items: center; justify-content: space-between; }
        .panel-head h3 { font-size: 15.5px; font-weight: 700; }
        .panel-head span { font-size: 12.5px; color: var(--subtle); font-weight: 500; }
        .panel-body { padding: 22px; }

        /* Donut */
        .donut-wrap { display: flex; align-items: center; gap: 32px; }
        .donut { position: relative; width: 164px; height: 164px; flex-shrink: 0; }
        .donut svg { width: 100%; height: 100%; transform: rotate(-90deg); }
        .donut-center { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .donut-center strong { font-size: 26px; font-weight: 700; line-height: 1.1; }
        .donut-center span { font-size: 12px; color: var(--muted); }
        .legend { list-style: none; flex: 1; display: flex; flex-direction: column; gap: 10px; }
        .legend li { display: flex; align-items: center; gap: 10px; font-size: 14px; }
        .legend .dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
        .legend .name { flex: 1; color: var(--ink); font-weight: 500; }
        .legend .num { font-weight: 600; color: var(--ink); }
        .legend .pct { width: 48px; text-align: right; color: var(--subtle); font-size: 13px; }

        /* Bar list */
        .bar-list { list-style: none; display: flex; flex-direction: column; gap: 15px; }
        .bar-row { display: flex; justify-content: space-between; gap: 12px; font-size: 14px; margin-bottom: 6px; }
        .bar-row span:first-child { color: var(--ink); font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .bar-row span:last-child { font-weight: 600; flex-shrink: 0; }
        .bar-track { height: 8px; border-radius: 999px; background: var(--sunken); overflow: hidden; }
        .bar-fill { height: 100%; border-radius: 999px; background: var(--primary); }

        .empty { text-align: center; color: var(--subtle); font-size: 14px; padding: 28px 12px; }
        .empty i { display: block; font-size: 22px; margin-bottom: 8px; color: #C3CCC7; }

        /* ---------- Kategori layanan ---------- */
        .category-table {
            display: grid; grid-template-columns: repeat(3, 1fr);
            border: 1px solid var(--line); border-radius: var(--radius); overflow: hidden;
            background: var(--surface);
            box-shadow: var(--shadow-card);
        }
        .category-item {
            padding: 24px; border-right: 1px solid var(--line); border-bottom: 1px solid var(--line);
            display: flex; gap: 16px; transition: background .15s ease;
        }
        .category-item:nth-child(3n) { border-right: 0; }
        .category-item:hover { background: var(--surface-alt); }
        .category-icon {
            width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
            background: var(--primary-soft); color: var(--primary);
            display: flex; align-items: center; justify-content: center; font-size: 16px;
        }
        .category-item h3 { font-size: 15.5px; font-weight: 700; margin-bottom: 4px; }
        .category-item p { font-size: 13.5px; color: var(--muted); margin-bottom: 8px; }
        .category-item small { font-size: 12.5px; font-weight: 600; color: var(--accent-ink); }

        /* ---------- Alur Pengerjaan ---------- */
        .steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; position: relative; }
        .step { position: relative; padding-right: 24px; }
        .step-marker { display: flex; align-items: center; margin-bottom: 16px; }
        .step-num {
            width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
            background: var(--surface); border: 2px solid var(--c);
            color: var(--c); font-weight: 700; font-size: 14px;
            display: flex; align-items: center; justify-content: center;
        }
        .step-line { flex: 1; height: 2px; background: var(--line); margin-left: 12px; }
        .step:last-child .step-line { display: none; }
        .step-status {
            display: inline-block; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 999px;
            background: var(--bg); color: var(--c); margin-bottom: 8px;
        }
        .step h3 { font-size: 15.5px; font-weight: 700; margin-bottom: 6px; }
        .step p { font-size: 13.5px; color: var(--muted); }

        /* ---------- Ulasan ---------- */
        .reviews-layout { display: grid; grid-template-columns: 290px 1fr; gap: 24px; align-items: start; }
        .rating-summary {
            background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius);
            box-shadow: var(--shadow-card); padding: 26px;
        }
        .rating-big { font-size: 46px; font-weight: 700; letter-spacing: -0.03em; line-height: 1; }
        .rating-big small { font-size: 17px; color: var(--subtle); font-weight: 600; }
        .stars { color: #E0A82E; font-size: 14px; letter-spacing: 2px; margin: 10px 0 6px; }
        .stars .off { color: #DCD6C8; }
        .rating-summary p { font-size: 13.5px; color: var(--muted); }
        .rating-summary hr { border: 0; border-top: 1px solid var(--line); margin: 16px 0; }

        .review-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; }
        .review {
            background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius);
            box-shadow: var(--shadow-card); padding: 22px; display: flex; flex-direction: column; gap: 14px;
        }
        .review-top { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
        .review-top .stars { margin: 0; font-size: 13px; }
        .review-service { font-size: 12.5px; font-weight: 600; color: var(--primary); background: var(--primary-soft); padding: 3px 10px; border-radius: 999px; }
        .review blockquote { font-size: 14.5px; color: var(--ink); line-height: 1.6; flex: 1; }
        .review-author { display: flex; align-items: center; gap: 12px; padding-top: 14px; border-top: 1px solid var(--line); }
        .avatar {
            width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
            background: var(--primary); color: #fff; font-size: 12.5px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }
        .review-author strong { display: block; font-size: 13.5px; font-weight: 600; line-height: 1.3; }
        .review-author span { font-size: 12px; color: var(--subtle); }

        /* ---------- CTA ---------- */
        .cta {
            border-radius: 22px; padding: 32px 36px;
            background: linear-gradient(135deg, #0D7048 0%, #0A5E3D 55%, #0E423E 100%);
            color: #fff; display: flex; align-items: center; justify-content: space-between; gap: 28px;
            box-shadow: 0 18px 40px -18px rgba(10, 94, 61, 0.45);
        }
        .cta h2 { font-size: 22px; font-weight: 700; letter-spacing: -0.015em; margin-bottom: 4px; }
        .cta p { color: rgba(255, 255, 255, 0.88); font-size: 14.5px; max-width: 540px; }

        /* ---------- Footer ---------- */
        .site-footer { background: var(--night); color: rgba(255, 255, 255, 0.82); padding: 40px 0 24px; font-size: 14px; }
        .footer-grid { display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 36px; margin-bottom: 28px; }
        .footer-grid p { color: rgba(255, 255, 255, 0.68); margin-top: 10px; max-width: 340px; font-size: 13.5px; }
        .footer-grid h4 { font-size: 13px; font-weight: 700; color: #fff; margin-bottom: 12px; }
        .footer-grid ul { list-style: none; display: flex; flex-direction: column; gap: 8px; font-size: 13.5px; }
        .footer-grid ul a { color: rgba(255, 255, 255, 0.72); }
        .footer-grid ul a:hover { color: #fff; }
        .footer-bottom { border-top: 1px solid rgba(255, 255, 255, 0.10); padding-top: 18px; font-size: 12.5px; color: rgba(255, 255, 255, 0.55); text-align: center; }

        /* ---------- Responsive ---------- */
        @media (max-width: 1024px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .panel-grid { grid-template-columns: 1fr; }
            .category-table { grid-template-columns: repeat(2, 1fr); }
            .category-item:nth-child(3n) { border-right: 1px solid var(--line); }
            .category-item:nth-child(2n) { border-right: 0; }
            .reviews-layout { grid-template-columns: 1fr; }
            .steps { grid-template-columns: repeat(2, 1fr); row-gap: 28px; }
            .step:nth-child(2) .step-line { display: none; }
        }
        @media (max-width: 768px) {
            .main-nav { display: none; }
            .hero-content { padding: 44px 0 56px; }
            .hero-content h1 { font-size: 34px; }
            .hero-content h1 span { font-size: 20px; }
            .section { padding: 52px 0; }
            .section-head { flex-direction: column; align-items: flex-start; }
            .section-head h2 { font-size: 24px; }
            .donut-wrap { flex-direction: column; align-items: stretch; }
            .donut { margin: 0 auto; }
            .category-table { grid-template-columns: 1fr; }
            .category-item { border-right: 0 !important; }
            .steps { grid-template-columns: 1fr; }
            .step-line { display: none; }
            .cta { flex-direction: column; align-items: flex-start; padding: 26px 22px; }
            .footer-grid { grid-template-columns: 1fr; gap: 24px; }
        }
    </style>
</head>
<body>

@php
    $categoryIcons = [
        'monitor'   => 'desktop',
        'book-open' => 'book-open',
        'folder'    => 'folder-open',
        'award'     => 'award',
        'users'     => 'user-graduate',
        'briefcase' => 'briefcase',
    ];
    $fmt = fn ($n) => number_format($n, 0, ',', '.');
@endphp

{{-- ================= HEADER + HERO (GEDUNG KAMPUS) ================= --}}
<section class="hero-banner">
    <img src="{{ asset('images/gedung-kampus.jpg') }}" alt="Gedung UIN Siber Syekh Nurjati Cirebon" class="hero-bg">
    <div class="hero-overlay" aria-hidden="true"></div>

    <header class="container header-inner">
        <a href="{{ route('landing') }}" class="brand">
            <span class="brand-logo"><x-logo /></span>
            <span>
                <span class="brand-name">SIPALA UINSSC</span>
                <span class="brand-sub">Layanan &amp; Pengaduan Akademik</span>
            </span>
        </a>

        <nav class="main-nav">
            <a href="#kinerja">Kinerja Layanan</a>
            <a href="#alur">Alur Pengerjaan</a>
            <a href="#layanan">Kategori Layanan</a>
            <a href="#ulasan">Ulasan</a>
        </nav>

        @auth
            <a href="{{ route('dashboard') }}" class="btn btn-gold"><i class="fas fa-gauge"></i> Buka Dashboard</a>
        @else
            <a href="{{ route('login') }}" class="btn btn-gold"><i class="fas fa-arrow-right-to-bracket"></i> Masuk</a>
        @endauth
    </header>

    <div class="container">
        <div class="hero-content">
            <p class="hero-eyebrow">UIN Siber Syekh Nurjati Cirebon</p>
            <h1>
                SIPALA UINSSC
                <span>Satu pintu untuk setiap layanan &amp; pengaduan akademik.</span>
            </h1>
            <p class="hero-lead">
                Ajukan layanan administrasi akademik, dokumen kelulusan, maupun kendala sistem informasi kampus dengan target waktu penyelesaian yang terukur.
            </p>
            <div class="hero-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-gold"><i class="fas fa-gauge"></i> Buka Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-gold"><i class="fas fa-arrow-right-to-bracket"></i> Masuk &amp; Ajukan</a>
                @endauth
                <a href="#kinerja" class="btn btn-glass"><i class="fas fa-chart-pie"></i> Lihat Statistik</a>
            </div>
        </div>
    </div>
</section>

{{-- ================= KINERJA & STATISTIK ================= --}}
<section class="section" id="kinerja">
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Kinerja layanan</h2>
                <p>Ringkasan penyelesaian tiket dan distribusi layanan yang tercatat di SIPALA.</p>
            </div>
            <span class="section-meta">Diperbarui {{ $updatedAt->translatedFormat('d M Y, H:i') }} WIB</span>
        </div>

        <div class="stats-grid">
            <div class="stat-card" style="--c: var(--primary);">
                <div class="stat-icon" style="background: var(--primary-soft); color: var(--primary);"><i class="fas fa-ticket"></i></div>
                <div class="stat-value">{{ $fmt($totalTickets) }}</div>
                <div class="stat-label">Total tiket masuk</div>
            </div>
            <div class="stat-card" style="--c: #1E9E63;">
                <div class="stat-icon" style="background: #DDEFE4; color: #1E9E63;"><i class="fas fa-circle-check"></i></div>
                <div class="stat-value">{{ $fmt($completed) }}</div>
                <div class="stat-label">Tiket selesai ditangani</div>
            </div>
            <div class="stat-card" style="--c: #3B7DD8;">
                <div class="stat-icon" style="background: #E1ECF8; color: #3B7DD8;"><i class="fas fa-arrows-rotate"></i></div>
                <div class="stat-value">{{ $fmt($inProgress) }}</div>
                <div class="stat-label">Sedang diverifikasi / diproses</div>
            </div>
            <div class="stat-card" style="--c: var(--accent);">
                <div class="stat-icon" style="background: var(--accent-soft); color: var(--accent);"><i class="fas fa-stopwatch"></i></div>
                <div class="stat-value">{{ $slaRate !== null ? $slaRate . '%' : '—' }}</div>
                <div class="stat-label">Selesai sesuai target SLA</div>
            </div>
        </div>

        <div class="panel-grid">
            <div class="panel">
                <div class="panel-head">
                    <h3>Status seluruh tiket</h3>
                    <span>{{ $fmt($totalTickets) }} tiket</span>
                </div>
                <div class="panel-body">
                    @if($totalTickets > 0)
                        <div class="donut-wrap">
                            <div class="donut">
                                <svg viewBox="0 0 120 120" aria-hidden="true">
                                    <circle cx="60" cy="60" r="46" fill="none" stroke="#F1EEE4" stroke-width="16"/>
                                    @foreach($distribution as $d)
                                        @if($d['pct'] > 0)
                                            <circle cx="60" cy="60" r="46" fill="none" pathLength="100"
                                                    stroke="{{ $d['color'] }}" stroke-width="16"
                                                    stroke-dasharray="{{ $d['pct'] }} {{ 100 - $d['pct'] }}"
                                                    stroke-dashoffset="{{ -$d['offset'] }}"/>
                                        @endif
                                    @endforeach
                                </svg>
                                <div class="donut-center">
                                    <strong>{{ $fmt($totalTickets) }}</strong>
                                    <span>tiket</span>
                                </div>
                            </div>
                            <ul class="legend">
                                @foreach($distribution as $d)
                                    <li>
                                        <span class="dot" style="background: {{ $d['color'] }};"></span>
                                        <span class="name">{{ $d['label'] }}</span>
                                        <span class="num">{{ $fmt($d['count']) }}</span>
                                        <span class="pct">{{ number_format($d['pct'], 1, ',', '.') }}%</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="empty"><i class="fas fa-chart-pie"></i>Belum ada tiket yang tercatat.</div>
                    @endif
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h3>Layanan paling sering diajukan</h3>
                    <span>5 teratas</span>
                </div>
                <div class="panel-body">
                    @if($topServices->isNotEmpty())
                        @php $maxCount = $topServices->max('tickets_count'); @endphp
                        <ul class="bar-list">
                            @foreach($topServices as $service)
                                <li>
                                    <div class="bar-row">
                                        <span>{{ $service->name }}</span>
                                        <span>{{ $fmt($service->tickets_count) }}</span>
                                    </div>
                                    <div class="bar-track">
                                        <div class="bar-fill" style="width: {{ round($service->tickets_count / $maxCount * 100) }}%;"></div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="empty"><i class="fas fa-chart-bar"></i>Belum ada data pengajuan layanan.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ================= ALUR PENGERJAAN ================= --}}
<section class="section section-white" id="alur">
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Alur pengerjaan</h2>
                <p>Empat langkah mudah dari pengajuan tiket hingga layanan selesai.</p>
            </div>
        </div>

        <div class="steps">
            <div class="step" style="--c: #0A5E3D; --bg: #E2EFE7;">
                <div class="step-marker"><span class="step-num">1</span><span class="step-line"></span></div>
                <span class="step-status">Langkah 1</span>
                <h3>Login akun kampus</h3>
                <p>Masuk ke SIPALA menggunakan akun kampus yang terdaftar (NIM untuk Mahasiswa, NIP untuk Dosen/Pegawai, atau akun Alumni).</p>
            </div>
            <div class="step" style="--c: #D29A1E; --bg: #FBF0D6;">
                <div class="step-marker"><span class="step-num">2</span><span class="step-line"></span></div>
                <span class="step-status">Langkah 2</span>
                <h3>Pilih layanan &amp; ajukan tiket</h3>
                <p>Pilih jenis layanan yang dibutuhkan, isi perihal dan rincian pengajuan, lalu unggah berkas persyaratan.</p>
            </div>
            <div class="step" style="--c: #3B7DD8; --bg: #E1ECF8;">
                <div class="step-marker"><span class="step-num">3</span><span class="step-line"></span></div>
                <span class="step-status">Langkah 3</span>
                <h3>Diproses petugas</h3>
                <p>Admin memverifikasi kelengkapan berkas dan menugaskan staf terkait untuk mengerjakan tiket sesuai target waktu (SLA).</p>
            </div>
            <div class="step" style="--c: #1E9E63; --bg: #DDEFE4;">
                <div class="step-marker"><span class="step-num">4</span><span class="step-line"></span></div>
                <span class="step-status">Langkah 4</span>
                <h3>Selesai &amp; beri rating</h3>
                <p>Terima hasil penyelesaian tiket beserta notifikasi email, lalu berikan penilaian 1 sampai 5 bintang.</p>
            </div>
        </div>
    </div>
</section>

{{-- ================= KATEGORI LAYANAN ================= --}}
<section class="section" id="layanan">
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Kategori layanan</h2>
                <p>Setiap kategori memiliki standar persyaratan dan estimasi waktu penanganan masing-masing.</p>
            </div>
        </div>

        @if($categories->isNotEmpty())
            <div class="category-table">
                @foreach($categories as $category)
                    <div class="category-item">
                        <div class="category-icon">
                            <i class="fas fa-{{ $categoryIcons[$category->icon] ?? 'folder-open' }}"></i>
                        </div>
                        <div>
                            <h3>{{ $category->name }}</h3>
                            <p>{{ $category->description ?: 'Layanan administrasi akademik universitas.' }}</p>
                            <small>{{ $category->services_count }} jenis layanan</small>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="panel"><div class="empty"><i class="fas fa-folder-open"></i>Kategori layanan belum tersedia.</div></div>
        @endif
    </div>
</section>

{{-- ================= ULASAN ================= --}}
<section class="section section-white" id="ulasan">
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Ulasan pemohon</h2>
                <p>Penilaian positif dari sivitas akademika setelah tiket dinyatakan selesai.</p>
            </div>
        </div>

        <div class="reviews-layout">
            <aside class="rating-summary">
                @if($ratingAvg)
                    <div class="rating-big">{{ number_format($ratingAvg, 1, ',', '.') }} <small>/ 5</small></div>
                    <div class="stars">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= round($ratingAvg) ? '' : 'off' }}"></i>
                        @endfor
                    </div>
                    <p>Berdasarkan {{ $fmt($ratingCount) }} penilaian dari pemohon layanan.</p>
                @else
                    <div class="rating-big">— <small>/ 5</small></div>
                    <p style="margin-top: 10px;">Belum ada penilaian yang masuk.</p>
                @endif
                <hr>
                <p>Nama pemohon disamarkan untuk menjaga privasi.</p>
            </aside>

            @if($reviews->isNotEmpty())
                <div class="review-grid">
                    @foreach($reviews as $review)
                        <article class="review">
                            <div class="review-top">
                                <span class="review-service">{{ $review['service'] }}</span>
                                <span class="stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $review['rating'] ? '' : 'off' }}"></i>
                                    @endfor
                                </span>
                            </div>
                            <blockquote>&ldquo;{{ $review['comment'] }}&rdquo;</blockquote>
                            <div class="review-author">
                                <span class="avatar">{{ $review['initial'] }}</span>
                                <div>
                                    <strong>{{ $review['name'] }}</strong>
                                    <span>{{ $review['role'] }} · {{ $review['date'] }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="panel"><div class="empty"><i class="fas fa-comment-dots"></i>Ulasan akan tampil di sini setelah pemohon memberikan penilaian.</div></div>
            @endif
        </div>
    </div>
</section>

{{-- ================= CTA ================= --}}
<section class="section">
    <div class="container">
        <div class="cta">
            <div>
                <h2>Butuh layanan atau ingin menyampaikan pengaduan akademik?</h2>
                <p>Masuk menggunakan akun kampus yang terdaftar dan pantau tahapan pengerjaan tiket Anda sampai selesai.</p>
            </div>
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-gold"><i class="fas fa-gauge"></i> Buka Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-gold"><i class="fas fa-plus"></i> Buat Pengaduan</a>
            @endauth
        </div>
    </div>
</section>

{{-- ================= FOOTER ================= --}}
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <a href="{{ route('landing') }}" class="brand">
                    <span class="brand-logo"><x-logo /></span>
                    <span>
                        <span class="brand-name">SIPALA UINSSC</span>
                        <span class="brand-sub">Layanan &amp; Pengaduan Akademik</span>
                    </span>
                </a>
                <p>Sistem Pengaduan dan Layanan Akademik terpadu UIN Siber Syekh Nurjati Cirebon.</p>
            </div>
            <div>
                <h4>Bagian Halaman</h4>
                <ul>
                    <li><a href="#kinerja">Kinerja layanan</a></li>
                    <li><a href="#alur">Alur pengerjaan</a></li>
                    <li><a href="#layanan">Kategori layanan</a></li>
                    <li><a href="#ulasan">Ulasan pemohon</a></li>
                </ul>
            </div>
            <div>
                <h4>Akses</h4>
                <ul>
                    <li><a href="{{ route('login') }}">Masuk ke SIPALA</a></li>
                    <li><a href="{{ route('login') }}">Pantau status tiket</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; {{ date('Y') }} UIN Siber Syekh Nurjati Cirebon · Sistem Pengaduan dan Layanan Akademik (SIPALA)
        </div>
    </div>
</footer>

</body>
</html>
