{{-- ================= SIDEBAR UTAMA (UNIFIED ROLE-BASED PILL SIDEBAR) ================= --}}
<aside class="sidebar" id="appSidebar">

    <a href="{{ route('dashboard') }}" class="sidebar-logo">
        <span class="sidebar-logo-text">
            T<span class="sidebar-logo-o">
                <svg viewBox="0 0 36 36" width="21" height="21" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="18" cy="18" rx="14" ry="15" stroke="#FFFFFF" stroke-width="3.2"/>
                    <path d="M9 17.5C13.5 22 18.5 11 27.5 13" stroke="#60A5FA" stroke-width="2.8" stroke-linecap="round"/>
                    <path d="M9.5 22C14.5 25.5 20 15.5 27 17.5" stroke="#93C5FD" stroke-width="2.3" stroke-linecap="round"/>
                </svg>
            </span>YACHECK
        </span>
    </a>

    <nav class="sidebar-nav" id="sidebarNav">

        {{-- ── DASHBOARD UTAMA ── --}}
        <a href="{{ route('dashboard') }}"
           class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           id="nav-dashboard">
            <span class="nav-icon"><i class="fa-solid fa-border-all"></i></span>
            Dashboard
        </a>

        {{-- ==========================================================
             1. MENU KHUSUS ROLE MEKANIK (TERPISAH DARI ADMIN)
        ========================================================== --}}
        @role('mekanik')
        <a href="{{ route('mekanik.inventory') }}"
           class="nav-item {{ request()->routeIs('mekanik.inventory*') ? 'active' : '' }}"
           id="nav-inventory">
            <span class="nav-icon"><i class="fa-solid fa-box-archive"></i></span>
            Inventaris
        </a>

        <a href="{{ route('mekanik.repairs') }}"
           class="nav-item {{ request()->routeIs('mekanik.repairs*') ? 'active' : '' }}"
           id="nav-repairs">
            <span class="nav-icon"><i class="fa-solid fa-wrench"></i></span>
            Log Perbaikan
        </a>

        <a href="{{ route('mekanik.master-data') }}"
           class="nav-item {{ request()->routeIs('mekanik.master-data*') ? 'active' : '' }}"
           id="nav-master-data">
            <span class="nav-icon"><i class="fa-solid fa-database"></i></span>
            Master Data
        </a>

        <a href="{{ route('mekanik.approvals') }}"
           class="nav-item {{ request()->routeIs('mekanik.approvals*') ? 'active' : '' }}"
           id="nav-approvals">
            <span class="nav-icon"><i class="fa-regular fa-circle-check"></i></span>
            Persetujuan
        </a>
        @endrole

        {{-- ==========================================================
             2. MENU KHUSUS ROLE ADMIN DISTRIBUSI (TERPISAH DARI MEKANIK)
        ========================================================== --}}
        @role('admin')
        <span class="nav-section-label">Data Master</span>

        <a href="{{ route('users.index') }}"
           class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}"
           id="nav-users">
            <span class="nav-icon"><i class="fa-solid fa-users"></i></span>
            Pengguna
        </a>

        <a href="{{ route('vehicles.index') }}"
           class="nav-item {{ request()->routeIs('vehicles.*') ? 'active' : '' }}"
           id="nav-armada">
            <span class="nav-icon"><i class="fa-solid fa-truck"></i></span>
            Armada
        </a>

        <a href="{{ route('inspection-items.index') }}"
           class="nav-item {{ request()->routeIs('inspection-items.*') ? 'active' : '' }}"
           id="nav-inspection-items">
            <span class="nav-icon"><i class="fa-solid fa-list-check"></i></span>
            Item Pemeriksaan
        </a>

        <span class="nav-section-label">Operasional</span>

        <a href="{{ route('checklists.index') }}"
           class="nav-item {{ request()->routeIs('checklists.*') ? 'active' : '' }}"
           id="nav-checklists">
            <span class="nav-icon"><i class="fa-solid fa-clipboard-check"></i></span>
            Checklist Armada
        </a>

        <a href="{{ route('riwayat.index') }}"
           class="nav-item {{ request()->routeIs('riwayat.*') ? 'active' : '' }}"
           id="nav-riwayat">
            <span class="nav-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
            Riwayat Checklist
        </a>

        <a href="{{ route('maintenances.index') }}"
           class="nav-item {{ request()->routeIs('maintenances.*') ? 'active' : '' }}"
           id="nav-catatan">
            <span class="nav-icon"><i class="fa-solid fa-screwdriver-wrench"></i></span>
            Catatan Perbaikan
        </a>

        <span class="nav-section-label">Pendukung</span>

        @php
            $pendingRequests = \App\Models\SparepartRequest::where('status', 'pending')->count();
        @endphp
        <a href="{{ route('sparepart-requests.index') }}"
           class="nav-item {{ request()->routeIs('sparepart-requests.*') ? 'active' : '' }}"
           id="nav-permintaan">
            <span class="nav-icon"><i class="fa-solid fa-bell"></i></span>
            Permintaan
            @if($pendingRequests > 0)
                <span class="nav-badge">{{ $pendingRequests }}</span>
            @endif
        </a>

        <a href="{{ route('vendors.index') }}"
           class="nav-item {{ request()->routeIs('vendors.*') ? 'active' : '' }}"
           id="nav-vendor">
            <span class="nav-icon"><i class="fa-solid fa-store"></i></span>
            Vendor
        </a>
        @endrole

        {{-- ==========================================================
             3. MENU KHUSUS ROLE PENGADAAN (FIGMA NODE 917-10746)
        ========================================================== --}}
        @role('pengadaan')
        <a href="{{ route('pengadaan.vendors') }}"
           class="nav-item {{ request()->routeIs('pengadaan.vendors*') ? 'active' : '' }}"
           id="nav-pengadaan-vendors">
            <span class="nav-icon"><i class="fa-solid fa-store"></i></span>
            Vendor
        </a>

        @php
            $pendingProcCount = \App\Models\SparepartRequest::where('status', 'pending')->count();
        @endphp
        <a href="{{ route('pengadaan.requests') }}"
           class="nav-item {{ request()->routeIs('pengadaan.requests*') ? 'active' : '' }}"
           id="nav-pengadaan-requests">
            <span class="nav-icon"><i class="fa-solid fa-box-open"></i></span>
            Permintaan
            @if($pendingProcCount > 0)
                <span class="nav-badge">{{ $pendingProcCount }}</span>
            @endif
        </a>

        <a href="{{ route('pengadaan.reports') }}"
           class="nav-item {{ request()->routeIs('pengadaan.reports*') ? 'active' : '' }}"
           id="nav-pengadaan-reports">
            <span class="nav-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
            Laporan
        </a>

        <a href="{{ route('pengadaan.approvals') }}"
           class="nav-item {{ request()->routeIs('pengadaan.approvals*') ? 'active' : '' }}"
           id="nav-pengadaan-approvals">
            <span class="nav-icon"><i class="fa-regular fa-circle-check"></i></span>
            Persetujuan
        </a>
        @endrole

    </nav>

    <div class="sidebar-bottom">
        <button type="button" class="sidebar-logout-btn" onclick="openModal('modalConfirmLogout')">
            <span class="nav-icon"><i class="fa-solid fa-arrow-right-from-bracket"></i></span>
            Log out
        </button>
    </div>

</aside>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
