<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'TOYACHECK Mobile')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="{{ asset('img/logo.png') }}" />

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        :root {
            --m-bg-outer: #0B1324;
            --m-bg-shell: #FFFFFF;
            --m-bg-soft: #F5F6F8;
            --m-card-gray: #E2E4E8;
            --m-navy: #0B2E8A;
            --m-navy-dark: #071F60;
            --m-green: #4CAF28;
            --m-green-dark: #3B8C1E;
            --m-red: #DC2626;
            --m-amber: #F59E0B;
            --m-text: #111827;
            --m-text-sub: #4B5563;
            --m-text-muted: #8E95A5;
            --m-border: #E5E7EB;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--m-bg-outer);
            color: var(--m-text);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        /* Phone Shell Container (390px - 420px di desktop, 100% di HP) */
        .mobile-shell {
            width: 100%;
            max-width: 420px;
            min-height: 100vh;
            background: var(--m-bg-shell);
            position: relative;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.55);
        }

        @media (min-width: 500px) {
            body {
                padding: 20px 0;
                align-items: center;
            }
            .mobile-shell {
                min-height: 844px;
                max-height: 92vh;
                border-radius: 36px;
                overflow-y: auto;
                border: 6px solid #1E293B;
            }
            .mobile-shell::-webkit-scrollbar {
                width: 4px;
            }
            .mobile-shell::-webkit-scrollbar-thumb {
                background: rgba(0,0,0,0.18);
                border-radius: 4px;
            }
        }

        /* Top Status / Brand Bar sesuai Figma */
        .m-topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            background: #FFFFFF;
            border-bottom: 1.5px solid #222222;
            padding: 14px 18px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 56px;
        }

        .m-topbar-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: none;
            background: transparent;
            color: #111827;
            font-size: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s;
        }

        .m-topbar-btn:hover {
            background: #F3F4F6;
        }

        /* Logo TOYACHECK serif/italic khas Figma */
        .m-brand-logo {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.03em;
            color: #000000;
            text-decoration: none;
            display: inline-flex;
            align-items: baseline;
        }
        .m-brand-logo .italic-o {
            font-style: italic;
            font-weight: 400;
            margin-right: 1px;
        }

        .m-page-title {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            text-align: center;
            line-height: 1.25;
            flex: 1;
            padding: 0 8px;
        }

        /* Content Area */
        .m-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding-bottom: 80px;
        }

        /* Search Bar Pill sesuai Figma */
        .m-search-wrap {
            padding: 16px 20px 8px;
        }

        .m-search-box {
            position: relative;
            width: 100%;
        }

        .m-search-box i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6B7280;
            font-size: 14px;
            pointer-events: none;
        }

        .m-search-input {
            width: 100%;
            background: #E5E6EB;
            border: none;
            border-radius: 999px;
            padding: 11px 18px 11px 40px;
            font-size: 13px;
            color: #111827;
            font-family: inherit;
            outline: none;
            transition: box-shadow 0.2s, background 0.2s;
        }

        .m-search-input:focus {
            background: #ECEEF2;
            box-shadow: 0 0 0 2px var(--m-navy);
        }

        /* Slide-over Drawer Menu (Hamburger) */
        .m-drawer-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            z-index: 90;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }
        .m-drawer-backdrop.open {
            opacity: 1;
            pointer-events: auto;
        }

        .m-drawer {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            width: 280px;
            max-width: 82vw;
            background: #FFFFFF;
            z-index: 100;
            transform: translateX(100%);
            transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            box-shadow: -8px 0 30px rgba(0,0,0,0.2);
        }
        .m-drawer.open {
            transform: translateX(0);
        }

        .m-drawer-header {
            padding: 24px 20px 18px;
            background: linear-gradient(135deg, #0B2E8A 0%, #0F1E36 100%);
            color: #FFFFFF;
        }

        .m-drawer-user {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 8px;
        }

        .m-drawer-avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.8);
            background: #FFFFFF;
        }

        .m-drawer-nav {
            padding: 12px;
            flex: 1;
            overflow-y: auto;
        }

        .m-drawer-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 12px;
            color: #1F2937;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
            transition: background 0.15s, color 0.15s;
        }

        .m-drawer-link i {
            width: 20px;
            text-align: center;
            color: #6B7280;
            font-size: 15px;
        }

        .m-drawer-link:hover,
        .m-drawer-link.active {
            background: #EFF6FF;
            color: var(--m-navy);
        }

        .m-drawer-link:hover i,
        .m-drawer-link.active i {
            color: var(--m-navy);
        }

        /* Bottom Navigation Bar Mobile */
        .m-bottom-nav {
            position: sticky;
            bottom: 0;
            z-index: 40;
            background: #FFFFFF;
            border-top: 1px solid #E5E7EB;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            padding: 6px 8px 10px;
            margin-top: auto;
            box-shadow: 0 -4px 16px rgba(0,0,0,0.04);
        }

        .m-bottom-nav.cols-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        .m-bottom-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            text-decoration: none;
            color: #9CA3AF;
            font-size: 11px;
            font-weight: 600;
            padding: 6px 4px;
            border-radius: 10px;
            transition: color 0.15s;
        }

        .m-bottom-item i {
            font-size: 17px;
        }

        .m-bottom-item.active {
            color: var(--m-navy);
        }

        /* Toast Alert Mobile */
        .m-toast {
            margin: 12px 18px 0;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 12.5px;
            font-weight: 600;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            line-height: 1.45;
            animation: slideDown 0.3s ease;
        }
        .m-toast.success {
            background: #DCFCE7;
            color: #166534;
            border: 1px solid #86EFAC;
        }
        .m-toast.error {
            background: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FCA5A5;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>

    @stack('styles')
</head>
<body>

<div class="mobile-shell">

    {{-- TOPBAR PERSIS FIGMA: Back (<) di kiri, TOYACHECK di tengah, Hamburger (≡) di kanan --}}
    @hasSection('hide_topbar')
    @else
    <header class="m-topbar">
        @hasSection('back_url')
            <a href="@yield('back_url')" class="m-topbar-btn" title="Kembali">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
        @else
            <span class="m-topbar-btn" style="visibility:hidden">
                <i class="fa-solid fa-chevron-left"></i>
            </span>
        @endif

        @hasSection('header_title')
            <div class="m-page-title">@yield('header_title')</div>
        @else
            <a href="{{ route('driver.index') }}" class="m-brand-logo">
                T<span class="italic-o">O</span>YACHECK
            </a>
        @endif

        @hasSection('right_action')
            @yield('right_action')
        @else
            <button type="button" class="m-topbar-btn" onclick="toggleMobileDrawer(true)" title="Menu">
                <i class="fa-solid fa-bars"></i>
            </button>
        @endif
    </header>
    @endif

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="m-toast success">
            <i class="fa-solid fa-circle-check" style="margin-top:2px;font-size:15px"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="m-toast error">
            <i class="fa-solid fa-circle-exclamation" style="margin-top:2px;font-size:15px"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    {{-- MAIN CONTENT --}}
    <main class="m-content">
        @yield('content')
    </main>

    {{-- BOTTOM NAVIGATION --}}
    @hasSection('hide_bottom_nav')
    @else
    <nav class="m-bottom-nav {{ auth()->check() && auth()->user()->hasRole('hse') ? 'cols-4' : '' }}">
        <a href="{{ route('driver.index') }}" class="m-bottom-item {{ request()->routeIs('driver.index', 'driver.vehicle.*') ? 'active' : '' }}">
            <i class="fa-solid fa-truck-front"></i>
            <span>Kendaraan</span>
        </a>
        @role('hse')
        <a href="{{ route('driver.k3-items') }}" class="m-bottom-item {{ request()->routeIs('driver.k3-items*') ? 'active' : '' }}">
            <i class="fa-solid fa-pen-to-square"></i>
            <span>Edit Data</span>
        </a>
        @endrole
        <a href="{{ route('driver.history') }}" class="m-bottom-item {{ request()->routeIs('driver.history*') ? 'active' : '' }}">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <span>Riwayat</span>
        </a>
        <a href="{{ route('driver.profile') }}" class="m-bottom-item {{ request()->routeIs('driver.profile*') ? 'active' : '' }}">
            <i class="fa-solid fa-user"></i>
            <span>Akun</span>
        </a>
    </nav>
    @endif

</div>

{{-- DRAWER NAVIGATION (Hamburger Menu di kanan atas — sesuai Componen Menu APK di Figma) --}}
@auth
<div class="m-drawer-backdrop" id="mDrawerBackdrop" onclick="toggleMobileDrawer(false)"></div>
<aside class="m-drawer" id="mDrawer">
    <div class="m-drawer-header">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
            <span style="font-family:'Playfair Display',serif;font-weight:700;font-size:18px;letter-spacing:0.04em">
                T<i style="font-weight:400">O</i>YACHECK
            </span>
            <button type="button" onclick="toggleMobileDrawer(false)" style="background:none;border:none;color:#fff;font-size:18px;cursor:pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="m-drawer-user">
            <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="m-drawer-avatar">
            <div style="overflow:hidden">
                <div style="font-weight:700;font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    {{ auth()->user()->name }}
                </div>
                <div style="font-size:11.5px;opacity:0.85;margin-top:2px">
                    ID: {{ auth()->user()->employee_id ?? auth()->user()->username }}
                    &bull; {{ auth()->user()->hasRole('hse') ? 'PETUGAS K3 / HSE' : strtoupper(auth()->user()->roles->first()?->name ?? 'DRIVER') }}
                </div>
            </div>
        </div>
    </div>

    <div class="m-drawer-nav">
        <a href="{{ route('driver.profile') }}" class="m-drawer-link {{ request()->routeIs('driver.profile') ? 'active' : '' }}">
            <i class="fa-regular fa-circle-user"></i>
            Informasi Akun
        </a>
        <a href="{{ route('driver.notifications') }}" class="m-drawer-link {{ request()->routeIs('driver.notifications') ? 'active' : '' }}">
            <i class="fa-regular fa-bell"></i>
            Pemberitahuan
        </a>
        <a href="{{ route('driver.notifications') }}#manual" class="m-drawer-link">
            <i class="fa-solid fa-book-open"></i>
            Petunjuk Manual
        </a>

        {{-- Menu Khusus Role K3 / HSE sesuai Componen Menu APK di Figma (Node 698:5377) --}}
        @role('hse')
        <a href="{{ route('driver.k3-items') }}" class="m-drawer-link {{ request()->routeIs('driver.k3-items*') ? 'active' : '' }}">
            <i class="fa-solid fa-pen"></i>
            Edit Data
        </a>
        @endrole

        <hr style="border:none;border-top:1px solid #E5E7EB;margin:10px 0">

        <a href="{{ route('driver.index') }}" class="m-drawer-link {{ request()->routeIs('driver.index') ? 'active' : '' }}">
            <i class="fa-solid fa-truck"></i>
            Pilih Kendaraan
        </a>
        <a href="{{ route('driver.history') }}" class="m-drawer-link {{ request()->routeIs('driver.history*') ? 'active' : '' }}">
            <i class="fa-solid fa-clock-rotate-left"></i>
            Riwayat {{ auth()->user()->hasRole('hse') ? 'Laporan K3' : 'Inspeksi' }}
        </a>

        @hasanyrole('admin|mekanik')
        <hr style="border:none;border-top:1px solid #E5E7EB;margin:10px 0">
        <a href="{{ route('dashboard') }}" class="m-drawer-link">
            <i class="fa-solid fa-desktop"></i>
            Buka Dashboard Web
        </a>
        @endhasanyrole

        <hr style="border:none;border-top:1px solid #E5E7EB;margin:10px 0">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="m-drawer-link" style="width:100%;border:none;background:none;cursor:pointer;color:#DC2626;text-align:left">
                <i class="fa-solid fa-right-from-bracket" style="color:#DC2626"></i>
                Keluar Akun
            </button>
        </form>
    </div>
</aside>
@endauth

<script>
    function toggleMobileDrawer(open) {
        var drawer = document.getElementById('mDrawer');
        var backdrop = document.getElementById('mDrawerBackdrop');
        if (!drawer || !backdrop) return;
        if (open) {
            drawer.classList.add('open');
            backdrop.classList.add('open');
        } else {
            drawer.classList.remove('open');
            backdrop.classList.remove('open');
        }
    }
</script>

@stack('scripts')
</body>
</html>
