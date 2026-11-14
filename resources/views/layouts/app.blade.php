<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TOYACHECK') — Admin Distribusi</title>
    <meta name="description" content="TOYACHECK — Sistem Monitoring dan Digitalisasi Operasional Armada Distribusi">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="icon" type="image/x-icon" href="{{ asset('img/logo.png') }}" />

    <style>
        /* =============================================
           DESIGN SYSTEM — TOYACHECK Admin Distribusi
        ============================================= */
        :root {
            --primary:          #2563EB;
            --primary-light:    #3B82F6;
            --primary-dark:     #1D4ED8;
            --primary-subtle:   #EFF6FF;
            --sidebar-bg:       #091E3A;
            --sidebar-hover:    rgba(255,255,255,0.09);
            --sidebar-pill:     #D5DCE6;
            --sidebar-width:    252px;
            --sidebar-border:   rgba(255,255,255,0.07);
            --topbar-bg:        #FFFFFF;
            --topbar-height:    64px;
            --topbar-border:    #E5E9F0;
            --content-bg:       #F4F7FC;
            --card-bg:          #FFFFFF;
            --card-shadow:      0 1px 4px rgba(15,30,54,0.08), 0 4px 16px rgba(15,30,54,0.04);
            --card-radius:      12px;
            --text-primary:     #0F1E36;
            --text-secondary:   #6B7A99;
            --text-muted:       #9BA8BF;
            --text-sidebar:     rgba(255,255,255,0.75);
            --success:          #16A34A;
            --success-bg:       #DCFCE7;
            --danger:           #DC2626;
            --danger-bg:        #FEE2E2;
            --warning:          #D97706;
            --warning-bg:       #FEF3C7;
            --info:             #0284C7;
            --info-bg:          #E0F2FE;
            --badge-radius:     999px;
            --border:           #E5E9F0;
            --border-light:     #F0F3FA;
            --radius-sm:        6px;
            --radius-md:        10px;
            --radius-lg:        12px;
            --radius-xl:        16px;
            --transition:       0.2s ease;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 14px;
            color: var(--text-primary);
            background: var(--content-bg);
            -webkit-font-smoothing: antialiased;
        }

        /* LAYOUT */
        .app-layout { display: flex; height: 100vh; overflow: hidden; }

        /* SIDEBAR (UNIFIED MEKANIK/ADMIN PILL DESIGN WITH INTER FONT) */
        .sidebar {
            width: var(--sidebar-width);
            min-width: var(--sidebar-width);
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: fixed;
            left: 0; top: 0;
            z-index: 100;
            padding: 28px 18px 22px;
            transition: transform var(--transition);
            border-right: 1px solid var(--sidebar-border);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 8px 26px;
            margin-bottom: 10px;
            border-bottom: 1px solid var(--sidebar-border);
            flex-shrink: 0;
            text-decoration: none;
        }

        .sidebar-logo-text {
            font-family: 'Inter', sans-serif;
            font-size: 22px;
            font-weight: 800;
            color: #FFFFFF;
            letter-spacing: 0.04em;
            display: inline-flex;
            align-items: center;
            line-height: 1;
        }

        .sidebar-logo-o {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            margin: 0 1px;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 8px 2px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .sidebar-nav::-webkit-scrollbar { width: 3px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 2px; }

        .nav-section-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.38);
            padding: 10px 16px 4px;
            margin-top: 6px;
            display: block;
        }

        .nav-section-label:first-child { margin-top: 0; padding-top: 2px; }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 11px 18px;
            border-radius: 999px;
            color: #FFFFFF;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            position: relative;
            transition: all var(--transition);
        }

        .nav-item:hover { background: var(--sidebar-hover); color: #FFFFFF; }
        .nav-item.active {
            background: var(--sidebar-pill);
            color: var(--sidebar-bg);
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(0,0,0,0.16);
        }
        .nav-item.active .nav-icon { color: var(--sidebar-bg); }

        .nav-icon {
            width: 20px;
            text-align: center;
            font-size: 14.5px;
            color: rgba(255,255,255,0.85);
            flex-shrink: 0;
            transition: color var(--transition);
        }

        .nav-item:hover .nav-icon { color: #FFFFFF; }

        .nav-badge {
            margin-left: auto;
            background: var(--danger);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            min-width: 18px;
            height: 18px;
            border-radius: var(--badge-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
        }

        .sidebar-bottom {
            padding-top: 14px;
            margin-top: auto;
            border-top: 1px solid var(--sidebar-border);
            flex-shrink: 0;
        }

        .sidebar-logout-btn {
            display: flex;
            align-items: center;
            gap: 13px;
            width: 100%;
            padding: 11px 18px;
            border-radius: 999px;
            background: transparent;
            border: none;
            color: #FFFFFF;
            font-family: 'Inter', sans-serif;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition);
            text-align: left;
        }

        .sidebar-logout-btn:hover {
            background: rgba(239, 68, 68, 0.18);
            color: #FCA5A5;
        }

        .btn-add-vehicle {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 10px 16px;
            background: linear-gradient(135deg, #7C3AED 0%, #5B21B6 100%);
            color: #FFFFFF;
            border: none;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition);
            text-decoration: none;
            font-family: 'Inter', sans-serif;
        }

        .btn-add-vehicle:hover {
            background: linear-gradient(135deg, #6D28D9 0%, #4C1D95 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(124,58,237,0.4);
            color: #FFFFFF;
        }

        /* MAIN AREA */
        .app-main {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* TOPBAR */
        .topbar {
            height: var(--topbar-height);
            background: var(--topbar-bg);
            border-bottom: 1px solid var(--topbar-border);
            display: flex;
            align-items: center;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 90;
            gap: 12px;
        }

        .topbar-left { flex: 1; display: flex; align-items: center; gap: 12px; }

        .topbar-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 18px;
            padding: 6px;
            border-radius: var(--radius-sm);
            transition: all var(--transition);
        }

        .topbar-toggle:hover { background: var(--border-light); color: var(--text-primary); }

        .topbar-breadcrumb { display: flex; align-items: center; gap: 6px; }
        .breadcrumb-item { font-size: 13px; color: var(--text-muted); }
        .breadcrumb-sep { color: var(--text-muted); font-size: 11px; }
        .breadcrumb-current { font-size: 13px; font-weight: 600; color: var(--text-primary); }

        .topbar-right { display: flex; align-items: center; gap: 8px; }

        .topbar-notif {
            position: relative;
            width: 38px; height: 38px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all var(--transition);
        }

        .topbar-notif:hover { background: var(--border-light); color: var(--text-primary); }

        .notif-dot {
            position: absolute;
            top: 7px; right: 7px;
            width: 8px; height: 8px;
            background: var(--danger);
            border-radius: 50%;
            border: 2px solid var(--topbar-bg);
        }

        /* NOTIFICATION DROPDOWN */
        .notif-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: -60px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: 0 10px 38px rgba(15,30,54,0.16);
            width: 340px;
            max-width: 90vw;
            z-index: 200;
            display: none;
            animation: dropdownIn 0.15s ease;
            overflow: hidden;
        }

        .notif-dropdown.show { display: block; }

        .notif-dropdown-header {
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-light);
            background: #FAFAFC;
        }

        .notif-dropdown-body {
            max-height: 360px;
            overflow-y: auto;
        }

        .notif-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 16px;
            text-decoration: none;
            color: inherit;
            border-bottom: 1px solid #F1F5F9;
            transition: background 0.15s;
            position: relative;
        }

        .notif-item:last-child { border-bottom: none; }
        .notif-item:hover { background: #F8FAFC; }
        .notif-item.unread { background: #F0F7FF; }

        .notif-item-icon {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .notif-item-content { flex: 1; min-width: 0; }
        .notif-item-title { font-size: 12.5px; font-weight: 700; color: var(--text-primary); line-height: 1.3; }
        .notif-item-msg { font-size: 11.5px; color: var(--text-secondary); margin-top: 2px; line-height: 1.4; }
        .notif-item-time { font-size: 10.5px; color: var(--text-muted); margin-top: 4px; display: flex; align-items: center; }

        .notif-unread-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--primary);
            flex-shrink: 0;
            margin-top: 6px;
        }

        .notif-dropdown-footer {
            padding: 9px 16px;
            text-align: center;
            background: #FAFAFC;
            border-top: 1px solid var(--border-light);
        }

        .notif-footer-link {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .notif-footer-link:hover { text-decoration: underline; }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 10px;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--transition);
            border: 1px solid transparent;
            position: relative;
        }

        .topbar-user:hover { background: var(--border-light); border-color: var(--border); }

        .user-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, #7C3AED 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .user-info { line-height: 1.2; }
        .user-name { font-size: 13px; font-weight: 600; color: var(--text-primary); }
        .user-role { font-size: 11px; color: var(--text-secondary); }

        .user-dropdown-icon {
            color: var(--text-muted);
            font-size: 11px;
            transition: transform var(--transition);
        }

        .user-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: 0 8px 32px rgba(15,30,54,0.12);
            min-width: 200px;
            padding: 8px;
            z-index: 200;
            display: none;
            animation: dropdownIn 0.15s ease;
        }

        .user-dropdown.show { display: block; }

        @keyframes dropdownIn {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition);
            border: none;
            background: none;
            width: 100%;
            font-family: 'Inter', sans-serif;
        }

        .dropdown-item:hover { background: var(--border-light); }
        .dropdown-item.text-danger { color: var(--danger); }
        .dropdown-item.text-danger:hover { background: var(--danger-bg); }

        .dropdown-divider { height: 1px; background: var(--border); margin: 6px 0; }

        /* PAGE CONTENT */
        .page-content { flex: 1; padding: 28px; overflow-y: auto; }

        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 24px;
            gap: 16px;
        }

        .page-title { font-size: 20px; font-weight: 700; color: var(--text-primary); line-height: 1.3; }
        .page-subtitle { font-size: 13px; color: var(--text-secondary); margin-top: 3px; }

        /* CARDS */
        .card {
            background: var(--card-bg);
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-light);
            overflow: hidden;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
        }

        .card-title { font-size: 15px; font-weight: 700; color: var(--text-primary); }
        .card-subtitle { font-size: 12px; color: var(--text-secondary); margin-top: 2px; }
        .card-body { padding: 22px; }
        .card-body-p0 { padding: 0; }

        .card-footer {
            padding: 14px 22px;
            border-top: 1px solid var(--border);
            background: var(--border-light);
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        /* STAT CARDS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-light);
            padding: 20px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
            transition: all var(--transition);
        }

        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(15,30,54,0.1); }

        .stat-icon {
            width: 44px; height: 44px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .stat-icon.blue   { background: var(--primary-subtle); color: var(--primary); }
        .stat-icon.green  { background: var(--success-bg); color: var(--success); }
        .stat-icon.red    { background: var(--danger-bg); color: var(--danger); }
        .stat-icon.amber  { background: var(--warning-bg); color: var(--warning); }
        .stat-icon.violet { background: #F3E8FF; color: #7C3AED; }

        .stat-info { flex: 1; min-width: 0; }
        .stat-label { font-size: 12px; color: var(--text-secondary); font-weight: 500; margin-bottom: 4px; }
        .stat-value { font-size: 24px; font-weight: 700; color: var(--text-primary); line-height: 1.1; }
        .stat-change { font-size: 11px; color: var(--text-muted); margin-top: 4px; }

        /* TABLE */
        .table-wrap { overflow-x: auto; }

        table.tc-table { width: 100%; border-collapse: collapse; }

        table.tc-table thead th {
            padding: 12px 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-secondary);
            background: var(--border-light);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
            text-align: left;
        }

        table.tc-table tbody td {
            padding: 13px 16px;
            font-size: 13.5px;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
        }

        table.tc-table tbody tr:last-child td { border-bottom: none; }
        table.tc-table tbody tr { transition: background var(--transition); }
        table.tc-table tbody tr:hover { background: #F8FAFF; }

        /* BADGES */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: var(--badge-radius);
            font-size: 11.5px;
            font-weight: 600;
        }

        .badge-success { background: var(--success-bg); color: var(--success); }
        .badge-danger  { background: var(--danger-bg);  color: var(--danger); }
        .badge-warning { background: var(--warning-bg); color: var(--warning); }
        .badge-info    { background: var(--info-bg);    color: var(--info); }
        .badge-primary { background: var(--primary-subtle); color: var(--primary); }
        .badge-neutral { background: #F1F5F9; color: #64748B; }
        .badge-admin-distribusi { background: #F0FDF4; color: #166534; }
        .badge-mekanik { background: #FEF3C7; color: #92400E; }
        .badge-driver  { background: #F3E8FF; color: #6B21A8; }
        .badge-hse     { background: #FFF1F2; color: #BE123C; }
        .badge-admin   { background: #EFF6FF; color: #1D4ED8; }

        /* BUTTONS */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: var(--radius-md);
            font-size: 13.5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all var(--transition);
            text-decoration: none;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
        }

        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37,99,235,0.35); color: #fff; }

        .btn-secondary { background: #F1F5F9; color: var(--text-primary); }
        .btn-secondary:hover { background: #E2E8F0; color: var(--text-primary); }

        .btn-danger { background: var(--danger); color: #fff; }
        .btn-danger:hover { background: #B91C1C; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(220,38,38,0.3); color: #fff; }

        .btn-warning { background: var(--warning-bg); color: var(--warning); }
        .btn-warning:hover { background: #FDE68A; color: var(--warning); }

        .btn-sm { padding: 6px 12px; font-size: 12px; border-radius: var(--radius-sm); }
        .btn-icon { padding: 7px; width: 32px; height: 32px; justify-content: center; }

        /* FORMS */
        .form-group { margin-bottom: 18px; }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 6px;
        }

        .form-label .required { color: var(--danger); }

        .form-control {
            width: 100%;
            padding: 9px 13px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 13.5px;
            color: var(--text-primary);
            background: #FFFFFF;
            font-family: 'Inter', sans-serif;
            transition: border-color var(--transition), box-shadow var(--transition);
            outline: none;
        }

        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.12); }
        .form-control::placeholder { color: var(--text-muted); }
        .form-control.is-invalid { border-color: var(--danger); }

        .invalid-feedback {
            font-size: 12px;
            color: var(--danger);
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* MODALS */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15,30,54,0.45);
            backdrop-filter: blur(4px);
            z-index: 300;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.25s ease;
        }

        .modal-overlay.show { opacity: 1; visibility: visible; }

        .modal-box {
            background: var(--card-bg);
            border-radius: var(--radius-xl);
            box-shadow: 0 20px 60px rgba(15,30,54,0.2);
            width: 100%;
            max-width: 520px;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transform: translateY(20px) scale(0.97);
            transition: transform 0.25s ease;
        }

        .modal-box > form {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            max-height: 90vh;
            overflow: hidden;
        }

        .modal-overlay.show .modal-box { transform: translateY(0) scale(1); }
        .modal-box.modal-lg { max-width: 680px; }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

        .modal-title { font-size: 16px; font-weight: 700; color: var(--text-primary); }

        .modal-close {
            width: 32px; height: 32px;
            border-radius: var(--radius-sm);
            border: none;
            background: var(--border-light);
            color: var(--text-secondary);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: all var(--transition);
        }

        .modal-close:hover { background: var(--border); color: var(--text-primary); }

        .modal-body { padding: 24px; overflow-y: auto; flex: 1; }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-shrink: 0;
            background: var(--border-light);
        }

        /* TOASTS */
        .toast-container {
            position: fixed;
            top: 20px; right: 20px;
            z-index: 500;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        .toast {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            box-shadow: 0 8px 24px rgba(15,30,54,0.15);
            padding: 14px 18px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            min-width: 300px;
            max-width: 400px;
            border-left: 4px solid;
            pointer-events: auto;
            animation: toastIn 0.3s ease;
        }

        @keyframes toastIn {
            from { opacity: 0; transform: translateX(100%); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .toast.fade-out { animation: toastOut 0.3s ease forwards; }

        @keyframes toastOut {
            from { opacity: 1; transform: translateX(0); }
            to   { opacity: 0; transform: translateX(100%); }
        }

        .toast-success { border-color: var(--success); }
        .toast-danger  { border-color: var(--danger); }
        .toast-warning { border-color: var(--warning); }
        .toast-info    { border-color: var(--info); }
        .toast-icon    { font-size: 16px; flex-shrink: 0; margin-top: 1px; }
        .toast-success .toast-icon { color: var(--success); }
        .toast-danger  .toast-icon { color: var(--danger); }
        .toast-warning .toast-icon { color: var(--warning); }
        .toast-info    .toast-icon { color: var(--info); }
        .toast-content { flex: 1; }
        .toast-title   { font-size: 13.5px; font-weight: 600; color: var(--text-primary); }
        .toast-message { font-size: 12.5px; color: var(--text-secondary); margin-top: 2px; }

        /* PAGINATION */
        .pagination { display: flex; align-items: center; gap: 4px; }

        .page-item .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 34px; height: 34px;
            padding: 0 10px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            font-size: 13px;
            font-weight: 500;
            color: var(--text-primary);
            text-decoration: none;
            transition: all var(--transition);
            background: var(--card-bg);
        }

        .page-item .page-link:hover { background: var(--primary-subtle); border-color: var(--primary); color: var(--primary); }
        .page-item.active .page-link { background: var(--primary); border-color: var(--primary); color: #fff; }
        .page-item.disabled .page-link { opacity: 0.4; cursor: not-allowed; }

        /* UTILITIES */
        .d-flex { display: flex; }
        .align-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-8  { gap: 8px; }
        .gap-12 { gap: 12px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-muted { color: var(--text-muted); font-size: 13px; }
        .mt-4  { margin-top: 4px; }
        .mt-8  { margin-top: 8px; }
        .mt-16 { margin-top: 16px; }
        .mt-24 { margin-top: 24px; }
        .mb-16 { margin-bottom: 16px; }
        .w-100 { width: 100%; }

        .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 24px; text-align: center; }
        .empty-icon  { width: 64px; height: 64px; background: var(--border-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--text-muted); margin-bottom: 16px; }
        .empty-title { font-size: 15px; font-weight: 600; color: var(--text-primary); margin-bottom: 6px; }
        .empty-text  { font-size: 13px; color: var(--text-secondary); }

        /* RESPONSIVE */
        .sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(15,30,54,0.5); z-index: 99; }

        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .app-main { margin-left: 0; }
            .topbar-toggle { display: flex; }
            .sidebar-backdrop.show { display: block; }
        }

        @media (max-width: 640px) {
            .page-content { padding: 16px; }
            .topbar { padding: 0 16px; }
            .user-info { display: none; }
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 12px; }
        }

        .page-content::-webkit-scrollbar { width: 5px; }
        .page-content::-webkit-scrollbar-track { background: transparent; }
        .page-content::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }
    </style>

    @stack('styles')
</head>
<body>

<div class="app-layout">

    @include('layouts.partials.sidebar')

    {{-- ================= MAIN ================= --}}
    <div class="app-main">

        @include('layouts.partials.topbar')

        <main class="page-content">
            @include('layouts.partials.page-header')

            @yield('content')
        </main>

    </div>

</div>

{{-- Toast Container --}}
<div class="toast-container" id="toastContainer"></div>

{{-- Global Confirm Log Out Modal (Figma Match) --}}
<div class="modal-overlay" id="modalConfirmLogout">
    <div class="modal-box" style="max-width:410px;padding:32px 28px 22px;text-align:center;border-radius:20px">
        <div style="width:52px;height:52px;border-radius:14px;background:#FEE2E2;color:#DC2626;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;font-size:20px">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
        </div>

        <h3 style="font-size:19px;font-weight:800;color:#0F172A;margin-bottom:8px">Confirm Log Out</h3>
        <p style="font-size:12.5px;color:#64748B;line-height:1.55;margin-bottom:24px;padding:0 10px">
            Are you sure you want to log out? You will need to re-authenticate to access the logistics control panel.
        </p>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit"
                    style="width:100%;padding:13px 20px;border-radius:10px;border:none;background:#B91C1C;color:#FFFFFF;font-size:12px;font-weight:800;letter-spacing:0.06em;text-transform:uppercase;cursor:pointer;font-family:'Inter',sans-serif">
                LOG OUT
            </button>
        </form>

        <button type="button"
                onclick="closeModal('modalConfirmLogout')"
                style="width:100%;margin-top:10px;padding:12px 20px;border-radius:10px;border:1.5px solid #E2E8F0;background:#FFFFFF;color:#1E293B;font-size:12px;font-weight:800;letter-spacing:0.06em;text-transform:uppercase;cursor:pointer;font-family:'Inter',sans-serif">
            CANCEL
        </button>

        <div style="margin-top:22px;padding-top:14px;border-top:1px solid #F1F5F9;display:flex;align-items:center;justify-content:center;gap:6px;font-size:9.5px;font-weight:700;letter-spacing:0.08em;color:#94A3B8;text-transform:uppercase">
            <i class="fa-solid fa-shield-halved" style="font-size:11px"></i>
            SECURE SESSION MANAGEMENT
        </div>
    </div>
</div>

{{-- Modals --}}
@yield('modals')

{{-- ================= GLOBAL SCRIPTS ================= --}}
@include('layouts.partials.scripts')

@stack('scripts')

</body>
</html>
