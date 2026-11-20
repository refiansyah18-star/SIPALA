<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Akses Dibatasi (Khusus Staf)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @font-face {
            font-family: 'Plus Jakarta Sans';
            src: url('/fonts/plus-jakarta-sans/plus-jakarta-sans-latin-400-normal.woff2') format('woff2');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Plus Jakarta Sans';
            src: url('/fonts/plus-jakarta-sans/plus-jakarta-sans-latin-500-normal.woff2') format('woff2');
            font-weight: 500;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Plus Jakarta Sans';
            src: url('/fonts/plus-jakarta-sans/plus-jakarta-sans-latin-600-normal.woff2') format('woff2');
            font-weight: 600;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Plus Jakarta Sans';
            src: url('/fonts/plus-jakarta-sans/plus-jakarta-sans-latin-700-normal.woff2') format('woff2');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }

        :root {
            --primary: #1A6B3C;
            --primary-dark: #14472A;
            --primary-light: #23864C;
            --accent: #D4A843;
            --accent-dark: #B8860B;
            --bg-cream: #F7F5EE;
            --surface: #FFFFFF;
            --gray-100: #F3F4F6;
            --gray-200: #E5E7EB;
            --gray-300: #D1D5DB;
            --gray-500: #6B7280;
            --gray-600: #4B5563;
            --gray-700: #374151;
            --gray-800: #1F2937;
            --gray-900: #111827;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 15px;
            background: var(--bg-cream);
            color: var(--gray-800);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 24px 16px;
            -webkit-font-smoothing: antialiased;
        }

        .error-card {
            background: var(--surface);
            border: 1px solid rgba(212, 168, 67, 0.25);
            border-radius: 16px;
            box-shadow: 0 10px 30px -5px rgba(20, 71, 42, 0.08), 0 4px 12px -2px rgba(0, 0, 0, 0.04);
            max-width: 520px;
            width: 100%;
            padding: 36px 32px 32px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Top Hazard Decorative Strip */
        .hazard-strip {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: repeating-linear-gradient(
                -45deg,
                var(--primary-dark),
                var(--primary-dark) 12px,
                var(--accent) 12px,
                var(--accent) 24px
            );
        }

        /* Badge Status */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 9999px;
            background: rgba(212, 168, 67, 0.15);
            border: 1px solid rgba(212, 168, 67, 0.4);
            color: var(--primary-dark);
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 20px;
            letter-spacing: 0.3px;
        }

        .status-pill i {
            color: var(--accent-dark);
        }

        /* Animated Illustration Container */
        .illustration-wrap {
            width: 220px;
            height: 170px;
            margin: 0 auto 20px;
            position: relative;
        }

        /* Keyframes Animations */
        @keyframes gentleBob {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-7px); }
        }

        @keyframes coneBounce {
            0%, 100% { transform: scale(1) translateY(0); }
            50% { transform: scale(1.05) translateY(-4px); }
        }

        @keyframes gearRotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes barrierPulse {
            0%, 100% { opacity: 0.9; }
            50% { opacity: 1; filter: drop-shadow(0 0 6px rgba(212, 168, 67, 0.4)); }
        }

        @keyframes beaconBlink {
            0%, 100% { fill: #F59E0B; filter: drop-shadow(0 0 8px #F59E0B); }
            50% { fill: #D97706; filter: drop-shadow(0 0 2px #D97706); }
        }

        .anim-bob {
            animation: gentleBob 3.5s ease-in-out infinite;
            transform-origin: center;
        }

        .anim-cone {
            animation: coneBounce 2.8s ease-in-out infinite;
            transform-origin: bottom center;
        }

        .anim-cone-delay {
            animation: coneBounce 2.8s ease-in-out 1.4s infinite;
            transform-origin: bottom center;
        }

        .anim-gear {
            animation: gearRotate 12s linear infinite;
            transform-origin: 175px 45px;
        }

        .anim-barrier {
            animation: barrierPulse 2s ease-in-out infinite;
        }

        .anim-beacon {
            animation: beaconBlink 1.2s ease-in-out infinite;
        }

        /* Heading & Typography */
        h1.error-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 8px;
            line-height: 1.3;
        }

        p.error-desc {
            font-size: 14px;
            line-height: 1.6;
            color: var(--gray-600);
            margin-bottom: 24px;
        }

        p.error-desc strong {
            color: var(--primary-dark);
            font-weight: 600;
        }

        /* Action Buttons */
        .action-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: #FFFFFF;
            border: none;
            box-shadow: 0 2px 8px rgba(26, 107, 60, 0.25);
        }

        .btn-primary:hover {
            box-shadow: 0 4px 12px rgba(26, 107, 60, 0.35);
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            color: var(--gray-700);
            border: 1.5px solid var(--gray-300);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(26, 107, 60, 0.04);
        }

        /* Footer Info */
        .error-footer {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid var(--gray-100);
            font-size: 12px;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        @media (max-width: 480px) {
            .error-card {
                padding: 28px 20px 24px;
            }
            .illustration-wrap {
                width: 190px;
                height: 150px;
            }
            h1.error-title {
                font-size: 18px;
            }
            .action-group {
                flex-direction: column;
            }
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    @php
        $isAdminPath = request()->is('admin*');
        $isStaffPath = request()->is('staff*');
        $zoneLabel = $isAdminPath
            ? 'AREA ADMINISTRATOR &bull; AKSES DIBATASI'
            : ($isStaffPath ? 'AREA PENANGANAN STAF &bull; AKSES DIBATASI' : 'ZONA TERBATAS &bull; AKSES DITOLAK');
        $zoneTitle = $isAdminPath
            ? 'Halaman Ini Khusus Administrator'
            : ($isStaffPath ? 'Halaman Ini Khusus Staf Pelaksana' : 'Akses Halaman Dibatasi');
        $zoneRoleText = $isAdminPath
            ? 'hanya dapat diakses oleh akun Administrator'
            : ($isStaffPath ? 'hanya dapat diakses oleh akun Staf Pelaksana' : 'tidak sesuai dengan hak akses akun Anda saat ini');
    @endphp

    <div class="error-card">
        <div class="hazard-strip"></div>

        <div class="status-pill">
            <i class="fas fa-hard-hat"></i>
            <span>{!! $zoneLabel !!}</span>
        </div>

        {{-- SVG Animated Construction Scene --}}
        <div class="illustration-wrap">
            <svg viewBox="0 0 240 180" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 100%; height: 100%;">
                <defs>
                    <!-- Shadow filters -->
                    <filter id="dropGlow" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="#14472A" flood-opacity="0.15"/>
                    </filter>
                    <linearGradient id="workerGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#1A6B3C"/>
                        <stop offset="100%" stop-color="#14472A"/>
                    </linearGradient>
                    <linearGradient id="goldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#E5B95C"/>
                        <stop offset="100%" stop-color="#B8860B"/>
                    </linearGradient>
                    <linearGradient id="coneGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#F59E0B"/>
                        <stop offset="100%" stop-color="#D97706"/>
                    </linearGradient>
                </defs>

                <!-- Background Subtle Gear Animation -->
                <g class="anim-gear" opacity="0.35">
                    <circle cx="175" cy="45" r="22" stroke="#D4A843" stroke-width="3" stroke-dasharray="8 4" fill="none"/>
                    <circle cx="175" cy="45" r="9" fill="#D4A843"/>
                </g>

                <!-- Ground Shadow Base -->
                <ellipse cx="120" cy="165" rx="90" ry="8" fill="#E5E7EB"/>

                <!-- Left Traffic Cone (Animated) -->
                <g class="anim-cone" transform="translate(18, 110)">
                    <ellipse cx="18" cy="48" rx="16" ry="4" fill="#B45309" opacity="0.3"/>
                    <path d="M7 47 L18 10 L29 47 Z" fill="url(#coneGrad)"/>
                    <path d="M12 32 L24 32 L26 39 L10 39 Z" fill="#FFFFFF"/>
                    <path d="M15 19 L21 19 L23 25 L13 25 Z" fill="#FFFFFF"/>
                    <!-- Cone Base Plate -->
                    <rect x="2" y="46" width="32" height="4" rx="2" fill="#B45309"/>
                </g>

                <!-- Right Traffic Cone (Animated Delay) -->
                <g class="anim-cone-delay" transform="translate(185, 114)">
                    <ellipse cx="16" cy="45" rx="14" ry="3.5" fill="#B45309" opacity="0.3"/>
                    <path d="M6 44 L16 10 L26 44 Z" fill="url(#coneGrad)"/>
                    <path d="M10 29 L22 29 L24 36 L8 36 Z" fill="#FFFFFF"/>
                    <path d="M13 18 L19 18 L21 23 L11 23 Z" fill="#FFFFFF"/>
                    <rect x="2" y="43" width="28" height="4" rx="2" fill="#B45309"/>
                </g>

                <!-- Center Construction Worker Character (Animated Bob) -->
                <g class="anim-bob">
                    <!-- Body / Uniform (UIN SSC Green) -->
                    <path d="M92 125 C92 98, 148 98, 148 125 L148 155 L92 155 Z" fill="url(#workerGrad)" filter="url(#dropGlow)"/>
                    
                    <!-- Safety Vest Stripes (Gold / Reflector) -->
                    <path d="M106 106 L106 155" stroke="#D4A843" stroke-width="5" stroke-linecap="round"/>
                    <path d="M134 106 L134 155" stroke="#D4A843" stroke-width="5" stroke-linecap="round"/>
                    <line x1="94" y1="135" x2="146" y2="135" stroke="#D4A843" stroke-width="4"/>

                    <!-- Neck & Head -->
                    <rect x="114" y="90" width="12" height="12" rx="3" fill="#FBCFE8"/>
                    <ellipse cx="120" cy="82" rx="16" ry="17" fill="#FCD34D"/>

                    <!-- Eyes & Smile -->
                    <circle cx="114" cy="80" r="2" fill="#1F2937"/>
                    <circle cx="126" cy="80" r="2" fill="#1F2937"/>
                    <path d="M116 87 Q120 91 124 87" stroke="#1F2937" stroke-width="1.8" stroke-linecap="round" fill="none"/>

                    <!-- Hardhat / Helm Proyek Konstruksi (Gold/Kuning) -->
                    <path d="M102 74 C102 54, 138 54, 138 74 Z" fill="url(#goldGrad)"/>
                    <!-- Hardhat Brim / Rim -->
                    <path d="M98 73 C98 70, 142 70, 142 73 L142 76 L98 76 Z" fill="#B8860B" rx="1"/>
                    <!-- Hardhat Top Ridge -->
                    <path d="M118 55 L122 55 L122 72 L118 72 Z" fill="#996F08"/>

                    <!-- Safety Goggles on Helmet -->
                    <rect x="109" y="66" width="10" height="5" rx="2" fill="#E0F2FE" stroke="#B8860B" stroke-width="1"/>
                    <rect x="121" y="66" width="10" height="5" rx="2" fill="#E0F2FE" stroke="#B8860B" stroke-width="1"/>
                    <line x1="119" y1="68" x2="121" y2="68" stroke="#B8860B" stroke-width="1"/>
                </g>

                <!-- Construction Barrier / Palang Barikade (Depan Karakter) -->
                <g class="anim-barrier" transform="translate(48, 130)">
                    <!-- Left and Right Barrier Legs -->
                    <rect x="6" y="8" width="6" height="30" rx="1" fill="#4B5563"/>
                    <rect x="132" y="8" width="6" height="30" rx="1" fill="#4B5563"/>
                    <polygon points="2,38 16,38 12,35 6,35" fill="#374151"/>
                    <polygon points="128,38 142,38 138,35 132,35" fill="#374151"/>

                    <!-- Warning Beacon Light on top of barrier -->
                    <rect x="133" y="1" width="4" height="6" fill="#374151"/>
                    <circle cx="135" cy="1" r="5" class="anim-beacon" fill="#F59E0B"/>

                    <!-- Striped Barrier Board -->
                    <rect x="0" y="8" width="144" height="18" rx="3" fill="#FFFFFF" stroke="#9CA3AF" stroke-width="1"/>
                    <!-- Hazard Stripes (UIN SSC Green & Gold) -->
                    <g overflow="hidden" clip-path="url(#barrierClip)">
                        <defs>
                            <clipPath id="barrierClip">
                                <rect x="0" y="8" width="144" height="18" rx="3"/>
                            </clipPath>
                        </defs>
                        <polygon points="0,8 14,8 0,26" fill="#14472A"/>
                        <polygon points="14,8 32,8 14,26 0,26" fill="#D4A843"/>
                        <polygon points="32,8 50,8 32,26 14,26" fill="#14472A"/>
                        <polygon points="50,8 68,8 50,26 32,26" fill="#D4A843"/>
                        <polygon points="68,8 86,8 68,26 50,26" fill="#14472A"/>
                        <polygon points="86,8 104,8 86,26 68,26" fill="#D4A843"/>
                        <polygon points="104,8 122,8 104,26 86,26" fill="#14472A"/>
                        <polygon points="122,8 140,8 122,26 104,26" fill="#D4A843"/>
                        <polygon points="140,8 144,8 144,12 140,26 122,26" fill="#14472A"/>
                    </g>

                    <!-- Lock Badge in Center of Barrier -->
                    <circle cx="72" cy="17" r="9" fill="#14472A" stroke="#FFFFFF" stroke-width="1.5"/>
                    <path d="M70 15 L70 14 C70 12.9 70.9 12 72 12 C73.1 12 74 12.9 74 14 L74 15 M69 15 L75 15 C75.5 15 76 15.5 76 16 L76 19 C76 19.5 75.5 20 75 20 L69 20 C68.5 20 68 19.5 68 19 L68 16 C68 15.5 68.5 15 69 15 Z" fill="#D4A843"/>
                </g>

                <!-- Floating Wrench / Kunci Pas Proyek (Top Left) -->
                <g transform="translate(42, 40) rotate(-25)">
                    <path d="M0 6 C0 2.5 3 0 6 0 C8 0 9.8 1.2 10.8 3 L8 5.8 C7.5 5.2 6.8 5 6 5 C4.9 5 4 5.9 4 7 C4 7.8 4.2 8.5 4.8 9 L2 11.8 C0.8 10.5 0 8.5 0 6 Z M6 9 L20 23 L18 25 L4 11 Z" fill="#9CA3AF"/>
                </g>
            </svg>
        </div>

        <h1 class="error-title">{{ $zoneTitle }}</h1>
        
        <p class="error-desc">
            Mohon maaf, halaman yang Anda tuju berada di zona terbatas dan <strong>{{ $zoneRoleText }}</strong>. Silakan kembali ke dashboard utama sesuai peran akun Anda.
        </p>

        <div class="action-group">
            <a href="{{ url('/dashboard') }}" class="btn btn-primary">
                <i class="fas fa-home"></i> Kembali ke Dashboard Saya
            </a>
        </div>

        <div class="error-footer">
            <i class="fas fa-shield-alt" style="color: var(--primary);"></i>
            <span>SIPALA &bull; Sistem Pengaduan dan Layanan Akademik</span>
        </div>
    </div>

</body>
</html>

