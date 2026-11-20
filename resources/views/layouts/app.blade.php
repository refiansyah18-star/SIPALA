<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIPALA') — Sistem Pengaduan dan Layanan Akademik</title>
    <meta name="description" content="SIPALA - Sistem Pengaduan dan Layanan Akademik Terintegrasi">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* =============================================
           SELF-HOSTED PLUS JAKARTA SANS
           ============================================= */
        @font-face {
            font-family: 'Plus Jakarta Sans';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url('{{ asset('fonts/plus-jakarta-sans/plus-jakarta-sans-latin-400-normal.woff2') }}') format('woff2');
        }
        @font-face {
            font-family: 'Plus Jakarta Sans';
            font-style: normal;
            font-weight: 500;
            font-display: swap;
            src: url('{{ asset('fonts/plus-jakarta-sans/plus-jakarta-sans-latin-500-normal.woff2') }}') format('woff2');
        }
        @font-face {
            font-family: 'Plus Jakarta Sans';
            font-style: normal;
            font-weight: 600;
            font-display: swap;
            src: url('{{ asset('fonts/plus-jakarta-sans/plus-jakarta-sans-latin-600-normal.woff2') }}') format('woff2');
        }
        @font-face {
            font-family: 'Plus Jakarta Sans';
            font-style: normal;
            font-weight: 700;
            font-display: swap;
            src: url('{{ asset('fonts/plus-jakarta-sans/plus-jakarta-sans-latin-700-normal.woff2') }}') format('woff2');
        }

        /* =============================================
           SIPALA DESIGN SYSTEM — Academic Modern Theme
           Inspired by UINSSC Helpdesk Fakultas Terpadu
           ============================================= */

        :root {
            /* Brand — Deep Islamic Green */
            --primary: #0A5E3D;
            --primary-hover: #084A30;
            --primary-dark: #07452C;
            --primary-light: #0D7048;
            --primary-soft: #E2EFE7;
            --on-primary: #FFFFFF;

            /* Accent — Golden Amber */
            --accent: #BB8F32;
            --accent-hover: #A57D28;
            --accent-light: #D4AE5A;
            --accent-dark: #8C603B;
            --accent-soft: #F4EAD3;
            --accent-ink: #7C5418;
            --cream: #F3E2B5;
            --on-cta: #1A1305;

            /* Canvas & Surfaces (Warm Ivory) */
            --canvas: #F6F3EA;
            --surface: #FFFFFF;
            --surface-alt: #FAF8F2;
            --sunken: #F1EEE4;
            --line: rgba(14, 66, 62, 0.12);
            --line-subtle: rgba(14, 66, 62, 0.06);
            --line-strong: #7D8A83;
            --bg: #F6F3EA;

            /* Text */
            --ink: #13261F;
            --muted: #52625A;

            /* Neutrals */
            --gray-50: #FAF8F2;
            --gray-100: #F1EEE4;
            --gray-200: #E5E1D5;
            --gray-300: #D1CCC0;
            --gray-400: #9C9688;
            --gray-500: #6B685E;
            --gray-600: #52625A;
            --gray-700: #373A34;
            --gray-800: #1F2420;
            --gray-900: #13261F;

            /* Status Colors (Soft Tinted Badges with Dot Indicators) */
            --status-diajukan: #D29A1E;
            --status-diajukan-bg: #FBF0D6;
            --status-diajukan-fg: #7A4E0E;
            --status-diajukan-dot: #D29A1E;

            --status-diverifikasi: #7C3AED;
            --status-diverifikasi-bg: #EDE9FE;
            --status-diverifikasi-fg: #5B21B6;
            --status-diverifikasi-dot: #7C3AED;

            --status-diproses: #3B7DD8;
            --status-diproses-bg: #E1ECF8;
            --status-diproses-fg: #1B4C82;
            --status-diproses-dot: #3B7DD8;

            --status-selesai: #1E9E63;
            --status-selesai-bg: #DDEFE4;
            --status-selesai-fg: #0A5E3D;
            --status-selesai-dot: #1E9E63;

            --status-ditolak: #D8463B;
            --status-ditolak-bg: #FBE2DF;
            --status-ditolak-fg: #972A22;
            --status-ditolak-dot: #D8463B;

            /* SLA Colors */
            --sla-normal: #1E9E63;
            --sla-near: #D29A1E;
            --sla-overdue: #D8463B;

            /* Shadows */
            --shadow-sm: 0 1px 2px rgba(14, 66, 62, 0.04);
            --shadow-md: 0 4px 12px -2px rgba(14, 66, 62, 0.08), 0 2px 4px -2px rgba(14, 66, 62, 0.04);
            --shadow-lg: 0 10px 24px -4px rgba(14, 66, 62, 0.10);
            --shadow-card: 0 1px 2px rgba(14, 66, 62, 0.05), 0 10px 30px -12px rgba(14, 66, 62, 0.14);
            --shadow-pop: 0 20px 50px -20px rgba(14, 66, 62, 0.28);
            --shadow-primary: 0 10px 24px -10px rgba(10, 94, 61, 0.45);
            --shadow-cta: 0 12px 28px -12px rgba(140, 96, 59, 0.55);

            /* Borders */
            --radius-sm: 6px;
            --radius-md: 12px;
            --radius-lg: 18px;
            --radius-xl: 20px;
            --radius-panel: 24px;
            --radius-full: 9999px;

            /* Transitions */
            --transition: all 0.15s ease;
            --transition-slow: all 0.25s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--canvas);
            background-image:
                radial-gradient(60rem 40rem at -10% -15%, rgba(10, 94, 61, 0.12), transparent 60%),
                radial-gradient(50rem 36rem at 110% 20%, rgba(187, 143, 50, 0.14), transparent 60%);
            background-attachment: fixed;
            color: var(--ink);
            font-size: 15px;
            font-weight: 400;
            line-height: 1.55;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            min-height: 100vh;
        }

        a { text-decoration: none; color: inherit; }

        /* =============================================
           LAYOUT
           ============================================= */

        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar — Clean Light Panel with Academic Identity */
        .sidebar {
            width: 260px;
            background: var(--surface);
            border-right: 1px solid var(--line);
            color: var(--ink);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
            transition: var(--transition-slow);
            overflow-y: auto;
            box-shadow: 2px 0 16px rgba(14, 66, 62, 0.04);
        }

        .sidebar-brand {
            padding: 18px 20px 16px;
            border-bottom: 1px solid var(--line);
        }

        .sidebar-brand-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }

        .sidebar-brand-logo {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(145deg, #071A0E 0%, #11361E 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(10, 94, 61, 0.22);
            padding: 5px;
            border: 1px solid rgba(212, 168, 67, 0.25);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .sidebar-brand-wrap:hover .sidebar-brand-logo {
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(10, 94, 61, 0.32);
        }

        .sidebar-brand-logo svg {
            width: 100%;
            height: 100%;
        }

        .sidebar-brand-text h1 {
            font-size: 20px;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: 0.04em;
            margin: 0;
            line-height: 1.1;
        }

        .sidebar-brand-text p {
            font-size: 10px;
            font-weight: 700;
            color: var(--muted);
            margin: 3px 0 0;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .sidebar-nav {
            flex: 1;
            padding: 18px 14px;
        }

        .nav-section {
            margin-bottom: 22px;
        }

        .nav-section-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--gray-400);
            padding: 0 12px;
            margin-bottom: 6px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 44px;
            padding: 10px 14px;
            border-radius: var(--radius-md);
            color: var(--muted);
            font-size: 14px;
            font-weight: 600;
            transition: var(--transition);
            margin-bottom: 3px;
            position: relative;
        }

        .nav-link:hover {
            background: var(--sunken);
            color: var(--ink);
        }

        .nav-link.active {
            background: var(--primary-soft);
            color: var(--primary);
            font-weight: 700;
        }

        /* Gold indicator on the left for active menu */
        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 8px;
            bottom: 8px;
            width: 4px;
            border-radius: 0 4px 4px 0;
            background: var(--accent);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 16px;
            color: inherit;
        }

        .nav-link.active i {
            color: var(--primary);
        }

        .nav-link .badge {
            margin-left: auto;
            background: var(--sunken);
            color: var(--ink);
            font-size: 12px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: var(--radius-full);
            min-width: 22px;
            text-align: center;
        }

        /* Sidebar User Info */
        .sidebar-user {
            padding: 14px 16px;
            border-top: 1px solid var(--line);
            background: var(--surface-alt);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-user-avatar {
            width: 38px;
            height: 38px;
            border-radius: var(--radius-full);
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            color: #FFFFFF;
            flex-shrink: 0;
            border: 2px solid var(--accent);
            box-shadow: 0 2px 6px rgba(10, 94, 61, 0.2);
        }

        .sidebar-user-info {
            flex: 1;
            min-width: 0;
        }

        .sidebar-user-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--ink);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-role {
            font-size: 12px;
            font-weight: 500;
            color: var(--muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user button {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted);
            padding: 6px;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .sidebar-user button:hover {
            color: var(--status-ditolak);
            background: var(--status-ditolak-bg);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        /* Topbar — Solid Opaque Header that never collides with scrolling content */
        .topbar {
            height: 64px;
            background: var(--surface);
            border-bottom: 1px solid var(--line);
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 1px 3px rgba(14, 66, 62, 0.05);
        }

        .topbar-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--ink);
            letter-spacing: -0.015em;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
        }

        /* =============================================
           TOPBAR NOTIFICATION BELL & DROPDOWN
           ============================================= */
        .notif-dropdown-wrapper {
            position: relative;
            display: inline-block;
        }

        .notif-btn {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--surface);
            border: 1.5px solid rgba(212, 168, 67, 0.4);
            color: #D4A843;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(212, 168, 67, 0.12);
        }

        .notif-btn:hover {
            background: #FDF9EE;
            color: #B58421;
            border-color: #D4A843;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(212, 168, 67, 0.28);
        }

        .notif-badge {
            position: absolute;
            top: -3px;
            right: -3px;
            background: linear-gradient(135deg, #E5B842 0%, #B8860B 100%);
            color: #FFFFFF;
            font-size: 10px;
            font-weight: 800;
            min-width: 18px;
            height: 18px;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            border: 2px solid var(--surface);
            box-shadow: 0 2px 6px rgba(184, 134, 11, 0.45);
            animation: notifPulse 2.5s infinite;
        }

        @keyframes notifPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); box-shadow: 0 0 10px rgba(212, 168, 67, 0.6); }
        }

        .notif-dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 350px;
            max-width: 90vw;
            background: var(--surface);
            border-radius: 16px;
            border: 1px solid var(--line);
            box-shadow: 0 15px 35px rgba(14, 66, 62, 0.14), 0 5px 15px rgba(0,0,0,0.06);
            z-index: 1050;
            overflow: hidden;
            animation: notifSlideDown 0.2s ease-out;
        }

        @keyframes notifSlideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .notif-dropdown-menu.show {
            display: block;
        }

        .notif-header {
            padding: 14px 18px;
            border-bottom: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--surface-alt);
        }

        .notif-pill {
            background: #FEF3C7;
            color: #92400E;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 9999px;
            border: 1px solid rgba(212, 168, 67, 0.35);
        }

        .notif-body {
            max-height: 320px;
            overflow-y: auto;
        }

        .notif-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 18px;
            text-decoration: none;
            border-bottom: 1px solid var(--line-subtle);
            transition: background 0.15s ease;
        }

        .notif-item:hover {
            background: var(--gray-50);
        }

        .notif-item.unread {
            background: rgba(212, 168, 67, 0.05);
        }

        .notif-item-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .notif-item-text {
            flex: 1;
            min-width: 0;
        }

        .notif-item-title {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1.3;
            margin-bottom: 2px;
        }

        .notif-item-desc {
            font-size: 12px;
            color: var(--gray-600);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.3;
        }

        .notif-item-time {
            font-size: 11px;
            color: var(--gray-400);
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .notif-empty {
            padding: 32px 20px;
            text-align: center;
        }

        .notif-footer {
            padding: 11px 18px;
            text-align: center;
            background: var(--surface-alt);
            border-top: 1px solid var(--line);
        }

        .notif-footer-link {
            font-size: 12px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: gap 0.15s ease;
        }

        .notif-footer-link:hover {
            color: var(--primary-hover);
            gap: 9px;
        }

        .content-area {
            flex: 1;
            padding: 24px 32px 48px;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
        }

        /* =============================================
           COMPONENTS
           ============================================= */

        /* Dashboard Hero Greeting Banner */
        .dashboard-hero {
            position: relative;
            overflow: hidden;
            margin-bottom: 24px;
            padding: 24px 28px;
            border-radius: 20px;
            background: linear-gradient(135deg, #0D7048 0%, #0A5E3D 55%, #0E423E 100%);
            color: #FFFFFF;
            box-shadow: var(--shadow-primary);
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        @media (min-width: 768px) {
            .dashboard-hero {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                padding: 28px 32px;
            }
        }

        .dashboard-hero .hero-content {
            flex: 1;
            min-width: 0;
            position: relative;
            z-index: 1;
        }

        .dashboard-hero .hero-date {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 6px;
        }

        .dashboard-hero .hero-title {
            font-size: 24px;
            font-weight: 700;
            color: #FFFFFF;
            line-height: 1.25;
            letter-spacing: -0.015em;
        }

        .dashboard-hero .hero-title .highlight-name {
            color: var(--cream);
        }

        .dashboard-hero .hero-desc {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.90);
            margin-top: 6px;
            line-height: 1.5;
        }

        .dashboard-hero .hero-badges {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }

        .dashboard-hero .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: var(--radius-full);
            background: rgba(255, 255, 255, 0.16);
            color: #FFFFFF;
            font-size: 12px;
            font-weight: 600;
            backdrop-filter: blur(4px);
        }

        .dashboard-hero .hero-action {
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }

        .dashboard-hero .hero-stars {
            position: absolute;
            top: 14px;
            right: 28px;
            display: flex;
            gap: 12px;
            color: var(--accent-light);
            opacity: 0.25;
            pointer-events: none;
            font-size: 16px;
        }

        .dashboard-hero .hero-watermark {
            position: absolute;
            right: 20px;
            bottom: -20px;
            width: 175px;
            height: 175px;
            opacity: 0.14;
            pointer-events: none;
            z-index: 0;
        }

        .dashboard-hero .hero-watermark svg {
            width: 100%;
            height: 100%;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            padding: 20px;
            box-shadow: var(--shadow-card);
            border: 1px solid var(--line);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--stat-color, var(--primary));
        }

        .stat-card:hover {
            box-shadow: 0 4px 14px rgba(14, 66, 62, 0.08), 0 14px 32px -10px rgba(14, 66, 62, 0.16);
            transform: translateY(-2px);
        }

        .stat-card .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            margin-bottom: 12px;
        }

        .stat-card .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.15;
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum";
        }

        .stat-card .stat-label {
            font-size: 13px;
            color: var(--muted);
            margin-top: 4px;
            font-weight: 600;
        }

        /* Cards */
        .card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            border: 1px solid var(--line);
            overflow: hidden;
        }

        .card-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--ink);
        }

        .card-body {
            padding: 24px;
        }

        /* Tables */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background: var(--sunken);
            padding: 12px 18px;
            font-size: 12px;
            font-weight: 700;
            color: var(--muted);
            text-align: left;
            border-bottom: 1px solid var(--line);
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        tbody td {
            padding: 14px 18px;
            font-size: 14px;
            border-bottom: 1px solid var(--line-subtle);
            color: var(--ink);
        }

        tbody tr:hover {
            background: var(--surface-alt);
        }

        .ticket-num {
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum";
            font-weight: 700;
            color: var(--accent-ink);
        }

        /* Status Badge (Refined with Dot Indicator) */
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: var(--radius-full);
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-status::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
        }

        .badge-status.diajukan {
            background: var(--status-diajukan-bg);
            color: var(--status-diajukan-fg);
        }
        .badge-status.diajukan::before { background: var(--status-diajukan-dot); }

        .badge-status.diverifikasi {
            background: var(--status-diverifikasi-bg);
            color: var(--status-diverifikasi-fg);
        }
        .badge-status.diverifikasi::before { background: var(--status-diverifikasi-dot); }

        .badge-status.diproses {
            background: var(--status-diproses-bg);
            color: var(--status-diproses-fg);
        }
        .badge-status.diproses::before { background: var(--status-diproses-dot); }

        .badge-status.selesai {
            background: var(--status-selesai-bg);
            color: var(--status-selesai-fg);
        }
        .badge-status.selesai::before { background: var(--status-selesai-dot); }

        .badge-status.ditolak {
            background: var(--status-ditolak-bg);
            color: var(--status-ditolak-fg);
        }
        .badge-status.ditolak::before { background: var(--status-ditolak-dot); }

        /* SLA Badge */
        .badge-sla {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: var(--radius-full);
            font-size: 12px;
            font-weight: 700;
        }

        .badge-sla.normal { background: var(--status-selesai-bg); color: var(--status-selesai-fg); }
        .badge-sla.near_deadline { background: var(--status-diajukan-bg); color: var(--status-diajukan-fg); }
        .badge-sla.overdue { background: var(--status-ditolak-bg); color: var(--status-ditolak-fg); }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            font-family: inherit;
            white-space: nowrap;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0D7048 0%, #0A5E3D 55%, #0E423E 100%);
            color: #FFFFFF;
            box-shadow: 0 4px 14px rgba(10, 94, 61, 0.35);
        }

        .btn-primary:hover {
            box-shadow: 0 6px 18px rgba(10, 94, 61, 0.45);
            transform: translateY(-1px);
            color: #FFFFFF;
        }

        /* Gold CTA Button (from Reference) */
        .btn-cta, .btn-gold {
            background: linear-gradient(135deg, #ECD18E 0%, #D4AE5A 50%, #BB8F32 100%);
            color: #1A1305;
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(187, 143, 50, 0.35);
        }

        .btn-cta:hover, .btn-gold:hover {
            box-shadow: 0 6px 18px rgba(187, 143, 50, 0.45);
            transform: translateY(-1px);
            color: #1A1305;
        }

        .btn-secondary {
            background: var(--sunken);
            color: var(--ink);
            border: 1px solid var(--line);
        }

        .btn-secondary:hover {
            background: #EAE6DB;
            color: var(--ink);
        }

        .btn-outline {
            background: transparent;
            border: 1.5px solid var(--line);
            color: var(--ink);
        }

        .btn-outline:hover {
            background: var(--sunken);
            border-color: var(--accent);
            color: var(--ink);
        }

        .btn-success {
            background: linear-gradient(135deg, #1E9E63, #0A5E3D);
            color: white;
            box-shadow: 0 4px 12px rgba(30, 158, 99, 0.3);
        }

        .btn-warning {
            background: linear-gradient(135deg, #D4AE5A, #BB8F32);
            color: #1A1305;
            font-weight: 700;
        }

        .btn-danger {
            background: linear-gradient(135deg, #E53E3E, #972A22);
            color: white;
            box-shadow: 0 4px 12px rgba(229, 62, 62, 0.3);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 8px;
        }

        .btn-link {
            background: none;
            border: none;
            color: var(--primary);
            padding: 0;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-link:hover {
            text-decoration: underline;
        }

        .btn-block {
            width: 100%;
        }

        /* Back Link */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--primary) !important;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
        }

        .back-link i {
            font-size: 13px;
            color: var(--primary) !important;
            transition: transform 0.15s ease;
        }

        .back-link:hover {
            color: var(--primary-dark) !important;
            text-decoration: none;
        }

        .back-link:hover i {
            transform: translateX(-3px);
            color: var(--primary-dark) !important;
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 6px;
        }

        .form-label .required {
            color: var(--status-ditolak);
        }

        .form-control {
            width: 100%;
            padding: 11px 16px;
            border: 1.5px solid var(--line);
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
            color: var(--ink);
            background: var(--surface);
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(187, 143, 50, 0.18);
        }

        .form-control.is-invalid {
            border-color: var(--status-ditolak);
        }

        .invalid-feedback {
            color: var(--status-ditolak);
            font-size: 12px;
            font-weight: 500;
            margin-top: 4px;
        }

        select.form-control,
        .form-select,
        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-color: var(--surface);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%230A5E3D' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 16px 16px;
            padding-right: 42px;
            cursor: pointer;
            transition: var(--transition);
        }

        select.form-control:hover:not(:disabled),
        .form-select:hover:not(:disabled),
        select:hover:not(:disabled) {
            border-color: var(--primary);
            background-color: #FAFDFB;
        }

        select.form-control:focus,
        .form-select:focus,
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(10, 94, 61, 0.12);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23D4A843' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
        }

        select:disabled,
        select.form-control:disabled,
        .form-select:disabled {
            background-color: var(--gray-100);
            color: var(--gray-400);
            cursor: not-allowed;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239CA3AF' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
        }

        select option {
            padding: 10px 14px;
            background: #ffffff;
            color: var(--ink);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        /* Alerts */
        .alert {
            padding: 14px 20px;
            border-radius: 14px;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid transparent;
        }

        .alert-success {
            background: var(--status-selesai-bg);
            color: var(--status-selesai-fg);
            border-color: rgba(30, 158, 99, 0.3);
        }

        .alert-danger {
            background: var(--status-ditolak-bg);
            color: var(--status-ditolak-fg);
            border-color: rgba(216, 70, 59, 0.3);
        }

        .alert-warning {
            background: var(--status-diajukan-bg);
            color: var(--status-diajukan-fg);
            border-color: rgba(210, 154, 30, 0.3);
        }

        .alert-info {
            background: var(--status-diproses-bg);
            color: var(--status-diproses-fg);
            border-color: rgba(59, 125, 216, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--muted);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.4;
            color: var(--accent);
        }

        .empty-state h3 {
            font-size: 18px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 8px;
        }

        .empty-state p {
            font-size: 14px;
        }

        /* =============================================
           MOBILE MENU & BOTTOM NAV
           ============================================= */

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 18px;
            color: var(--ink);
            cursor: pointer;
            padding: 6px;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(19, 38, 31, 0.4);
            backdrop-filter: blur(4px);
            z-index: 99;
        }

        .sidebar-overlay.active {
            display: block;
        }

        .mobile-bottom-nav {
            display: none;
        }

        @media (max-width: 767px) {
            /* Sembunyikan sidebar di mobile */
            .sidebar { display: none !important; }
            .sidebar-overlay { display: none !important; }
            .mobile-menu-btn { display: none !important; }

            /* Konten penuh lebar */
            .main-content { margin-left: 0; }
            .content-area {
                padding: 14px;
                padding-bottom: 84px; /* Ruang untuk bottom nav */
            }

            /* Topbar mobile */
            .topbar {
                padding: 0 16px;
                height: 56px;
                background: rgba(255, 255, 255, 0.95);
                border-bottom: 1px solid var(--line);
            }
            .topbar-title { font-size: 16px; }
            .notif-dropdown-menu { width: calc(100vw - 32px); right: -8px; }

            /* Mobile Bottom Nav */
            .mobile-bottom-nav {
                display: flex;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                z-index: 100;
                background: var(--surface);
                border-top: 1px solid var(--line);
                box-shadow: 0 -4px 20px rgba(14, 66, 62, 0.08);
                border-radius: 20px 20px 0 0;
                padding-bottom: env(safe-area-inset-bottom, 0px);
            }

            .mobile-bottom-nav a,
            .mobile-bottom-nav button {
                flex: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 3px;
                min-height: 60px;
                padding: 8px 4px;
                font-size: 10px;
                font-weight: 600;
                color: var(--muted);
                background: none;
                border: none;
                cursor: pointer;
                font-family: inherit;
                text-decoration: none;
                transition: color 0.15s ease;
                position: relative;
            }

            .mobile-bottom-nav a.active,
            .mobile-bottom-nav a:hover {
                color: var(--primary);
                font-weight: 700;
            }

            .mobile-bottom-nav a i,
            .mobile-bottom-nav button i {
                font-size: 20px;
                line-height: 1;
            }

            .mobile-bottom-nav a.active i {
                color: var(--primary);
            }

            /* FAB tombol tengah (Buat Pengaduan) */
            .mobile-bottom-nav .fab-btn {
                position: relative;
            }

            .mobile-bottom-nav .fab-btn .fab-circle {
                width: 52px;
                height: 52px;
                border-radius: 18px;
                background: linear-gradient(135deg, #ECD18E 0%, #D4AE5A 50%, #BB8F32 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 8px 20px -4px rgba(187, 143, 50, 0.5);
                border: 4px solid var(--surface);
                margin-top: -24px;
                margin-bottom: 2px;
            }

            .mobile-bottom-nav .fab-btn .fab-circle i {
                font-size: 22px;
                color: #1A1305;
            }

            .mobile-bottom-nav .fab-btn span {
                font-size: 10px;
                color: var(--ink);
                font-weight: 700;
            }

            .mobile-bottom-nav .nav-spacer {
                flex: 1;
            }

            .mobile-bottom-nav .nav-form {
                flex: 1;
                display: flex;
            }

            .mobile-bottom-nav .nav-form button:hover {
                color: var(--status-ditolak);
            }

            /* Responsive grids */
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .stat-card { padding: 14px; border-radius: 14px; }
            .stat-card .stat-value { font-size: 22px; }
            .stat-card .stat-icon { width: 36px; height: 36px; font-size: 14px; margin-bottom: 8px; border-radius: 10px; }
            .card { border-radius: 14px; }
            .card-header { padding: 14px 16px; }
            .card-body { padding: 16px; }
            thead th { padding: 10px 12px; font-size: 11px; }
            tbody td { padding: 10px 12px; font-size: 13px; }
            .btn { padding: 8px 16px; font-size: 14px; }
            .btn-sm { padding: 5px 10px; font-size: 12px; }
            input, select, textarea, .form-control {
                font-size: 16px !important;
            }
        }

        @media (min-width: 768px) and (max-width: 1023px) {
            .sidebar { width: 240px; }
            .main-content { margin-left: 240px; }
            .topbar { padding: 0 20px; }
            .content-area { padding: 20px; }
        }

        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .content-area { padding: 10px; padding-bottom: 84px; }
            .stat-card { padding: 12px; }
            .stat-card .stat-value { font-size: 18px; }
            .stat-card .stat-label { font-size: 12px; }
        }

        /* =============================================
           ANIMATIONS
           ============================================= */

        .animate-in {
            opacity: 1;
        }

        /* =============================================
           SIPALA PAGINATION SYSTEM
           ============================================= */
        .sipala-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding: 8px 4px;
            font-size: 13px;
            color: var(--gray-600);
        }
        .sipala-pagination .pagination-info {
            font-size: 13px;
            color: var(--gray-600);
        }
        .sipala-pagination .pagination-bold {
            font-weight: 700;
            color: var(--gray-900);
        }
        .sipala-pagination .pagination-links {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            list-style: none;
            margin: 0;
            padding: 0;
            flex-wrap: wrap;
        }
        .sipala-pagination .page-item {
            display: inline-block;
            margin: 0;
        }
        .sipala-pagination .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 10px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: var(--surface);
            color: var(--gray-700);
            text-decoration: none;
            transition: all 0.15s ease;
            cursor: pointer;
        }
        .sipala-pagination .page-link:hover {
            background: var(--primary-soft);
            color: var(--primary);
            border-color: var(--primary);
        }
        .sipala-pagination .page-item.active .page-link {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
            cursor: default;
            box-shadow: 0 2px 4px rgba(10, 94, 61, 0.25);
        }
        .sipala-pagination .page-item.disabled .page-link {
            background: var(--gray-50);
            color: var(--gray-300);
            border-color: var(--line-subtle);
            cursor: not-allowed;
        }
        .sipala-pagination .page-link.dots {
            border: none;
            background: transparent;
            cursor: default;
            color: var(--gray-400);
        }

        /* SVG safety cap to prevent unconstrained icon expansion anywhere */
        svg.w-5, svg.h-5, .w-5, .h-5 {
            width: 18px !important;
            height: 18px !important;
            max-width: 18px !important;
            max-height: 18px !important;
        }
    </style>
    @stack('styles')
