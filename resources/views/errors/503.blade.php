<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Sedang Dalam Pemeliharaan — SIPALA</title>
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #FFFFFF;
            color: #1F2937;
            min-height: 100vh;
            min-height: 100dvh;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            text-align: center;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Full-screen HD illustration stage (no box/card) */
        .illustration-stage {
            position: relative;
            width: 100%;
            max-width: 740px;
            aspect-ratio: 1376 / 768;
            margin: 0 auto 8px;
            user-select: none;
        }

        .scene-base {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: contain;
        }

        /* Floating Robot + "FIXING SERVER..." layer (Transparent PNG, no clipping) */
        .scene-robot {
            position: absolute;
            left: 64.8%;
            top: 6.5%;
            width: 22.5%;
            aspect-ratio: 677 / 886;
            pointer-events: none;
            animation: floatBot 2.8s ease-in-out infinite;
            will-change: transform;
        }

        .scene-robot img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: contain;
        }

        @keyframes floatBot {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-14px);
            }
        }

        /* Blinking Server Rack LEDs aligned to HD 1376x768 coordinates */
        .led {
            position: absolute;
            width: 1.05%;
            aspect-ratio: 1 / 1;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }

        .led-green {
            left: 35.28%;
            background: #22C55E;
            box-shadow: 0 0 8px 2px rgba(34, 197, 94, 0.85);
            animation: blinkLed 1.4s ease-in-out infinite;
        }

        .led-yellow {
            left: 37.14%;
            background: #FACC15;
            box-shadow: 0 0 8px 2px rgba(250, 204, 21, 0.85);
            animation: blinkLed 1.9s ease-in-out infinite;
        }

        .led-red {
            left: 39.10%;
            background: #EF4444;
            box-shadow: 0 0 9px 2px rgba(239, 68, 68, 0.9);
            animation: blinkLed 1.1s ease-in-out infinite;
        }

        @keyframes blinkLed {
            0%, 100% { opacity: 0.15; transform: translate(-50%, -50%) scale(0.85); }
            50% { opacity: 1; transform: translate(-50%, -50%) scale(1.15); }
        }

        /* Typography */
        h1 {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 10px;
            letter-spacing: -0.3px;
        }

        p {
            font-size: 15.5px;
            line-height: 1.65;
            color: #4B5563;
            max-width: 520px;
            margin: 0 auto 26px;
        }

        .btn-reload {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 28px;
            border-radius: 10px;
            font-size: 14.5px;
            font-weight: 600;
            font-family: inherit;
            color: #FFFFFF;
            background: #0A5E3D;
            border: none;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.15s ease;
            text-decoration: none;
        }

        .btn-reload:hover {
            background: #07442C;
            transform: translateY(-1px);
        }

        .btn-reload:active {
            transform: translateY(0);
        }

        @media (max-width: 600px) {
            h1 {
                font-size: 22px;
            }
            p {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

    <div class="illustration-stage">
        <!-- High-Resolution Base Scene (1376x768) -->
        <img src="/images/maint-scene.png?v=hd2" alt="Sistem Sedang Dalam Pemeliharaan" class="scene-base">

        <!-- Animated Server Rack LED Lights (6 Server Blades) -->
        <span class="led led-green"  style="top: 17.3%; animation-delay: 0.1s;"></span>
        <span class="led led-yellow" style="top: 17.3%; animation-delay: 0.6s;"></span>
        <span class="led led-red"    style="top: 17.3%; animation-delay: 0.3s;"></span>

        <span class="led led-green"  style="top: 27.6%; animation-delay: 0.5s;"></span>
        <span class="led led-yellow" style="top: 27.6%; animation-delay: 0.2s;"></span>
        <span class="led led-red"    style="top: 27.6%; animation-delay: 0.9s;"></span>

        <span class="led led-green"  style="top: 38.4%; animation-delay: 0.2s;"></span>
        <span class="led led-yellow" style="top: 38.4%; animation-delay: 0.8s;"></span>

        <span class="led led-green"  style="top: 48.6%; animation-delay: 0.7s;"></span>
        <span class="led led-yellow" style="top: 48.6%; animation-delay: 0.4s;"></span>
        <span class="led led-red"    style="top: 48.6%; animation-delay: 0.6s;"></span>

        <span class="led led-green"  style="top: 59.1%; animation-delay: 0.3s;"></span>
        <span class="led led-yellow" style="top: 59.1%; animation-delay: 0.9s;"></span>
        <span class="led led-red"    style="top: 59.1%; animation-delay: 0.2s;"></span>

        <span class="led led-green"  style="top: 69.7%; animation-delay: 0.8s;"></span>
        <span class="led led-yellow" style="top: 69.7%; animation-delay: 0.1s;"></span>
        <span class="led led-red"    style="top: 69.7%; animation-delay: 0.5s;"></span>

        <!-- High-Resolution Floating Helper Robot + "FIXING SERVER..." (Transparent PNG) -->
        <div class="scene-robot">
            <img src="/images/maint-bot.png?v=hd2" alt="Fixing Server Robot">
        </div>
    </div>

    <h1>Sistem Sedang Dalam Pemeliharaan</h1>
    <p>
        Kami sedang melakukan pemeliharaan dan peningkatan performa server SIPALA agar layanan akademik semakin lancar. Silakan coba kembali beberapa saat lagi.
    </p>

    <button type="button" onclick="window.location.reload()" class="btn-reload">
        Muat Ulang Halaman
    </button>

</body>
</html>
