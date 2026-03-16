<!DOCTYPE html>
<html lang="id" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RFID System — @yield('title', 'Dashboard')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* =============================================
           DESIGN TOKENS — LIGHT & DARK THEME
        ============================================= */
        :root,
        [data-theme="dark"] {
            --bg-base: #0f0f1a;
            --bg-sidebar: #131320;
            --bg-card: #1a1a2e;
            --bg-hover: rgba(99, 102, 241, 0.10);
            --bg-active: rgba(99, 102, 241, 0.18);
            --bg-submenu: rgba(0, 0, 0, 0.20);
            --bg-input: #1e1e35;
            --border: rgba(255, 255, 255, 0.08);
            --border-sidebar: rgba(255, 255, 255, 0.06);

            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --text-active: #818cf8;

            --accent: #6366f1;
            --accent-light: #818cf8;
            --accent-glow: rgba(99, 102, 241, 0.3);
            --accent-cyan: #22d3ee;
            --accent-green: #22c55e;
            --accent-red: #ef4444;
            --accent-orange: #f97316;

            --shadow-sidebar: 4px 0 24px rgba(0, 0, 0, 0.4);
            --shadow-card: 0 8px 32px rgba(0, 0, 0, 0.3);
            --sidebar-width: 268px;
            --header-height: 60px;
            --radius: 12px;
            --radius-sm: 8px;

            color-scheme: dark;
        }

        [data-theme="light"] {
            --bg-base: #f8fafc;
            --bg-sidebar: #ffffff;
            --bg-card: #ffffff;
            --bg-hover: rgba(99, 102, 241, 0.07);
            --bg-active: rgba(99, 102, 241, 0.12);
            --bg-submenu: rgba(99, 102, 241, 0.04);
            --bg-input: #f1f5f9;
            --border: rgba(0, 0, 0, 0.08);
            --border-sidebar: rgba(0, 0, 0, 0.07);

            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --text-active: #4f46e5;

            --accent: #4f46e5;
            --accent-light: #6366f1;
            --accent-glow: rgba(79, 70, 229, 0.2);
            --accent-cyan: #0891b2;
            --accent-green: #16a34a;
            --accent-red: #dc2626;
            --accent-orange: #ea580c;

            --shadow-sidebar: 4px 0 16px rgba(0, 0, 0, 0.08);
            --shadow-card: 0 4px 16px rgba(0, 0, 0, 0.08);

            color-scheme: light;
        }

        /* =============================================
           BASE RESET
        ============================================= */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* =============================================
           SIDEBAR
        ============================================= */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-sidebar);
            box-shadow: var(--shadow-sidebar);
            display: flex;
            flex-direction: column;
            z-index: 200;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                background 0.3s ease,
                box-shadow 0.3s ease;
            overflow: hidden;
        }

        .sidebar.collapsed {
            width: 72px;
        }

        /* Brand / Logo */
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 18px;
            height: var(--header-height);
            border-bottom: 1px solid var(--border-sidebar);
            flex-shrink: 0;
            overflow: hidden;
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            min-width: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 17px;
            box-shadow: 0 0 16px var(--accent-glow);
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: opacity 0.2s, width 0.3s;
        }

        .brand-text h1 {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary);
            white-space: nowrap;
        }

        .brand-text span {
            font-size: 11px;
            color: var(--text-muted);
            white-space: nowrap;
        }

        .sidebar.collapsed .brand-text {
            opacity: 0;
            width: 0;
            pointer-events: none;
        }

        /* User Profile Area */
        .sidebar-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            border-bottom: 1px solid var(--border-sidebar);
            flex-shrink: 0;
            overflow: hidden;
            cursor: pointer;
            transition: background 0.2s;
        }

        .sidebar-profile:hover {
            background: var(--bg-hover);
        }

        .profile-avatar {
            width: 36px;
            height: 36px;
            min-width: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-cyan));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            position: relative;
        }

        .profile-avatar .online-dot {
            position: absolute;
            bottom: 1px;
            right: 1px;
            width: 9px;
            height: 9px;
            background: var(--accent-green);
            border-radius: 50%;
            border: 2px solid var(--bg-sidebar);
        }

        .profile-info {
            overflow: hidden;
            transition: opacity 0.2s, width 0.3s;
        }

        .profile-info h4 {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .profile-info span {
            font-size: 11px;
            color: var(--text-muted);
            white-space: nowrap;
        }

        .sidebar.collapsed .profile-info {
            opacity: 0;
            width: 0;
            pointer-events: none;
        }

        /* Navigation Area */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 10px 0;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 2px;
        }

        /* Category Label */
        .nav-category {
            padding: 14px 18px 6px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            transition: opacity 0.2s;
        }

        .sidebar.collapsed .nav-category {
            opacity: 0;
        }

        /* Nav Item */
        .nav-item {
            margin: 1px 8px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 12px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
            white-space: nowrap;
            text-decoration: none;
            color: var(--text-secondary);
            font-size: 13.5px;
            font-weight: 500;
            user-select: none;
        }

        .nav-link:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
        }

        .nav-link.active {
            background: var(--bg-active);
            color: var(--text-active);
            font-weight: 600;
        }

        .nav-link.active .nav-icon {
            color: var(--accent-light);
        }

        /* Active indicator bar */
        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: var(--accent-light);
            border-radius: 0 2px 2px 0;
        }

        .nav-icon {
            width: 32px;
            min-width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: all 0.2s;
            background: transparent;
        }

        .nav-link:hover .nav-icon,
        .nav-link.active .nav-icon {
            background: var(--bg-active);
        }

        .nav-label {
            flex: 1;
            transition: opacity 0.2s;
        }

        .sidebar.collapsed .nav-label {
            opacity: 0;
            pointer-events: none;
        }

        /* Chevron / Arrow */
        .nav-chevron {
            font-size: 11px;
            color: var(--text-muted);
            transition: transform 0.25s ease, opacity 0.2s;
        }

        .sidebar.collapsed .nav-chevron {
            opacity: 0;
        }

        .nav-item.open>.nav-link .nav-chevron {
            transform: rotate(90deg);
        }

        /* Badge */
        .nav-badge {
            font-size: 10px;
            font-weight: 700;
            background: var(--accent);
            color: #fff;
            border-radius: 10px;
            padding: 2px 7px;
            min-width: 20px;
            text-align: center;
            transition: opacity 0.2s;
        }

        .sidebar.collapsed .nav-badge {
            opacity: 0;
        }

        /* Submenu */
        .submenu {
            display: none;
            overflow: hidden;
            background: var(--bg-submenu);
            border-radius: var(--radius-sm);
            margin: 2px 0;
        }

        .nav-item.open>.submenu {
            display: block;
            animation: submenuIn 0.22s ease;
        }

        @keyframes submenuIn {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .submenu .nav-link {
            padding: 8px 12px 8px 52px;
            font-size: 13px;
            margin: 1px 0;
            border-radius: 6px;
        }

        .submenu .nav-link .nav-icon {
            width: 26px;
            min-width: 26px;
            height: 26px;
            font-size: 12px;
            border-radius: 6px;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            padding: 12px 8px;
            border-top: 1px solid var(--border-sidebar);
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        /* Theme Toggle Button */
        .theme-toggle {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 12px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            border: none;
            background: transparent;
            color: var(--text-secondary);
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 500;
            width: 100%;
            text-align: left;
            white-space: nowrap;
            overflow: hidden;
            transition: background 0.2s, color 0.2s;
        }

        .theme-toggle:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
        }

        .theme-toggle .nav-icon {
            width: 32px;
            min-width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            background: transparent;
        }

        .sidebar.collapsed .theme-toggle .toggle-label {
            opacity: 0;
        }

        .toggle-label {
            transition: opacity 0.2s;
        }

        /* Toggle switch pill */
        .toggle-pill {
            margin-left: auto;
            width: 34px;
            min-width: 34px;
            height: 18px;
            background: var(--border);
            border-radius: 9px;
            position: relative;
            transition: background 0.2s;
        }

        .toggle-pill::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 14px;
            height: 14px;
            background: var(--text-muted);
            border-radius: 50%;
            transition: transform 0.2s, background 0.2s;
        }

        [data-theme="light"] .toggle-pill {
            background: var(--accent);
        }

        [data-theme="light"] .toggle-pill::after {
            transform: translateX(16px);
            background: #fff;
        }

        .sidebar.collapsed .toggle-pill {
            opacity: 0;
        }

        /* Collapse Toggle Button */
        .collapse-btn {
            position: fixed;
            top: 14px;
            left: calc(var(--sidebar-width) - 14px);
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--bg-sidebar);
            border: 1px solid var(--border-sidebar);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 300;
            transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                transform 0.3s ease,
                color 0.2s,
                box-shadow 0.2s;
            font-size: 11px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .collapse-btn:hover {
            color: var(--text-primary);
            box-shadow: 0 0 12px var(--accent-glow);
        }

        .sidebar.collapsed~.collapse-btn,
        body.sidebar-collapsed .collapse-btn {
            left: 58px;
            transform: rotate(180deg);
        }

        /* =============================================
           MAIN CONTENT AREA
        ============================================= */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar.collapsed~.main-wrapper {
            margin-left: 72px;
        }

        /* Top Topbar */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 100;
            height: var(--header-height);
            background: var(--bg-sidebar);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            backdrop-filter: blur(12px);
            transition: background 0.3s;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-breadcrumb {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .topbar-breadcrumb .crumb-parent {
            color: var(--text-muted);
            font-weight: 400;
        }

        .topbar-breadcrumb .fa-chevron-right {
            font-size: 10px;
            color: var(--text-muted);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .topbar-btn {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-secondary);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: all 0.2s;
        }

        .topbar-btn:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
        }

        .topbar-status {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 6px 14px;
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.25);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: var(--accent-green);
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--accent-green);
            animation: blink 2s ease-in-out infinite;
        }

        @keyframes blink {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.5);
            }

            50% {
                box-shadow: 0 0 0 5px rgba(34, 197, 94, 0);
            }
        }

        /* =============================================
           PAGE CONTENT
        ============================================= */
        .page-content {
            flex: 1;
            padding: 24px;
            position: relative;
        }

        /* =============================================
           TOOLTIP (for collapsed state)
        ============================================= */
        .nav-link[data-tooltip]:hover::after,
        .theme-toggle[data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            left: calc(100% + 12px);
            top: 50%;
            transform: translateY(-50%);
            background: var(--bg-card);
            border: 1px solid var(--border);
            color: var(--text-primary);
            font-size: 12px;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            white-space: nowrap;
            pointer-events: none;
            z-index: 999;
            box-shadow: var(--shadow-card);
            display: none;
        }

        .sidebar.collapsed .nav-link[data-tooltip]:hover::after,
        .sidebar.collapsed .theme-toggle[data-tooltip]:hover::after {
            display: block;
        }

        /* =============================================
           MOBILE OVERLAY
        ============================================= */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 199;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width) !important;
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .sidebar-overlay {
                display: block;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.3s;
            }

            .sidebar.mobile-open~.sidebar-overlay {
                opacity: 1;
                pointer-events: auto;
            }

            .main-wrapper {
                margin-left: 0 !important;
            }

            .collapse-btn {
                display: none;
            }
        }

        /* =============================================
           SCROLLBAR GLOBAL
        ============================================= */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 3px;
        }
    </style>
    @stack('styles')
