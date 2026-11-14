<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Masuk — TOYACHECK</title>
    <meta name="description" content="Sistem Informasi & Inspeksi Armada Distribusi TOYACHECK">
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}" />

    {{-- Fonts: Playfair Display untuk Brand Serif & Plus Jakarta Sans untuk Form --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,400;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        html, body {
            width: 100%;
            height: 100%;
            background-color: #FFFFFF;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #111827;
            overflow-x: hidden;
        }

        /* ── Splash Screen Intro Animasi (Teks TOYACHECK + Truk Melintas) ── */
        .splash-screen {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            background: #FFFFFF;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        .splash-screen.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .splash-center {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .splash-logo {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 42px;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #000000;
            user-select: none;
            opacity: 0;
            transform: scale(0.9);
            animation: logoEnter 1.4s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        .splash-logo .italic-o,
        .brand-logo .italic-o {
            font-style: italic;
            font-weight: 400;
        }

        @keyframes logoEnter {
            0% {
                opacity: 0;
                transform: scale(0.85) translateY(8px);
            }
            45% {
                opacity: 1;
                transform: scale(1.02) translateY(0);
            }
            75% {
                opacity: 1;
                transform: scale(1);
            }
            100% {
                opacity: 0.15;
                transform: scale(0.97);
            }
        }

        /* Jalur & Animasi Truk Melintas dari Kiri ke Kanan */
        .truck-drive-track {
            position: absolute;
            bottom: -75px;
            left: 0;
            width: 100%;
            height: 125px;
            pointer-events: none;
        }

        .truck-road-line {
            position: absolute;
            bottom: 6px;
            left: 0;
            width: 100%;
            height: 2.5px;
            background: repeating-linear-gradient(90deg, #94A3B8 0, #94A3B8 24px, transparent 24px, transparent 38px);
            opacity: 0.85;
        }

        .truck-vehicle-actor {
            position: absolute;
            bottom: 8px;
            left: -320px;
            width: 250px;
            height: auto;
            animation: truckDriveThrough 1.85s cubic-bezier(0.38, 0, 0.22, 1) 0.85s forwards;
            filter: drop-shadow(0 10px 18px rgba(0, 0, 0, 0.22));
        }

        @keyframes truckDriveThrough {
            0% {
                transform: translateX(0);
            }
            30% {
                transform: translateX(calc(35vw + 40px));
            }
            70% {
                transform: translateX(calc(70vw + 120px));
            }
            100% {
                transform: translateX(calc(100vw + 380px));
            }
        }

        /* Roda berputar saat jalan */
        .truck-wheel-spin {
            transform-origin: center;
            animation: wheelRotate 0.35s linear infinite;
        }

        @keyframes wheelRotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Asap / Wind lines di belakang truk */
        .truck-speed-wind {
            position: absolute;
            left: -40px;
            top: 25px;
            opacity: 0.6;
        }

        /* ── Main Layout Wrapper (Responsive Desktop & Mobile) ── */
        .login-page-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: #FFFFFF;
        }

        .login-box {
            width: 100%;
            max-width: 440px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin: 0 auto;
            animation: fadeInContent 0.6s ease forwards;
        }

        @keyframes fadeInContent {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Brand Logo Typography ── */
        .brand-logo {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 34px;
            font-weight: 700;
            letter-spacing: 0.03em;
            color: #000000;
            margin-bottom: 24px;
            user-select: none;
            line-height: 1.1;
        }

        /* ── Hero 3 Trucks Illustration (Galon, Granmax Box, Pickup) ── */
        .trucks-hero {
            width: 100%;
            max-width: 360px;
            margin: 0 auto 28px;
            position: relative;
        }

        .trucks-hero svg {
            width: 100%;
            height: auto;
            display: block;
            filter: drop-shadow(0 12px 14px rgba(0, 0, 0, 0.20));
        }

        /* ── Section Title ── */
        .login-heading {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 20px;
            letter-spacing: 0.01em;
        }

        /* ── Form Container ── */
        .login-form {
            width: 100%;
            max-width: 270px;
            display: flex;
            flex-direction: column;
            gap: 13px;
        }

        /* ── Capsule / Pill Input Group ── */
        .pill-group {
            position: relative;
            width: 100%;
        }

        .pill-icon-left {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6B7280;
            font-size: 13.5px;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pill-input {
            width: 100%;
            background: #E2E3E5;
            border: 1.5px solid transparent;
            border-radius: 999px;
            padding: 12px 42px 12px 42px;
            font-size: 13px;
            font-weight: 500;
            color: #111827;
            font-family: inherit;
            outline: none;
            transition: all 0.2s ease;
        }

        .pill-input::placeholder {
            color: #6B7280;
            font-weight: 400;
        }

        .pill-input:focus {
            background: #ECEEF1;
            border-color: #0B2E8A;
            box-shadow: 0 0 0 3px rgba(11, 46, 138, 0.12);
        }

        .pill-input.is-invalid {
            border-color: #DC2626;
            background: #FEE2E2;
        }

        /* Toggle show/hide password */
        .pill-toggle-pwd {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6B7280;
            font-size: 13.5px;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s ease;
        }

        .pill-toggle-pwd:hover {
            color: #111827;
        }

        /* ── Submit Button (Deep Navy Pill) ── */
        .btn-submit {
            margin-top: 10px;
            width: 100%;
            background: #0B2E8A;
            color: #FFFFFF;
            border: none;
            border-radius: 999px;
            padding: 12px 20px;
            font-size: 15px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(11, 46, 138, 0.28);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover {
            background: #082268;
            transform: translateY(-1px);
            box-shadow: 0 8px 22px rgba(11, 46, 138, 0.36);
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        /* ── Alert Error ── */
        .alert-error {
            background: #FEE2E2;
            border: 1px solid #FECACA;
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 12px;
            color: #DC2626;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-align: left;
            width: 100%;
            max-width: 270px;
        }

        /* ── Footer Note (Sesuai Figma) ── */
        .login-footer-note {
            margin-top: 36px;
            font-size: 11px;
            color: #6B7280;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .login-footer-note i {
            font-size: 12px;
        }

        /* ── Media Queries Responsive ── */
        @media (min-width: 768px) {
            .login-page-wrap {
                padding: 48px 24px;
            }

            .brand-logo {
                font-size: 38px;
                margin-bottom: 26px;
            }

            .trucks-hero {
                max-width: 380px;
                margin-bottom: 32px;
            }

            .login-heading {
                font-size: 15px;
                margin-bottom: 22px;
            }

            .login-form,
            .alert-error {
                max-width: 300px;
            }

            .pill-input {
                font-size: 13.5px;
                padding: 13px 44px 13px 44px;
            }

            .btn-submit {
                font-size: 15.5px;
                padding: 13px 20px;
            }

            .login-footer-note {
                margin-top: 44px;
                font-size: 11.5px;
            }
        }

        @media (max-width: 420px) {
            .brand-logo {
                font-size: 29px;
                margin-bottom: 20px;
            }

            .trucks-hero {
                max-width: 310px;
                margin-bottom: 24px;
            }

            .login-form,
            .alert-error {
                max-width: 250px;
            }
        }
    </style>
</head>
<body>

{{-- =========================================================================
     INTRO ANIMASI SPLASH SCREEN (Teks TOYACHECK + Truk Mountoya Melintas)
     ========================================================================= --}}
<div class="splash-screen" id="splashScreen">
    <div class="splash-center">
        {{-- Teks Brand Logo --}}
        <div class="splash-logo">
            T<span class="italic-o">O</span>YACHECK
        </div>

        {{-- Jalur Truk Melintas --}}
        <div class="truck-drive-track">
            <div class="truck-road-line"></div>

            {{-- Vektor Truk Mountoya Mengemudi --}}
            <div class="truck-vehicle-actor">
                <svg viewBox="0 0 160 70" width="160" height="70" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Efek Garis Kecepatan Angin (Speed lines di belakang truk) -->
                    <g opacity="0.75" stroke="#94A3B8" stroke-width="2" stroke-linecap="round">
                        <line x1="-22" y1="20" x2="-2" y2="20" />
                        <line x1="-30" y1="30" x2="-6" y2="30" />
                        <line x1="-18" y1="40" x2="-2" y2="40" />
                    </g>

                    <!-- Bayangan Bawah Truk -->
                    <ellipse cx="78" cy="62" rx="68" ry="5" fill="rgba(15, 23, 42, 0.22)"/>

                    <!-- 1. Box / Kargo Belakang (Biru Mountoya) -->
                    <rect x="10" y="10" width="86" height="42" rx="4" fill="#1D4ED8" stroke="#1E3A8A" stroke-width="1.8"/>
                    <!-- Garis Aksen & Teks MOUNTOYA pada Box -->
                    <rect x="10" y="24" width="86" height="14" fill="#2563EB"/>
                    <text x="53" y="34.5" font-family="'Plus Jakarta Sans', sans-serif" font-size="7.5" font-weight="900" fill="#FFFFFF" text-anchor="middle" letter-spacing="1">MOUNTOYA</text>
                    <line x1="16" y1="44" x2="90" y2="44" stroke="#60A5FA" stroke-width="1" stroke-dasharray="3,3"/>

                    <!-- 2. Kabin Truk (Biru Elegan) -->
                    <path d="M96 22 L116 22 L132 36 L134 52 L96 52 Z" fill="#1E3A8A" stroke="#0F172A" stroke-width="1.8"/>
                    <!-- Kaca Depan Kabin -->
                    <path d="M102 25 L114 25 L126 36 L102 36 Z" fill="#E0F2FE" stroke="#0284C7" stroke-width="1"/>
                    <!-- Lampu Depan Menyala -->
                    <path d="M132 42 L136 44 L136 48 L132 48 Z" fill="#FDE047"/>
                    <!-- Cahaya Lampu Depan (Headlight Beam) -->
                    <polygon points="136,44 158,38 160,56 136,48" fill="url(#beamGrad)" opacity="0.6"/>

                    <!-- Bumper Depan -->
                    <rect x="128" y="49" width="9" height="4" rx="1.5" fill="#334155"/>

                    <!-- 3. Roda Belakang -->
                    <g transform="translate(32, 54)">
                        <circle cx="0" cy="0" r="10" fill="#0F172A"/>
                        <circle cx="0" cy="0" r="5.5" fill="#64748B"/>
                        <!-- Garis Jari-jari Roda Berputar -->
                        <g class="truck-wheel-spin">
                            <line x1="-5" y1="0" x2="5" y2="0" stroke="#CBD5E1" stroke-width="1.6"/>
                            <line x1="0" y1="-5" x2="0" y2="5" stroke="#CBD5E1" stroke-width="1.6"/>
                        </g>
                    </g>

                    <!-- 4. Roda Depan -->
                    <g transform="translate(114, 54)">
                        <circle cx="0" cy="0" r="10" fill="#0F172A"/>
                        <circle cx="0" cy="0" r="5.5" fill="#64748B"/>
                        <!-- Garis Jari-jari Roda Berputar -->
                        <g class="truck-wheel-spin">
                            <line x1="-5" y1="0" x2="5" y2="0" stroke="#CBD5E1" stroke-width="1.6"/>
                            <line x1="0" y1="-5" x2="0" y2="5" stroke="#CBD5E1" stroke-width="1.6"/>
                        </g>
                    </g>

                    <defs>
                        <linearGradient id="beamGrad" x1="0%" y1="50%" x2="100%" y2="50%">
                            <stop offset="0%" stop-color="#FEF08A" stop-opacity="0.8"/>
                            <stop offset="100%" stop-color="#FEF08A" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        </div>
    </div>
</div>

{{-- =========================================================================
     HALAMAN LOGIN RESPONSIF (1 Tampilan untuk Web Desktop & Mobile)
     ========================================================================= --}}
<div class="login-page-wrap">
    <div class="login-box">

        {{-- Brand Title --}}
        <div class="brand-logo">
            T<span class="italic-o">O</span>YACHECK
        </div>

        {{-- Ilustrasi 3 Truk Armada (Biru Galon Mountoya, Box Putih Granmax, Pickup Hitam) --}}
        <div class="trucks-hero">
            <svg viewBox="0 0 380 155">
                <!-- Bayangan Lantai Truk -->
                <ellipse cx="190" cy="138" rx="170" ry="12" fill="rgba(0,0,0,0.22)"/>

                <!-- 1. TRUK KIRI (Biru Pengangkut Galon Mountoya) -->
                <g transform="translate(8, 18)">
                    <!-- Rak Galon Belakang -->
                    <rect x="34" y="8" width="76" height="58" rx="4" fill="#1E3A8A" stroke="#172554" stroke-width="1.5"/>
                    <circle cx="46" cy="20" r="6" fill="#38BDF8"/>
                    <circle cx="60" cy="20" r="6" fill="#38BDF8"/>
                    <circle cx="74" cy="20" r="6" fill="#38BDF8"/>
                    <circle cx="88" cy="20" r="6" fill="#38BDF8"/>
                    <circle cx="46" cy="34" r="6" fill="#0284C7"/>
                    <circle cx="60" cy="34" r="6" fill="#0284C7"/>
                    <circle cx="74" cy="34" r="6" fill="#0284C7"/>
                    <!-- Kabin Biru -->
                    <path d="M12,48 L26,22 L92,22 L98,48 L100,96 L8,96 Z" fill="#1D4ED8" stroke="#1E3A8A" stroke-width="1.5"/>
                    <!-- Kaca Depan -->
                    <polygon points="17,46 28,26 86,26 92,46" fill="#0F172A"/>
                    <text x="54" y="62" font-family="'Plus Jakarta Sans',sans-serif" font-size="6.5" font-weight="800" fill="#FFFFFF" text-anchor="middle">MOUNTOYA</text>
                    <!-- Grille & Lampu -->
                    <rect x="12" y="68" width="16" height="9" rx="2" fill="#F8FAFC"/>
                    <rect x="30" y="70" width="44" height="8" rx="2" fill="#0F172A"/>
                    <rect x="76" y="68" width="16" height="9" rx="2" fill="#F8FAFC"/>
                    <!-- Bumper & Plat Nomor -->
                    <rect x="6" y="82" width="94" height="12" rx="3" fill="#0F172A"/>
                    <rect x="38" y="85" width="28" height="7" rx="1" fill="#1E293B" stroke="#475569" stroke-width="0.8"/>
                    <text x="52" y="90" font-family="monospace" font-size="4.5" fill="#FFFFFF" text-anchor="middle">E 8544 KI</text>
                    <!-- Roda -->
                    <circle cx="24" cy="102" r="13" fill="#0F172A"/>
                    <circle cx="24" cy="102" r="6" fill="#64748B"/>
                    <circle cx="84" cy="102" r="13" fill="#0F172A"/>
                    <circle cx="84" cy="102" r="6" fill="#64748B"/>
                </g>

                <!-- 2. TRUK KANAN (Pickup Box Hitam) -->
                <g transform="translate(242, 20)">
                    <!-- Box Kargo Hitam -->
                    <rect x="14" y="6" width="84" height="68" rx="3" fill="#1E293B" stroke="#0F172A" stroke-width="1.5"/>
                    <!-- Kabin Hitam -->
                    <path d="M26,48 L36,24 L98,24 L112,50 L114,96 L24,96 Z" fill="#111827" stroke="#000000" stroke-width="1.5"/>
                    <polygon points="32,48 40,28 94,28 105,48" fill="#334155"/>
                    <text x="70" y="62" font-family="'Plus Jakarta Sans',sans-serif" font-size="6" font-weight="800" fill="#E2E8F0" text-anchor="middle">MOUNTOYA</text>
                    <!-- Lampu & Grille -->
                    <rect x="28" y="68" width="15" height="9" rx="2" fill="#F8FAFC"/>
                    <rect x="46" y="70" width="44" height="8" rx="2" fill="#000000"/>
                    <rect x="92" y="68" width="15" height="9" rx="2" fill="#F8FAFC"/>
                    <!-- Bumper & Plat Nomor -->
                    <rect x="22" y="82" width="94" height="12" rx="3" fill="#090D16"/>
                    <rect x="55" y="85" width="28" height="7" rx="1" fill="#1E293B"/>
                    <text x="69" y="90" font-family="monospace" font-size="4.5" fill="#FFFFFF" text-anchor="middle">E 8869 KW</text>
                    <!-- Roda -->
                    <circle cx="38" cy="100" r="13" fill="#090D16"/>
                    <circle cx="38" cy="100" r="6" fill="#64748B"/>
                    <circle cx="96" cy="100" r="13" fill="#090D16"/>
                    <circle cx="96" cy="100" r="6" fill="#64748B"/>
                </g>

                <!-- 3. TRUK TENGAH (Granmax Box Putih di Garis Depan) -->
                <g transform="translate(126, 12)">
                    <!-- Box Kargo Putih -->
                    <rect x="18" y="4" width="92" height="72" rx="3" fill="#F1F5F9" stroke="#CBD5E1" stroke-width="1.5"/>
                    <text x="64" y="18" font-family="'Plus Jakarta Sans',sans-serif" font-size="6.5" font-weight="800" fill="#334155" text-anchor="middle">MOUNTOYA</text>
                    <!-- Kabin Putih -->
                    <path d="M14,56 L28,28 L100,28 L114,56 L116,104 L12,104 Z" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="1.5"/>
                    <polygon points="20,54 32,33 96,33 108,54" fill="#1E293B"/>
                    <text x="64" y="68" font-family="'Plus Jakarta Sans',sans-serif" font-size="6" font-weight="800" fill="#1E293B" text-anchor="middle">MOUNTOYA</text>
                    <!-- Grille & Lampu -->
                    <rect x="16" y="74" width="18" height="10" rx="2" fill="#E2E8F0" stroke="#94A3B8"/>
                    <rect x="38" y="76" width="52" height="9" rx="2" fill="#1E293B"/>
                    <rect x="94" y="74" width="18" height="10" rx="2" fill="#E2E8F0" stroke="#94A3B8"/>
                    <!-- Bumper & Plat Nomor -->
                    <rect x="10" y="90" width="108" height="13" rx="4" fill="#1E293B"/>
                    <rect x="48" y="93" width="32" height="8" rx="1.5" fill="#0F172A"/>
                    <text x="64" y="98.5" font-family="monospace" font-size="5" font-weight="700" fill="#FFFFFF" text-anchor="middle">E 8325 KI</text>
                    <!-- Roda -->
                    <circle cx="28" cy="110" r="14" fill="#0F172A"/>
                    <circle cx="28" cy="110" r="7" fill="#94A3B8"/>
                    <circle cx="100" cy="110" r="14" fill="#0F172A"/>
                    <circle cx="100" cy="110" r="7" fill="#94A3B8"/>
                </g>
            </svg>
        </div>

        <div class="login-heading">Masuk ke akun anda</div>

        {{-- Error Alert --}}
        @if(isset($errors) && $errors->any())
            <div class="alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        {{-- Form Login Universal --}}
        <form action="{{ route('login.attempt') }}" method="POST" class="login-form">
            @csrf

            {{-- Input ID Karyawan / Username --}}
            <div class="pill-group">
                <span class="pill-icon-left">
                    <i class="fa-solid fa-user"></i>
                </span>
                <input
                    type="text"
                    name="username"
                    value="{{ old('username') ?? old('identity') }}"
                    class="pill-input {{ isset($errors) && $errors->any() ? 'is-invalid' : '' }}"
                    placeholder="ID Karyawan"
                    required
                    autocomplete="username"
                    autofocus>
            </div>

            {{-- Input Password --}}
            <div class="pill-group">
                <span class="pill-icon-left">
                    <i class="fa-solid fa-key"></i>
                </span>
                <input
                    type="password"
                    id="loginPassword"
                    name="password"
                    class="pill-input"
                    placeholder="Password"
                    required
                    autocomplete="current-password">
                <button type="button" class="pill-toggle-pwd" onclick="togglePasswordVisibility()" title="Tampilkan/Sembunyikan Password">
                    <i class="fa-solid fa-eye" id="toggleIcon"></i>
                </button>
            </div>

            {{-- Tombol Masuk --}}
            <button type="submit" class="btn-submit">
                Masuk
            </button>
        </form>

        {{-- Catatan Kaki Sesuai Figma --}}
        <div class="login-footer-note">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>Harap login sesuai dengan ID masing-masing</span>
        </div>

    </div>
</div>

<script>
    /* ── Intro Animasi Splash Screen (Teks TOYACHECK + Truk Lewat) ── */
    document.addEventListener('DOMContentLoaded', function () {
        var splash = document.getElementById('splashScreen');
        if (!splash) return;

        // Jika ada pesan error validasi, lewati intro agar pengguna bisa langsung perbaiki login
        @if(isset($errors) && $errors->any())
            splash.classList.add('hidden');
        @else
            // Durasi: Teks muncul (0 - 1.0s) -> Truk melintas (0.85s - 2.65s) -> Fade out login (2.65s)
            setTimeout(function () {
                splash.classList.add('hidden');
            }, 2650);
        @endif
    });

    /* ── Toggle Show / Hide Password ── */
    function togglePasswordVisibility() {
        var pwdInp = document.getElementById('loginPassword');
        var icon   = document.getElementById('toggleIcon');
        if (!pwdInp || !icon) return;

        if (pwdInp.type === 'password') {
            pwdInp.type = 'text';
            icon.className = 'fa-solid fa-eye-slash';
        } else {
            pwdInp.type = 'password';
            icon.className = 'fa-solid fa-eye';
        }
    }
</script>

</body>
</html>