</head>
<body>
    @auth
        <div class="app-wrapper">
            @include('layouts.sidebar')

            {{-- Mobile overlay --}}
            <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

            <div class="main-content">
                <div class="topbar">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <button class="mobile-menu-btn" id="mobileMenuBtn" onclick="toggleSidebar()">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h1 class="topbar-title">@yield('page-title', 'Dashboard')</h1>
                    </div>
                    <div class="topbar-actions">
                        @php
                            $authUser = auth()->user();
                            $notifItems = [];
                            $notifCount = 0;
                            $allTicketsUrl = route('dashboard');

                            if ($authUser) {
                                if ($authUser->isEndUser()) {
                                    $allTicketsUrl = route('user.tickets.index');
                                    $userTickets = \App\Models\Ticket::with(['status', 'service'])
                                        ->where('user_id', $authUser->id)
                                        ->latest('updated_at')
                                        ->take(5)
                                        ->get();

                                    foreach ($userTickets as $t) {
                                        $statusName = $t->status->name ?? '';
                                        $isNew = in_array($statusName, [\App\Models\Status::DIPROSES, \App\Models\Status::SELESAI, \App\Models\Status::DIVERIFIKASI]);
                                        
                                        $icon = match($statusName) {
                                            'SELESAI' => 'fas fa-check-circle',
                                            'DIPROSES' => 'fas fa-sync-alt',
                                            'DIVERIFIKASI' => 'fas fa-clipboard-check',
                                            'DITOLAK' => 'fas fa-times-circle',
                                            default => 'fas fa-ticket-alt',
                                        };
                                        $iconBg = match($statusName) {
                                            'SELESAI' => '#DCFCE7',
                                            'DIPROSES' => '#DBEAFE',
                                            'DIVERIFIKASI' => '#EDE9FE',
                                            'DITOLAK' => '#FEE2E2',
                                            default => 'var(--primary-soft)',
                                        };
                                        $iconColor = match($statusName) {
                                            'SELESAI' => '#16A34A',
                                            'DIPROSES' => '#2563EB',
                                            'DIVERIFIKASI' => '#7C3AED',
                                            'DITOLAK' => '#DC2626',
                                            default => 'var(--primary)',
                                        };

                                        $notifItems[] = [
                                            'title' => $t->ticket_number . ' — ' . ($t->status->label ?? 'Status Diperbarui'),
                                            'desc' => $t->subject,
                                            'time' => $t->updated_at ? $t->updated_at->diffForHumans() : 'Baru saja',
                                            'url' => route('user.tickets.show', $t),
                                            'is_new' => $isNew,
                                            'icon' => $icon,
                                            'icon_bg' => $iconBg,
                                            'icon_color' => $iconColor,
                                        ];
                                    }
                                    $notifCount = \App\Models\Ticket::where('user_id', $authUser->id)
                                        ->whereHas('status', fn($q) => $q->whereIn('name', [\App\Models\Status::DIPROSES, \App\Models\Status::SELESAI]))
                                        ->count();

                                } elseif ($authUser->isStaff()) {
                                    $allTicketsUrl = route('staff.tickets.index');
                                    $staffTickets = \App\Models\Ticket::assignedTo($authUser->id)
                                        ->with(['status', 'service', 'user'])
                                        ->latest('updated_at')
                                        ->take(5)
                                        ->get();

                                    foreach ($staffTickets as $t) {
                                        $notifItems[] = [
                                            'title' => 'Tiket: ' . $t->ticket_number,
                                            'desc' => $t->subject . ' (' . ($t->user->nama ?? 'Pemohon') . ')',
                                            'time' => $t->updated_at ? $t->updated_at->diffForHumans() : 'Baru saja',
                                            'url' => route('staff.tickets.show', $t),
                                            'is_new' => $t->status->name === \App\Models\Status::DIPROSES,
                                            'icon' => 'fas fa-user-check',
                                            'icon_bg' => '#DBEAFE',
                                            'icon_color' => '#2563EB',
                                        ];
                                    }
                                    $notifCount = \App\Models\Ticket::assignedTo($authUser->id)
                                        ->whereHas('status', fn($q) => $q->whereIn('name', [\App\Models\Status::DIPROSES, \App\Models\Status::DIVERIFIKASI]))
                                        ->count();

                                } else { // Admin
                                    $allTicketsUrl = route('admin.tickets.index');
                                    $pendingAlumniNotif = \App\Models\AlumniRegistration::pending()->count();
                                    if ($pendingAlumniNotif > 0) {
                                        $notifItems[] = [
                                            'title' => 'Verifikasi Akun Alumni',
                                            'desc' => "{$pendingAlumniNotif} pengajuan pendaftaran alumni menunggu konfirmasi Admin",
                                            'time' => 'Menunggu',
                                            'url' => route('admin.alumni.index'),
                                            'is_new' => true,
                                            'icon' => 'fas fa-user-graduate',
                                            'icon_bg' => '#E2EFE7',
                                            'icon_color' => '#0A5E3D',
                                        ];
                                    }

                                    $adminTickets = \App\Models\Ticket::with(['status', 'service', 'user'])
                                        ->latest('created_at')
                                        ->take(5)
                                        ->get();

                                    foreach ($adminTickets as $t) {
                                        $notifItems[] = [
                                            'title' => 'Tiket Masuk: ' . $t->ticket_number,
                                            'desc' => $t->subject . ' oleh ' . ($t->user->nama ?? 'Pengguna'),
                                            'time' => $t->created_at ? $t->created_at->diffForHumans() : 'Baru saja',
                                            'url' => route('admin.tickets.show', $t),
                                            'is_new' => $t->status->name === \App\Models\Status::DIAJUKAN,
                                            'icon' => 'fas fa-inbox',
                                            'icon_bg' => '#FEF3C7',
                                            'icon_color' => '#D97706',
                                        ];
                                    }
                                    $notifCount = \App\Models\Ticket::whereHas('status', fn($q) => $q->where('name', \App\Models\Status::DIAJUKAN))->count() + $pendingAlumniNotif;
                                }
                            }
                        @endphp

                        <div class="notif-dropdown-wrapper">
                            <button type="button" class="notif-btn" id="notifDropdownBtn" onclick="toggleNotifDropdown(event)" aria-label="Notifikasi Layanan" title="Notifikasi Layanan">
                                <i class="fas fa-bell"></i>
                                @if($notifCount > 0)
                                    <span class="notif-badge">{{ $notifCount }}</span>
                                @endif
                            </button>

                            <!-- Notification Dropdown Menu -->
                            <div class="notif-dropdown-menu" id="notifDropdownMenu">
                                <div class="notif-header">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-bell" style="color: #D4A843;"></i>
                                        <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: var(--ink);">Notifikasi Layanan</h4>
                                    </div>
                                    @if($notifCount > 0)
                                        <span class="notif-pill">{{ $notifCount }} Aktif</span>
                                    @endif
                                </div>

                                <div class="notif-body">
                                    @forelse($notifItems as $item)
                                        <a href="{{ $item['url'] }}" class="notif-item {{ $item['is_new'] ? 'unread' : '' }}">
                                            <div class="notif-item-icon" style="background: {{ $item['icon_bg'] }}; color: {{ $item['icon_color'] }};">
                                                <i class="{{ $item['icon'] }}"></i>
                                            </div>
                                            <div class="notif-item-text">
                                                <div class="notif-item-title">{{ $item['title'] }}</div>
                                                <div class="notif-item-desc">{{ $item['desc'] }}</div>
                                                <div class="notif-item-time"><i class="far fa-clock"></i> {{ $item['time'] }}</div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="notif-empty">
                                            <i class="far fa-bell-slash" style="font-size: 28px; color: var(--gray-400); margin-bottom: 8px; display: block;"></i>
                                            <p style="margin: 0; font-size: 13px; color: var(--muted);">Tidak ada notifikasi saat ini.</p>
                                        </div>
                                    @endforelse
                                </div>

                                <div class="notif-footer">
                                    <a href="{{ $allTicketsUrl }}" class="notif-footer-link">
                                        Lihat Semua Tiket <i class="fas fa-arrow-right" style="font-size: 11px;"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-area">
                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ session('error') }}
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>

            {{-- Mobile Bottom Navigation Bar (< 768px only) --}}
            <nav class="mobile-bottom-nav" aria-label="Menu mobile">

                @if(auth()->user()->isEndUser())
                    {{-- Dashboard --}}
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>Beranda</span>
                    </a>

                    {{-- Tiket Saya --}}
                    <a href="{{ route('user.tickets.index') }}" class="{{ request()->routeIs('user.tickets.index') || request()->routeIs('user.tickets.show') ? 'active' : '' }}">
                        <i class="fas fa-ticket-alt"></i>
                        <span>Tiket Saya</span>
                    </a>

                    {{-- FAB Buat Pengaduan --}}
                    <a href="{{ route('user.tickets.create') }}" class="fab-btn {{ request()->routeIs('user.tickets.create') ? 'active' : '' }}">
                        <span class="fab-circle"><i class="fas fa-plus"></i></span>
                        <span>Buat</span>
                    </a>

                    {{-- Rating Layanan --}}
                    <a href="{{ route('user.ratings.index') }}" class="{{ request()->routeIs('user.ratings.*') ? 'active' : '' }}">
                        <i class="fas fa-star"></i>
                        <span>Rating</span>
                    </a>

                    {{-- Logout --}}
                    <form action="{{ route('logout') }}" method="POST" class="nav-form">
                        @csrf
                        <button type="submit">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Keluar</span>
                        </button>
                    </form>

                @elseif(auth()->user()->isStaff())
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>Beranda</span>
                    </a>
                    <a href="{{ route('staff.tickets.index') }}" class="{{ request()->routeIs('staff.tickets.*') ? 'active' : '' }}">
                        <i class="fas fa-tasks"></i>
                        <span>Tugas</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="nav-form">
                        @csrf
                        <button type="submit">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Keluar</span>
                        </button>
                    </form>

                @elseif(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>Beranda</span>
                    </a>
                    <a href="{{ route('admin.tickets.index') }}" class="{{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
                        <i class="fas fa-inbox"></i>
                        <span>Tiket</span>
                    </a>
                    <a href="{{ route('admin.staff.index') }}" class="{{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                        <i class="fas fa-user-tie"></i>
                        <span>Staf</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="nav-form">
                        @csrf
                        <button type="submit">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Keluar</span>
                        </button>
                    </form>
                @endif

            </nav>

        </div>
    @else
        @yield('content')
    @endauth

    @stack('scripts')
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('active');
        }

        // Topbar Notification Dropdown
        function toggleNotifDropdown(event) {
            event.stopPropagation();
            const menu = document.getElementById('notifDropdownMenu');
            if (menu) {
                menu.classList.toggle('show');
            }
        }
        document.addEventListener('click', function(e) {
            const wrapper = document.querySelector('.notif-dropdown-wrapper');
            const menu = document.getElementById('notifDropdownMenu');
            if (menu && wrapper && !wrapper.contains(e.target)) {
                menu.classList.remove('show');
            }
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const menu = document.getElementById('notifDropdownMenu');
                if (menu) menu.classList.remove('show');
            }
        });
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });

        function syncAppCsrfToken() {
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
                    const meta = document.querySelector('meta[name="csrf-token"]');
                    if (meta) meta.setAttribute('content', data.token);
                }
            })
            .catch(function() {});
        }
        window.addEventListener('focus', syncAppCsrfToken);
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                syncAppCsrfToken();
            }
        });
    </script>
</body>
</html>