</head>

<body>

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar" id="sidebar">

        <!-- Brand -->
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="fas fa-wifi"></i>
            </div>
            <div class="brand-text">
                <h1>RFID DF</h1>
                <span>v2.0 — RFID WIP Management</span>
            </div>
        </div>

        <!-- Profile -->
        <div class="sidebar-profile">
            <div class="profile-avatar">
                A
                <span class="online-dot"></span>
            </div>
            <div class="profile-info">
                <h4>Administrator</h4>
                <span>Super Admin</span>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav" id="sidebarNav">

            <!-- === READER SECTION === -->
            <div class="nav-category">Reader</div>

            <!-- 1. Register Reader -->
            <div class="nav-item" id="menu-register">
                <a class="nav-link" onclick="toggleMenu('menu-register')" data-tooltip="Register Reader">
                    <span class="nav-icon"><i class="fas fa-id-card"></i></span>
                    <span class="nav-label">Register Reader</span>
                    <i class="fas fa-chevron-right nav-chevron"></i>
                </a>
                <div class="submenu">
                    <!-- Register Card -->
                    <div class="nav-item" id="menu-register-card">
                        <a class="nav-link" onclick="toggleMenu('menu-register-card')" data-tooltip="Register Card">
                            <span class="nav-icon"><i class="fas fa-credit-card"></i></span>
                            <span class="nav-label">Register Card</span>
                            <i class="fas fa-chevron-right nav-chevron"></i>
                        </a>
                        <div class="submenu">
                            <div class="nav-item">
                                <a class="nav-link" href="#" data-page="register-card-new"
                                    onclick="setActive(this, 'Pendaftaran Card', 'Register Card')"
                                    data-tooltip="Pendaftaran Card">
                                    <span class="nav-icon"><i class="fas fa-plus-circle"></i></span>
                                    <span class="nav-label">Pendaftaran Card</span>
                                </a>
                            </div>
                            <div class="nav-item">
                                <a class="nav-link" href="#" data-page="registered-cards"
                                    onclick="setActive(this, 'Card Terdaftar', 'Register Card')"
                                    data-tooltip="Card Terdaftar">
                                    <span class="nav-icon"><i class="fas fa-list-check"></i></span>
                                    <span class="nav-label">Card Terdaftar</span>
                                </a>
                            </div>
                            <div class="nav-item">
                                <a class="nav-link" href="#" data-page="card-status"
                                    onclick="setActive(this, 'Status Card', 'Register Card')"
                                    data-tooltip="Status Card">
                                    <span class="nav-icon"><i class="fas fa-toggle-on"></i></span>
                                    <span class="nav-label">Status Card</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Setting Register -->
                    <div class="nav-item">
                        <a class="nav-link" href="#" data-page="register-setting"
                            onclick="setActive(this, 'Setting', 'Register Reader')" data-tooltip="Setting Register">
                            <span class="nav-icon"><i class="fas fa-sliders"></i></span>
                            <span class="nav-label">Setting</span>
                        </a>
                    </div>

                    <!-- Audit Trail Register -->
                    <div class="nav-item">
                        <a class="nav-link" href="#" data-page="register-audit"
                            onclick="setActive(this, 'Audit Trail', 'Register Reader')"
                            data-tooltip="Audit Trail Register">
                            <span class="nav-icon"><i class="fas fa-scroll"></i></span>
                            <span class="nav-label">Audit Trail</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 2. Scan Reader -->
            <div class="nav-item" id="menu-scan">
                <a class="nav-link" onclick="toggleMenu('menu-scan')" data-tooltip="Scan Reader">
                    <span class="nav-icon"><i class="fas fa-radar"></i></span>
                    <span class="nav-label">Scan Reader</span>
                    <i class="fas fa-chevron-right nav-chevron"></i>
                </a>
                <div class="submenu">
                    <!-- Live Scan Log -->
                    <div class="nav-item">
                        <a class="nav-link" href="#" data-page="live-scan"
                            onclick="setActive(this, 'Live Scan Log', 'Scan Reader')" data-tooltip="Live Scan Log">
                            <span class="nav-icon"><i class="fas fa-satellite-dish"></i></span>
                            <span class="nav-label">Live Scan Log</span>
                            <span class="nav-badge" id="badge-live">●</span>
                        </a>
                    </div>

                    <!-- Setting Scan -->
                    <div class="nav-item">
                        <a class="nav-link" href="#" data-page="scan-setting"
                            onclick="setActive(this, 'Setting', 'Scan Reader')" data-tooltip="Setting Scan">
                            <span class="nav-icon"><i class="fas fa-sliders"></i></span>
                            <span class="nav-label">Setting</span>
                        </a>
                    </div>

                    <!-- Audit Trail Scan -->
                    <div class="nav-item">
                        <a class="nav-link" href="#" data-page="scan-audit"
                            onclick="setActive(this, 'Audit Trail', 'Scan Reader')" data-tooltip="Audit Trail Scan">
                            <span class="nav-icon"><i class="fas fa-scroll"></i></span>
                            <span class="nav-label">Audit Trail</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- === MONITORING SECTION === -->
            <div class="nav-category">Monitoring</div>

            <!-- 3. Audit Trail Umum -->
            <div class="nav-item">
                <a class="nav-link" href="#" data-page="audit-trail" onclick="setActive(this, 'Audit Trail Umum')"
                    data-tooltip="Audit Trail Umum">
                    <span class="nav-icon"><i class="fas fa-clipboard-list"></i></span>
                    <span class="nav-label">Audit Trail Umum</span>
                </a>
            </div>

            <!-- === MANAJEMEN SECTION === -->
            <div class="nav-category">Manajemen</div>

            <!-- 4. Profil -->
            <div class="nav-item">
                <a class="nav-link" href="#" data-page="profile" onclick="setActive(this, 'Profil')"
                    data-tooltip="Profil">
                    <span class="nav-icon"><i class="fas fa-user-circle"></i></span>
                    <span class="nav-label">Profil</span>
                </a>
            </div>

            <!-- 5. Level Access -->
            <div class="nav-item">
                <a class="nav-link" href="#" data-page="level-access" onclick="setActive(this, 'Level Access')"
                    data-tooltip="Level Access">
                    <span class="nav-icon"><i class="fas fa-layer-group"></i></span>
                    <span class="nav-label">Level Access</span>
                </a>
            </div>

            <!-- 6. Role Permission -->
            <div class="nav-item">
                <a class="nav-link" href="#" data-page="role-permission" onclick="setActive(this, 'Role & Permission')"
                    data-tooltip="Role Permission">
                    <span class="nav-icon"><i class="fas fa-shield-halved"></i></span>
                    <span class="nav-label">Role & Permission</span>
                </a>
            </div>

        </nav>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <!-- Theme Toggle -->
            <button class="theme-toggle" onclick="toggleTheme()" data-tooltip="Toggle Theme">
                <span class="nav-icon" id="themeIcon"><i class="fas fa-moon"></i></span>
                <span class="toggle-label" id="themeLabel">Dark Mode</span>
                <span class="toggle-pill"></span>
            </button>

            <!-- Logout -->
            <a class="nav-link" href="{{ route('logout') ?? '#' }}" data-tooltip="Logout"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <span class="nav-icon"><i class="fas fa-right-from-bracket"></i></span>
                <span class="nav-label" style="color: var(--accent-red);">Logout</span>
            </a>

            <form id="logout-form" action="{{ route('logout') ?? '#' }}" method="POST" style="display:none;">
                @csrf
            </form>
        </div>
    </aside>

    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>

    <!-- Collapse Button -->
    <button class="collapse-btn" id="collapseBtn" onclick="toggleCollapse()" title="Toggle Sidebar">
        <i class="fas fa-chevron-left"></i>
    </button>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-wrapper" id="mainWrapper">

        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <!-- Mobile hamburger -->
                <button class="topbar-btn" id="mobileMenuBtn" onclick="openMobileSidebar()" style="display:none;">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="topbar-breadcrumb">
                    <span class="crumb-parent" id="breadcrumbParent"></span>
                    <i class="fas fa-chevron-right" id="breadcrumbSep" style="display:none;"></i>
                    <span id="breadcrumbCurrent">Dashboard</span>
                </div>
            </div>

            <div class="topbar-right">
                <div class="topbar-status">
                    <span class="status-dot"></span>
                    Reader Online
                </div>
                <button class="topbar-btn" title="Notifications">
                    <i class="fas fa-bell"></i>
                </button>
            </div>
        </header>

        <!-- Dynamic Page Content -->
        <main class="page-content" id="pageContent">
            @yield('content')
        </main>
    </div>

    <script>
        // =============================================
        // THEME MANAGEMENT
        // =============================================
        const THEME_KEY = 'rfid_theme';

        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            const icon = document.getElementById('themeIcon');
            const label = document.getElementById('themeLabel');
            if (theme === 'dark') {
                icon.innerHTML = '<i class="fas fa-moon"></i>';
                label.textContent = 'Dark Mode';
            } else {
                icon.innerHTML = '<i class="fas fa-sun"></i>';
                label.textContent = 'Light Mode';
            }
            localStorage.setItem(THEME_KEY, theme);
        }

        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme');
            applyTheme(current === 'dark' ? 'light' : 'dark');
        }

        // Apply saved theme on load
        (function () {
            const saved = localStorage.getItem(THEME_KEY) || 'dark';
            applyTheme(saved);
        })();

        // =============================================
        // SIDEBAR COLLAPSE
        // =============================================
        const COLLAPSE_KEY = 'rfid_sidebar_collapsed';

        function toggleCollapse() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem(COLLAPSE_KEY, isCollapsed ? '1' : '0');
        }

        (function () {
            if (localStorage.getItem(COLLAPSE_KEY) === '1') {
                document.getElementById('sidebar').classList.add('collapsed');
            }
        })();

        // =============================================
        // MENU TOGGLE (ACCORDION)
        // =============================================
        function toggleMenu(menuId) {
            const item = document.getElementById(menuId);
            if (!item) return;

            // Don't toggle if sidebar is collapsed (just navigate or ignore)
            const sidebar = document.getElementById('sidebar');
            if (sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
                localStorage.setItem(COLLAPSE_KEY, '0');
            }

            const isOpen = item.classList.contains('open');

            // Close same-level siblings
            const parent = item.parentElement;
            parent.querySelectorAll(':scope > .nav-item.open').forEach(el => {
                if (el !== item) el.classList.remove('open');
            });

            item.classList.toggle('open', !isOpen);
        }

        // =============================================
        // ACTIVE STATE & BREADCRUMB
        // =============================================
        function setActive(el, pageTitle, parentTitle) {
            // Remove all active classes
            document.querySelectorAll('.nav-link.active').forEach(l => l.classList.remove('active'));

            // Set new active
            el.classList.add('active');

            // Breadcrumb
            const parent = document.getElementById('breadcrumbParent');
            const sep = document.getElementById('breadcrumbSep');
            const curr = document.getElementById('breadcrumbCurrent');

            if (parentTitle) {
                parent.textContent = parentTitle;
                sep.style.display = '';
                curr.textContent = pageTitle;
            } else {
                parent.textContent = '';
                sep.style.display = 'none';
                curr.textContent = pageTitle;
            }
        }

        // =============================================
        // MOBILE SIDEBAR
        // =============================================
        function openMobileSidebar() {
            document.getElementById('sidebar').classList.add('mobile-open');
        }

        function closeMobileSidebar() {
            document.getElementById('sidebar').classList.remove('mobile-open');
        }

        // Show hamburger on mobile
        function checkMobile() {
            const btn = document.getElementById('mobileMenuBtn');
            if (window.innerWidth <= 768) {
                btn.style.display = 'flex';
            } else {
                btn.style.display = 'none';
                document.getElementById('sidebar').classList.remove('mobile-open');
            }
        }

        window.addEventListener('resize', checkMobile);
        checkMobile();

        // =============================================
        // LIVE BADGE PULSE
        // =============================================
        const liveBadge = document.getElementById('badge-live');
        if (liveBadge) {
            setInterval(() => {
                liveBadge.style.opacity = liveBadge.style.opacity === '0' ? '1' : '0';
            }, 800);
        }
    </script>

    @stack('scripts')
</body>

</html>