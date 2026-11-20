@props([
    'width' => '100%',
    'height' => '100%',
    'headsetColor' => '#D4A843',
    'capColor' => '#FFFFFF',
    'cutColor' => '#0B2416',
])

<svg viewBox="0 0 240 220" width="{{ $width }}" height="{{ $height }}" fill="none" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
    <!-- HEADSET (Gold #D4A843) -->
    <path d="M 52,90 C 38,106 32,122 30,135" stroke="{{ $headsetColor }}" stroke-width="7" stroke-linecap="round"/>
    <path d="M 188,90 C 202,106 208,122 210,135" stroke="{{ $headsetColor }}" stroke-width="7" stroke-linecap="round"/>

    <!-- Left Earpad -->
    <g transform="rotate(-15, 30, 150)">
        <rect x="18" y="130" width="24" height="40" rx="12" fill="{{ $headsetColor }}"/>
        <rect x="22" y="134" width="16" height="32" rx="8" fill="#F3E2B5" opacity="0.4"/>
    </g>

    <!-- Right Earpad -->
    <g transform="rotate(15, 210, 150)">
        <rect x="198" y="130" width="24" height="40" rx="12" fill="{{ $headsetColor }}"/>
        <rect x="202" y="134" width="16" height="32" rx="8" fill="#F3E2B5" opacity="0.4"/>
    </g>

    <!-- Mic Boom Arm -->
    <path d="M 33,165 C 33,198 70,214 108,206" stroke="{{ $headsetColor }}" stroke-width="6" stroke-linecap="round"/>
    <circle cx="114" cy="204" r="9" fill="{{ $headsetColor }}"/>
    <circle cx="112" cy="202" r="3.5" fill="#F3E2B5" opacity="0.75"/>

    <!-- GRADUATION CAP (MORTARBOARD) -->
    <path d="M 68,80 L 68,104 C 68,118 94,126 120,126 C 146,126 172,118 172,104 L 172,80 Z" fill="{{ $capColor }}"/>
    <!-- Curved Cut Fold -->
    <path d="M 112,106 C 132,106 156,110 172,104 C 160,115 138,118 118,116 C 114,112 112,108 112,106 Z" fill="{{ $cutColor }}"/>

    <!-- Mortarboard Diamond Flat Top -->
    <polygon points="120,18 214,64 120,102 26,64" fill="{{ $capColor }}"/>
    <polygon points="26,64 120,102 120,106 26,68" fill="#E2E8F0"/>
    <polygon points="120,102 214,64 214,68 120,106" fill="#CBD5E1"/>
</svg>

