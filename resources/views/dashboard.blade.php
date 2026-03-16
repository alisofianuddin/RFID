<!DOCTYPE html>
<html lang="id" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RFID System — Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: ['class', '[data-theme="dark"]'],
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        bg: {
                            primary: 'var(--bg-primary)',
                            secondary: 'var(--bg-secondary)',
                            card: 'var(--bg-card)',
                            'card-hover': 'var(--bg-card-hover)',
                        },
                        accent: {
                            blue: 'var(--accent-blue)',
                            cyan: 'var(--accent-cyan)',
                            green: 'var(--accent-green)',
                            red: 'var(--accent-red)',
                            orange: 'var(--accent-orange)',
                            purple: 'var(--accent-purple)',
                        },
                        text: {
                            primary: 'var(--text-primary)',
                            secondary: 'var(--text-secondary)',
                            muted: 'var(--text-muted)',
                        },
                        border: {
                            DEFAULT: 'var(--border)',
                        }
                    },
                    boxShadow: {
                        glow: 'var(--shadow)',
                        'glow-blue': 'var(--shadow-glow-blue)',
                        'glow-green': 'var(--shadow-glow-green)',
                        'glow-red': 'var(--shadow-glow-red)',
                    }
                }
            }
        }

        // ===== UI HELPERS (Defined early for inline handlers) =====
        const SB_THEME = 'rfid_theme', SB_COLL = 'rfid_sb_coll';
        function sbApplyTheme(t) {
            document.documentElement.setAttribute('data-theme', t);
            const ico = document.getElementById('sbThemeIco');
            const lbl = document.getElementById('sbThemeLbl');
            if(ico) ico.innerHTML = t === 'dark' ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
            if(lbl) lbl.textContent = t === 'dark' ? 'Dark Mode' : 'Light Mode';
            localStorage.setItem(SB_THEME, t);
        }
        function sbTheme() { sbApplyTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'); }
        function sbCollapse() {
            const s = document.getElementById('sidebar');
            if(!s) return;
            s.classList.toggle('collapsed');
            localStorage.setItem(SB_COLL, s.classList.contains('collapsed') ? '1' : '0');
        }
        function sbToggle(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const sb = document.getElementById('sidebar');
            if (sb && sb.classList.contains('collapsed')) { sb.classList.remove('collapsed'); localStorage.setItem(SB_COLL, '0'); }
            el.parentElement.querySelectorAll(':scope > .nav-item.open').forEach(s => { if (s !== el) s.classList.remove('open'); });
            el.classList.toggle('open');
        }
        function sbActive(el, page, parent) {
            document.querySelectorAll('.nav-lnk.active').forEach(l => l.classList.remove('active'));
            if (el) el.classList.add('active');
            const bp = document.getElementById('bcParent'), bs = document.getElementById('bcSep'), bc = document.getElementById('bcCurr');
            if (parent && bp) { bp.textContent = parent; bs.style.display = ''; bc.textContent = page; }
            else if (bp) { bp.textContent = ''; bs.style.display = 'none'; bc.textContent = page; }
        }
        function openSbMobile() {
            const s = document.getElementById('sidebar');
            if(s) s.classList.add('mob-open');
            const o = document.getElementById('sbOverlay');
            if(o) o.style.cssText = 'opacity:1;pointer-events:auto;';
        }
        function closeSbMobile() {
            const s = document.getElementById('sidebar');
            if(s) s.classList.remove('mob-open');
            const o = document.getElementById('sbOverlay');
            if(o) o.style.cssText = 'opacity:0;pointer-events:none;';
        }

        let currentPage = 'page-dashboard';
        const pageLoaders = {
            'page-dashboard': () => { if(window.loadStats) loadStats(); if(window.loadLogs) loadLogs(); },
            'page-live-scan': () => { if(window.loadLogs) loadLogs(); },
            'page-register-card': () => { if(window.loadRegisterCardTable) loadRegisterCardTable(); },
            'page-register-setting': () => { if(window.loadReaderStatus) loadReaderStatus(); },
            'page-scan-setting': () => { if(window.loadScanReaderStatus) loadScanReaderStatus(); },
            'page-register-audit': () => { if(window.loadAuditReg) loadAuditReg(); },
            'page-scan-audit': () => { if(window.loadAuditScan) loadAuditScan(); },
            'page-audit-umum': () => { if(window.loadAuditUmum) loadAuditUmum(); }
        };
        function showPage(pageId) {
            document.querySelectorAll('.page-view').forEach(p => p.style.display = 'none');
            const target = document.getElementById(pageId);
            if(target) target.style.display = 'block';
            currentPage = pageId;
            if (pageLoaders[pageId]) pageLoaders[pageId]();
        }

        // Apply theme immediately
        sbApplyTheme(localStorage.getItem(SB_THEME) || 'dark');
        
        // Setup initial collapse on DOM load
        document.addEventListener('DOMContentLoaded', () => {
            if (localStorage.getItem(SB_COLL) === '1') {
                const s = document.getElementById('sidebar');
                if(s) s.classList.add('collapsed');
            }
            const h = document.getElementById('hambBtn');
            if(h) {
                const cw = () => { h.style.display = window.innerWidth <= 768 ? 'flex' : 'none'; };
                window.addEventListener('resize', cw); cw();
            }
        });
    </script>
    <style type="text/tailwindcss">
        :root {
            --bg-primary: #0f0f23;
            --bg-secondary: #1a1a3e;
            --bg-card: #1e1e45;
            --bg-card-hover: #252560;
            --accent-blue: #3b82f6;
            --accent-cyan: #06b6d4;
            --accent-green: #10b981;
            --accent-red: #ef4444;
            --accent-orange: #f97316;
            --accent-purple: #8b5cf6;
            --text-primary: #f8fafc;
            --text-secondary: #e4e9efff;
            --text-muted: #0e1013ff;
            --border: #334155;
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            --shadow-glow-blue: 0 0 20px rgba(59, 130, 246, 0.3);
            --shadow-glow-green: 0 0 20px rgba(16, 185, 129, 0.3);
            --shadow-glow-red: 0 0 20px rgba(239, 68, 68, 0.3);
        }

        [data-theme="light"] {
            --bg-primary: #f1f5f9;
            --bg-secondary: #ffffff;
            --bg-card: #ffffff;
            --bg-card-hover: #f8fafc;
            --accent-blue: #2563eb;
            --accent-cyan: #0891b2;
            --accent-green: #059669;
            --accent-red: #dc2626;
            --accent-orange: #ea580c;
            --accent-purple: #7c3aed;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --border: #d8dce1ff;
            --shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
            --shadow-glow-blue: 0 0 15px rgba(37, 99, 235, 0.2);
            --shadow-glow-green: 0 0 15px rgba(5, 150, 105, 0.2);
            --shadow-glow-red: 0 0 15px rgba(220, 38, 38, 0.2);
        }

        body {
            @apply font-sans bg-bg-primary text-text-primary min-h-screen overflow-x-hidden transition-colors duration-300;
        }

        /* Animated background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse at 20% 50%, rgba(59, 130, 246, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(6, 182, 212, 0.06) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 80%, rgba(139, 92, 246, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        /* ===== COMPONENT STYLES (plain CSS for CDN compatibility) ===== */

        /* Main Content */
        .container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1600px;
            margin: 0 auto;
            padding: 24px 32px;
        }
        @media (max-width: 768px) {
            .container { padding: 16px 20px; }
        }

        /* Sections Layout */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 24px;
            align-items: stretch;
        }
        @media (max-width: 1024px) {
            .content-grid { grid-template-columns: 1fr; }
        }

        /* Card Component */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s, box-shadow 0.3s, border-color 0.3s;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            border-color: rgba(59, 130, 246, 0.3);
        }

        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(0,0,0,0.08);
        }
        .card-header h2 {
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-header h2 i { color: var(--accent-blue); }
        .card-body { padding: 20px 24px; flex: 1; }

        /* Live Scan Alert */
        .scan-alert {
            display: none;
            background: linear-gradient(135deg, rgba(16,185,129,0.1), rgba(6,182,212,0.1));
            border: 1px solid rgba(16,185,129,0.3);
            border-radius: 16px;
            padding: 16px 24px;
            margin-bottom: 24px;
            align-items: center;
            gap: 16px;
            box-shadow: var(--shadow-glow-green);
            transition: all 0.3s;
        }
        .scan-alert.show { display: flex; animation: pgIn .3s ease; }
        .scan-alert.unregistered {
            background: linear-gradient(135deg, rgba(239,68,68,0.1), rgba(249,115,22,0.1));
            border-color: rgba(239,68,68,0.3);
            box-shadow: var(--shadow-glow-red);
        }
        .scan-alert .alert-icon {
            width: 48px; height: 48px; min-width: 48px;
            border-radius: 50%;
            background: rgba(16,185,129,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 26px; color: var(--accent-green); flex-shrink: 0;
        }
        .scan-alert.unregistered .alert-icon {
            background: rgba(239,68,68,0.2); color: var(--accent-red);
        }
        .scan-alert .alert-info h3 { font-size: 16px; font-weight: 600; margin-bottom: 4px; }
        .scan-alert .alert-info p  { font-size: 13px; color: var(--text-secondary); }

        /* Log Table */
        .log-table { width: 100%; border-collapse: collapse; }
        .log-table thead {
            position: sticky; top: 0; z-index: 2;
        }
        .log-table thead th {
            padding: 14px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-secondary);
            background-color: var(--bg-card);
            border-bottom: 2px solid var(--border);
            white-space: nowrap;
        }
        .log-table tbody tr {
            border-bottom: 1px solid rgba(42,42,90,0.5);
            transition: background 0.2s;
        }
        .log-table tbody tr:hover { background: rgba(59,130,246,0.05); }
        .log-table tbody tr.new-row { animation: highlightRow 2s ease; }
        .log-table tbody td { padding: 14px 16px; font-size: 14px; }
        .log-table .uid-cell {
            font-family: 'Courier New', monospace;
            font-weight: 600; color: var(--accent-cyan); font-size: 13px;
        }

        /* Badges */
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.3px;
        }
        .badge-registered  { background: rgba(16,185,129,0.15); color: var(--accent-green); border: 1px solid rgba(16,185,129,0.3); }
        .badge-unregistered { background: rgba(239,68,68,0.15);  color: var(--accent-red);   border: 1px solid rgba(239,68,68,0.3); }
        .badge-active   { background: rgba(16,185,129,0.15); color: var(--accent-green); }
        .badge-inactive { background: rgba(148,163,184,0.15); color: #fcfcfdff; border: 1px solid rgba(148,163,184,0.25); }

        /* Forms */
        .card-form { display: flex; flex-direction: column; gap: 14px; }
        .form-group label {
            display: block; font-size: 12px; font-weight: 600;
            color: var(--text-secondary); margin-bottom: 6px;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .form-group input {
            width: 100%; padding: 12px 16px;
            background: var(--bg-secondary); border: 1px solid var(--border);
            border-radius: 10px; color: var(--text-primary);
            font-size: 14px; font-family: inherit; transition: all 0.3s;
        }
        .form-group input:focus {
            outline: none; border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
        }
        .form-group input::placeholder { color: var(--text-muted); }

        /* Buttons */
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 8px; padding: 12px 20px; border-radius: 10px;
            font-size: 14px; font-weight: 600; font-family: inherit;
            cursor: pointer; border: none; transition: all 0.3s;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-cyan));
            color: #fff;
        }
        .btn-primary:hover { box-shadow: var(--shadow-glow-blue); transform: translateY(-2px); }
        .btn-danger {
            background: rgba(239,68,68,0.15); color: var(--accent-red);
            border: 1px solid rgba(239,68,68,0.3);
        }
        .btn-danger:hover { background: rgba(239,68,68,0.25); }
        .btn-sm { padding: 6px 12px; font-size: 12px; border-radius: 8px; }
        .btn-secondary {
            background: rgba(255,255,255,0.06); color: var(--text-secondary);
            border: 1px solid var(--border);
        }
        .btn-secondary:hover { background: rgba(255,255,255,0.1); }
        .btn-success {
            background: linear-gradient(135deg, #00e676, #00c853);
            color: #000; font-weight: 700;
        }
        .btn-success:hover { box-shadow: var(--shadow-glow-green); transform: translateY(-2px); }

        /* Card List */
        .card-list { max-height: 400px; overflow-y: auto; }
        .card-list::-webkit-scrollbar { width: 6px; }
        .card-list::-webkit-scrollbar-track { background: transparent; }
        .card-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

        .card-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 0; border-bottom: 1px solid rgba(42,42,90,0.3);
        }
        .card-item:last-child { border-bottom: none; }
        .card-item-info h4 { font-size: 14px; font-weight: 600; margin-bottom: 4px; }
        .card-item-info p  { font-size: 12px; color: var(--text-muted); font-family: 'Courier New', monospace; }
        .card-item-actions  { display: flex; gap: 8px; }

        /* Empty state */
        .empty-state { text-align: center; padding: 40px 20px; color: var(--text-muted); }
        .empty-state i { font-size: 48px; margin-bottom: 12px; opacity: 0.3; }
        .empty-state p { font-size: 14px; }

        /* Modal */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.65); backdrop-filter: blur(6px);
            z-index: 500; align-items: center; justify-content: center;
        }
        .modal-overlay.show { display: flex; animation: fadeIn 0.25s ease; }
        .modal-box {
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: 20px; padding: 32px; width: 100%; max-width: 1500px;
            box-shadow: 0 24px 64px rgba(0,0,0,0.5); animation: slideUp 0.3s ease;
        }
        .modal-header { display: flex; align-items: center; gap: 14px; margin-bottom: 24px; }
        .modal-icon {
            width: 52px; height: 52px; border-radius: 14px;
            background: rgba(249,115,22,0.15); color: var(--accent-orange);
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; flex-shrink: 0;
        }
        .modal-header h3 { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
        .modal-header p  { font-size: 13px; color: var(--text-secondary); }
        .modal-uid-display {
            background: var(--bg-secondary); border: 1px solid var(--border);
            border-radius: 10px; padding: 12px 16px;
            font-family: 'Courier New', monospace; font-size: 15px; font-weight: 700;
            color: var(--accent-cyan); letter-spacing: 1px; margin-bottom: 20px; word-break: break-all;
        }
        .modal-uid-label {
            font-size: 11px; font-weight: 600; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;
        }
        .modal-footer { display: flex; gap: 10px; margin-top: 24px; }

        /* Toast */
        .toast-container {
            position: fixed; top: 80px; right: 24px; z-index: 1000;
            display: flex; flex-direction: column; gap: 8px;
        }
        .toast {
            padding: 14px 20px; border-radius: 10px; font-size: 14px; font-weight: 500;
            box-shadow: var(--shadow); animation: slideIn 0.3s ease; min-width: 280px;
        }
        .toast-success { background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); color: var(--accent-green); }
        .toast-error   { background: rgba(239,68,68,0.15);  border: 1px solid rgba(239,68,68,0.3);  color: var(--accent-red); }

        /* Log scroll */
        .log-scroll { max-height: 500px; overflow-y: auto; }
        .log-scroll::-webkit-scrollbar { width: 6px; }
        .log-scroll::-webkit-scrollbar-track { background: transparent; }
        .log-scroll::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

        /* Power Slider */
        .power-slider-container { padding: 8px 0; }
        .power-slider-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .power-value {
            font-size: 28px; font-weight: 800;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-cyan));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .power-label { font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .power-slider {
            -webkit-appearance: none; appearance: none; width: 100%; height: 8px; border-radius: 4px;
            background: linear-gradient(90deg, var(--accent-green), var(--accent-orange), var(--accent-red));
            outline: none; opacity: 0.9; transition: opacity 0.2s;
        }
        .power-slider:hover { opacity: 1; }
        .power-slider::-webkit-slider-thumb {
            -webkit-appearance: none; appearance: none; width: 24px; height: 24px; border-radius: 50%;
            background: #fff; cursor: pointer; border: 3px solid var(--accent-blue);
            box-shadow: 0 2px 8px rgba(0,0,0,0.3); transition: all 0.2s;
        }
        .power-slider::-webkit-slider-thumb:hover { transform: scale(1.15); box-shadow: 0 2px 12px rgba(59,130,246,0.5); }
        .power-slider::-moz-range-thumb {
            width: 24px; height: 24px; border-radius: 50%; background: #fff;
            cursor: pointer; border: 3px solid var(--accent-blue); box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .power-labels { display: flex; justify-content: space-between; margin-top: 8px; font-size: 11px; color: var(--text-muted); }
        .power-info { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border); }
        .power-info-item { background: var(--bg-secondary); border-radius: 10px; padding: 10px 14px; text-align: center; }
        .power-info-item .info-label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px; }
        .power-info-item .info-value { font-size: 14px; font-weight: 600; }
        .reader-status-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 6px; }
        .reader-status-dot.online  { background: var(--accent-green); }
        .reader-status-dot.offline { background: var(--accent-red); }

        /* Page view animation */
        .page-view { animation: pgIn .22s ease; }

        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .stat-card .stat-value { font-size: 24px; }
        }

        /* ===== SIDEBAR DESIGN TOKENS ===== */
        :root, [data-theme="dark"] {
            --sb-bg: #111120;
            --sb-border: rgba(255,255,255,.06);
            --sb-hover: rgba(99,102,241,.12);
            --sb-active: rgba(99,102,241,.20);
            --sb-submenu: rgba(0,0,0,.18);
            --sb-text: #94a3b8;
            --sb-active-text: #a5b4fc;
            --sb-muted: #475569;
            --sb-accent: #6366f1;
            --sb-accent-l: #818cf8;
            --sb-glow: rgba(99,102,241,.32);
            --sb-green: #22c55e;
            --sb-w: 264px;
            --sb-hdr: 60px;
            --sb-r: 8px;
            color-scheme: dark;
        }
        [data-theme="light"] {
            --bg-primary: #f4f6fb;
            --bg-secondary: #eef0f7;
            --bg-card: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --border: #e2e8f0;
            --accent-blue: #4f46e5;
            --accent-cyan: #0891b2;
            --accent-green: #16a34a;
            --accent-red: #dc2626;
            --accent-orange: #ea580c;
            --sb-bg: #ffffff;
            --sb-border: rgba(0,0,0,.06);
            --sb-hover: rgba(99,102,241,.07);
            --sb-active: rgba(99,102,241,.12);
            --sb-submenu: rgba(99,102,241,.04);
            --sb-text: #475569;
            --sb-active-text: #4338ca;
            --sb-muted: #94a3b8;
            --sb-accent: #4f46e5;
            --sb-accent-l: #6366f1;
            --sb-glow: rgba(79,70,229,.2);
            --sb-green: #16a34a;
            color-scheme: light;
        }

        body { display: flex; background: var(--bg-primary); transition: background .3s, color .3s; }

        /* Keyframes */
        @keyframes highlightRow { 0% { background: rgba(0,230,118,0.15); } 100% { background: transparent; } }
        @keyframes fadeIn  { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { transform: translateY(40px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @keyframes slideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes pgIn   { from { opacity: 0; transform: translateY(8px); }  to { opacity: 1; transform: translateY(0); } }
        @keyframes pulse  { 0%, 100% { box-shadow: 0 0 0 0 rgba(0,230,118,0.5); } 50% { box-shadow: 0 0 0 8px rgba(0,230,118,0); } }
        @keyframes smIn   { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }

    </style>
</head>

<body>

    <!-- ===== SIDEBAR ===== -->
    <aside
        class="sidebar group/sidebar peer fixed top-0 left-0 w-[var(--sb-w)] h-screen bg-bg-card border-r border-border flex flex-col z-[200] transition-[width,transform] duration-300 ease-[cubic-bezier(.4,0,.2,1)] [&.collapsed]:w-[70px] max-md:-translate-x-full max-md:!w-[var(--sb-w)] [&.mob-open]:translate-x-0"
        id="sidebar">
        <div
            class="sb-brand h-[var(--sb-hdr)] flex items-center px-5 border-b border-border overflow-hidden shrink-0 cursor-pointer">
            <div
                class="sb-brand-icon w-8 min-w-[32px] h-8 bg-gradient-to-br from-accent-blue to-accent-purple rounded-lg flex items-center justify-center text-white text-[14px] mr-3 shadow-glow-blue">
                <i class="fas fa-wifi"></i>
            </div>
            <div
                class="sb-brand-text whitespace-nowrap transition-opacity duration-200 group-[.collapsed]/sidebar:opacity-0">
                <h2 class="text-[16px] font-bold tracking-[.5px]">RFID DF</h2><small
                    class="text-[10px] text-text-muted">RFID WIP Management</small>
            </div>
        </div>
        <div class="sb-profile p-5 flex items-center border-b border-dashed border-border overflow-hidden shrink-0">
            <div
                class="sb-avatar w-[38px] min-w-[38px] h-[38px] rounded-full bg-bg-secondary border-2 border-border flex items-center justify-center font-bold text-text-primary mr-3 relative">
                A<span
                    class="on absolute bottom-0 right-0 w-2.5 h-2.5 bg-accent-green border-2 border-bg-card rounded-full"></span>
            </div>
            <div
                class="sb-profile-info whitespace-nowrap transition-opacity duration-200 group-[.collapsed]/sidebar:opacity-0">
                <b class="block text-[13px] text-text-primary">Administrator</b><small
                    class="text-[11px] text-text-muted">IT Functional Developer</small>
            </div>
        </div>
        <nav class="sb-nav flex-1 overflow-y-auto overflow-x-hidden py-4">
            <div
                class="nav-cat px-5 pt-2.5 pb-1 text-[10px] uppercase tracking-[1px] text-text-muted font-semibold whitespace-nowrap transition-opacity duration-200 group-[.collapsed]/sidebar:opacity-0">
                Reader</div>

            <div class="nav-item group/item mb-0.5" id="mn-register">
                <div class="nav-lnk flex items-center px-5 py-[10px] text-text-secondary hover:text-text-primary hover:bg-white/5 cursor-pointer relative transition-colors duration-200 [&.active]:text-accent-blue [&.active]:bg-gradient-to-r [&.active]:from-accent-blue/10 [&.active]:to-transparent [&.active]:border-l-[3px] [&.active]:border-accent-blue [&.active]:pl-[17px]"
                    onclick="sbToggle('mn-register')" data-tip="Register Reader">
                    <span class="ni w-[30px] min-w-[30px] inline-block text-[15px]"><i
                            class="fas fa-id-card-clip"></i></span><span
                        class="nl whitespace-nowrap text-[13px] font-medium transition-opacity duration-200 group-[.collapsed]/sidebar:opacity-0">Register
                        Reader</span><i
                        class="fas fa-chevron-right chev ml-auto text-[10px] transition-transform duration-300 opacity-60 group-[.collapsed]/sidebar:opacity-0 group-[.open]/item:rotate-90"></i>
                </div>
                <div
                    class="submenu hidden flex-col bg-black/15 border-l border-border ml-9 pl-1 my-1 group-[.open]/item:flex animate-[smIn_.25s_ease]">
                    <div class="nav-item mb-0.5"><a
                            class="nav-lnk flex items-center px-5 py-[10px] text-text-secondary hover:text-text-primary hover:bg-white/5 cursor-pointer relative transition-colors duration-200 [&.active]:text-accent-blue [&.active]:bg-gradient-to-r [&.active]:from-accent-blue/10 [&.active]:to-transparent [&.active]:border-l-[3px] [&.active]:border-accent-blue [&.active]:pl-[17px]"
                            href="#"
                            onclick="sbActive(this,'Register Card','Register Reader');showPage('page-register-card');return false"
                            data-tip="Register Card"><span class="ni w-[30px] min-w-[30px] inline-block text-[15px]"><i
                                    class="fas fa-credit-card"></i></span><span
                                class="nl whitespace-nowrap text-[13px] font-medium transition-opacity duration-200 group-[.collapsed]/sidebar:opacity-0">Register
                                Card</span></a></div>
                    <div class="nav-item mb-0.5"><a
                            class="nav-lnk flex items-center px-5 py-[10px] text-text-secondary hover:text-text-primary hover:bg-white/5 cursor-pointer relative transition-colors duration-200 [&.active]:text-accent-blue [&.active]:bg-gradient-to-r [&.active]:from-accent-blue/10 [&.active]:to-transparent [&.active]:border-l-[3px] [&.active]:border-accent-blue [&.active]:pl-[17px]"
                            href="#"
                            onclick="sbActive(this,'Setting','Register Reader');showPage('page-register-setting');return false"
                            data-tip="Setting Register"><span
                                class="ni w-[30px] min-w-[30px] inline-block text-[15px]"><i
                                    class="fas fa-sliders"></i></span><span
                                class="nl whitespace-nowrap text-[13px] font- medium transition-opacity duration-200 group-[.collapsed]/sidebar:opacity-0">Setting</span></a>
                    </div>
                    <div class="nav-item mb-0.5"><a
                            class="nav-lnk flex items-center px-5 py-[10px] text-text-secondary hover:text-text-primary hover:bg-white/5 cursor-pointer relative transition-colors duration-200 [&.active]:text-accent-blue [&.active]:bg-gradient-to-r [&.active]:from-accent-blue/10 [&.active]:to-transparent [&.active]:border-l-[3px] [&.active]:border-accent-blue [&.active]:pl-[17px]"
                            href="#"
                            onclick="sbActive(this,'Audit Trail','Register Reader');showPage('page-register-audit');return false"
                            data-tip="Audit Trail"><span class="ni w-[30px] min-w-[30px] inline-block text-[15px]"><i
                                    class="fas fa-scroll"></i></span><span
                                class="nl whitespace-nowrap text-[13px] font-medium transition-opacity duration-200 group-[.collapsed]/sidebar:opacity-0">Audit
                                Trail</span></a></div>
                </div>
            </div>
            <div class="nav-item group/item mb-0.5" id="mn-scan">
                <div class="nav-lnk flex items-center px-5 py-[10px] text-text-secondary hover:text-text-primary hover:bg-white/5 cursor-pointer relative transition-colors duration-200 [&.active]:text-accent-blue [&.active]:bg-gradient-to-r [&.active]:from-accent-blue/10 [&.active]:to-transparent [&.active]:border-l-[3px] [&.active]:border-accent-blue [&.active]:pl-[17px]"
                    onclick="sbToggle('mn-scan')" data-tip="Scan Reader">
                    <span class="ni w-[30px] min-w-[30px] inline-block text-[15px]"><i
                            class="fas fa-satellite-dish"></i></span><span
                        class="nl whitespace-nowrap text-[13px] font-medium transition-opacity duration-200 group-[.collapsed]/sidebar:opacity-0">Scan
                        Reader</span><i
                        class="fas fa-chevron-right chev ml-auto text-[10px] transition-transform duration-300 opacity-60 group-[.collapsed]/sidebar:opacity-0 group-[.open]/item:rotate-90"></i>
                </div>
                <div
                    class="submenu hidden flex-col bg-black/15 border-l border-border ml-9 pl-1 my-1 group-[.open]/item:flex animate-[smIn_.25s_ease]">
                    <div class="nav-item mb-0.5"><a
                            class="nav-lnk flex items-center px-5 py-[10px] text-text-secondary hover:text-text-primary hover:bg-white/5 cursor-pointer relative transition-colors duration-200 [&.active]:text-accent-blue [&.active]:bg-gradient-to-r [&.active]:from-accent-blue/10 [&.active]:to-transparent [&.active]:border-l-[3px] [&.active]:border-accent-blue [&.active]:pl-[17px]"
                            href="#"
                            onclick="sbActive(this,'Live Scan Log','Scan Reader');showPage('page-live-scan');return false"
                            data-tip="Live Scan Log"><span class="ni w-[30px] min-w-[30px] inline-block text-[15px]"><i
                                    class="fas fa-tower-broadcast"></i></span><span
                                class="nl whitespace-nowrap text-[13px] font-medium transition-opacity duration-200 group-[.collapsed]/sidebar:opacity-0">Live
                                Scan Log</span><span
                                class="lv-badge ml-2 px-1 py-[1px] text-[8px] font-bold tracking-[.5px] rounded border border-accent-red text-accent-red bg-accent-red/10 animate-pulse">LIVE</span></a>
                    </div>
                    <div class="nav-item mb-0.5"><a
                            class="nav-lnk flex items-center px-5 py-[10px] text-text-secondary hover:text-text-primary hover:bg-white/5 cursor-pointer relative transition-colors duration-200 [&.active]:text-accent-blue [&.active]:bg-gradient-to-r [&.active]:from-accent-blue/10 [&.active]:to-transparent [&.active]:border-l-[3px] [&.active]:border-accent-blue [&.active]:pl-[17px]"
                            href="#"
                            onclick="sbActive(this,'Setting','Scan Reader');showPage('page-scan-setting');return false"
                            data-tip="Setting Scan"><span class="ni w-[30px] min-w-[30px] inline-block text-[15px]"><i
                                    class="fas fa-sliders"></i></span><span
                                class="nl whitespace-nowrap text-[13px] font-medium transition-opacity duration-200 group-[.collapsed]/sidebar:opacity-0">Setting</span></a>
                    </div>
                    <div class="nav-item mb-0.5"><a
                            class="nav-lnk flex items-center px-5 py-[10px] text-text-secondary hover:text-text-primary hover:bg-white/5 cursor-pointer relative transition-colors duration-200 [&.active]:text-accent-blue [&.active]:bg-gradient-to-r [&.active]:from-accent-blue/10 [&.active]:to-transparent [&.active]:border-l-[3px] [&.active]:border-accent-blue [&.active]:pl-[17px]"
                            href="#"
                            onclick="sbActive(this,'Audit Trail','Scan Reader');showPage('page-scan-audit');return false"
                            data-tip="Audit Trail"><span class="ni w-[30px] min-w-[30px] inline-block text-[15px]"><i
                                    class="fas fa-scroll"></i></span><span
                                class="nl whitespace-nowrap text-[13px] font-medium transition-opacity duration-200 group-[.collapsed]/sidebar:opacity-0">Audit
                                Trail</span></a></div>
                </div>
            </div>

            <div
                class="nav-cat px-5 pt-2.5 pb-1 text-[10px] uppercase tracking-[1px] text-text-muted font-semibold whitespace-nowrap transition-opacity duration-200 group-[.collapsed]/sidebar:opacity-0">
                Monitoring</div>
            <div class="nav-item mb-0.5"><a
                    class="nav-lnk flex items-center px-5 py-[10px] text-text-secondary hover:text-text-primary hover:bg-white/5 cursor-pointer relative transition-colors duration-200 [&.active]:text-accent-blue [&.active]:bg-gradient-to-r [&.active]:from-accent-blue/10 [&.active]:to-transparent [&.active]:border-l-[3px] [&.active]:border-accent-blue [&.active]:pl-[17px]"
                    href="#" onclick="sbActive(this,'Audit Trail Umum');showPage('page-audit-umum');return false"
                    data-tip="Audit Trail Umum"><span class="ni w-[30px] min-w-[30px] inline-block text-[15px]"><i
                            class="fas fa-clipboard-list"></i></span><span
                        class="nl whitespace-nowrap text-[13px] font-medium transition-opacity duration-200 group-[.collapsed]/sidebar:opacity-0">Audit
                        Trail Umum</span></a></div>

            <div
                class="nav-cat px-5 pt-2.5 pb-1 text-[10px] uppercase tracking-[1px] text-text-muted font-semibold whitespace-nowrap transition-opacity duration-200 group-[.collapsed]/sidebar:opacity-0">
                Manajemen</div>
            <div class="nav-item mb-0.5"><a
                    class="nav-lnk flex items-center px-5 py-[10px] text-text-secondary hover:text-text-primary hover:bg-white/5 cursor-pointer relative transition-colors duration-200 [&.active]:text-accent-blue [&.active]:bg-gradient-to-r [&.active]:from-accent-blue/10 [&.active]:to-transparent [&.active]:border-l-[3px] [&.active]:border-accent-blue [&.active]:pl-[17px]"
                    href="#" onclick="sbActive(this,'Profil');showPage('page-profil');return false"
                    data-tip="Profil"><span class="ni w-[30px] min-w-[30px] inline-block text-[15px]"><i
                            class="fas fa-user-circle"></i></span><span
                        class="nl whitespace-nowrap text-[13px] font-medium transition-opacity duration-200 group-[.collapsed]/sidebar:opacity-0">Profil</span></a>
            </div>
            <div class="nav-item mb-0.5"><a
                    class="nav-lnk flex items-center px-5 py-[10px] text-text-secondary hover:text-text-primary hover:bg-white/5 cursor-pointer relative transition-colors duration-200 [&.active]:text-accent-blue [&.active]:bg-gradient-to-r [&.active]:from-accent-blue/10 [&.active]:to-transparent [&.active]:border-l-[3px] [&.active]:border-accent-blue [&.active]:pl-[17px]"
                    href="#" onclick="sbActive(this,'Level Access');showPage('page-level-access');return false"
                    data-tip="Level Access"><span class="ni w-[30px] min-w-[30px] inline-block text-[15px]"><i
                            class="fas fa-layer-group"></i></span><span
                        class="nl whitespace-nowrap text-[13px] font-medium transition-opacity duration-200 group-[.collapsed]/sidebar:opacity-0">Level
                        Access</span></a></div>
            <div class="nav-item mb-0.5"><a
                    class="nav-lnk flex items-center px-5 py-[10px] text-text-secondary hover:text-text-primary hover:bg-white/5 cursor-pointer relative transition-colors duration-200 [&.active]:text-accent-blue [&.active]:bg-gradient-to-r [&.active]:from-accent-blue/10 [&.active]:to-transparent [&.active]:border-l-[3px] [&.active]:border-accent-blue [&.active]:pl-[17px]"
                    href="#"
                    onclick="sbActive(this,'Role &amp; Permission');showPage('page-role-permission');return false"
                    data-tip="Role Permission"><span class="ni w-[30px] min-w-[30px] inline-block text-[15px]"><i
                            class="fas fa-shield-halved"></i></span><span
                        class="nl whitespace-nowrap text-[13px] font-medium transition-opacity duration-200 group-[.collapsed]/sidebar:opacity-0">Role
                        &amp; Permission</span></a></div>
        </nav>
        <div class="sb-footer p-4 border-t border-border mt-auto shrink-0 flex flex-col gap-1.5">
            <button
                class="theme-btn w-full flex items-center bg-bg-secondary border border-border rounded-lg text-text-secondary p-2.5 cursor-pointer transition-all duration-300 hover:text-text-primary group-[.collapsed]/sidebar:justify-center"
                onclick="sbTheme()">
                <span class="ni w-[30px] min-w-[30px] inline-block text-[15px]" id="sbThemeIco"><i
                        class="fas fa-moon"></i></span>
                <span
                    class="t-lbl transition-opacity duration-200 whitespace-nowrap text-[13px] font-medium group-[.collapsed]/sidebar:opacity-0 group-[.collapsed]/sidebar:w-0"
                    id="sbThemeLbl">Dark Mode</span>
                <span
                    class="t-pill ml-auto w-[34px] min-w-[34px] h-[18px] bg-accent-blue dark:bg-border rounded-full relative transition-[background,opacity] duration-200 group-[.collapsed]/sidebar:opacity-0 group-[.collapsed]/sidebar:w-0 overflow-hidden after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:w-[14px] after:h-[14px] after:bg-white dark:after:bg-text-muted after:rounded-full after:transition-all after:duration-200 after:translate-x-[16px] dark:after:translate-x-0"></span>
            </button>
            <div class="nav-item mt-0.5">
                <a class="nav-lnk flex items-center px-5 py-[10px] hover:bg-white/5 cursor-pointer relative transition-colors duration-200 text-accent-red"
                    href="#" data-tip="Logout">
                    <span class="ni w-[30px] min-w-[30px] inline-block text-[15px]"><i
                            class="fas fa-right-from-bracket"></i></span>
                    <span
                        class="nl whitespace-nowrap text-[13px] font-medium transition-opacity duration-200 group-[.collapsed]/sidebar:opacity-0">Logout</span>
                </a>
            </div>
        </div>
    </aside>
    <button
        class="cb-btn fixed top-[15px] left-[calc(var(--sb-w)-13px)] w-[26px] h-[26px] rounded-full bg-bg-card border border-border text-text-muted cursor-pointer flex items-center justify-center text-[10px] z-[300] shadow-[0_2px_8px_rgba(0,0,0,.2)] transition-all duration-300 ease-[cubic-bezier(.4,0,.2,1)] hover:text-text-primary hover:shadow-[0_0_12px_var(--shadow-glow-blue)] peer-[.collapsed]:left-[57px] peer-[.collapsed]:rotate-180 max-md:hidden"
        id="cbBtn" onclick="sbCollapse()" title="Toggle Sidebar"><i class="fas fa-chevron-left"></i></button>
    <div class="sb-overlay hidden fixed inset-0 bg-black/55 z-[199] max-md:block max-md:opacity-0 max-md:pointer-events-none max-md:transition-opacity peer-[.mob-open]:max-md:opacity-100 peer-[.mob-open]:max-md:pointer-events-auto"
        id="sbOverlay" onclick="closeSbMobile()"></div>

    <!-- ===== MAIN WRAP ===== -->
    <div class="ml-[var(--sb-w)] flex-1 flex flex-col min-h-screen transition-[margin-left] duration-300 ease-[cubic-bezier(.4,0,.2,1)] peer-[.collapsed]:ml-[70px] max-md:!ml-0"
        id="mainWrap">
        <header
            class="sticky top-0 z-[100] h-[var(--sb-hdr)] bg-bg-primary/85 backdrop-blur-[20px] border-b border-border flex items-center justify-between px-8 transition-colors duration-300">
            <div class="flex items-center gap-2.5">
                <button
                    class="hidden max-md:flex p-[7px_10px] bg-transparent border border-border text-text-secondary rounded items-center justify-center cursor-pointer"
                    id="hambBtn" onclick="openSbMobile()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="text-[14px] font-semibold flex items-center gap-2 text-text-primary">
                    <span class="text-[13px] font-normal text-text-muted" id="bcParent"></span>
                    <i class="fas fa-chevron-right text-[10px] text-text-muted hidden" id="bcSep"></i>
                    <span id="bcCurr"></span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div
                    class="flex items-center gap-[7px] px-[13px] py-[5px] bg-accent-green/10 border border-accent-green/25 rounded-[20px] text-[11.5px] font-semibold text-accent-green">
                    <div
                        class="w-2 h-2 rounded-full bg-accent-green shadow-[0_0_8px_var(--accent-green)] transition-colors duration-300">
                    </div>Reader Online
                </div>
            </div>
        </header>

        <!-- Toast Container -->
        <div class="toast-container" id="toastContainer"></div>

        <!-- Modal Assign BN -->
        <div class="modal-overlay" id="modalAssignBn">
            <div class="modal-box">
                <div class="modal-header">
                    <div class="modal-icon"><i class="fas fa-tag"></i></div>
                    <div>
                        <h3>Card Tidak Terdaftar!</h3>
                        <p>Assign BN untuk mendaftarkan card ini</p>
                    </div>
                </div>
                <div class="modal-uid-label">UID Card Terdeteksi</div>
                <div class="modal-uid-display" id="modalUidDisplay">--</div>
                <form id="assignBnForm" onsubmit="submitAssignBn(event)">
                    <div class="form-group">
                        <label>BN (Batch Number)</label>
                        <input type="text" id="modalBn" placeholder="MASUKKAN BN" required autocomplete="off" autofocus
                            oninput="this.value = this.value.toUpperCase()"
                            style="text-transform:uppercase;font-weight:600;letter-spacing:1px;">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" style="flex:1;" onclick="closeAssignModal()"><i
                                class="fas fa-times"></i> Lewati</button>
                        <button type="submit" class="btn btn-success" style="flex:2;"><i class="fas fa-check"></i>
                            Daftarkan
                            & Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="container" id="pageContainer">

            <!-- ============================================================
                 PAGES  (only one visible at a time via showPage())
                 Default = page-live-scan (dashboard utama)
            ============================================================ -->

            <!-- PAGE: Dashboard UTama (Blank/Welcome) -->
            <div class="page-view" id="page-dashboard">
                <div
                    style="display:flex; flex-direction:column; align-items:center; justify-content:center; height: 60vh; text-align:center;">
                    <div style="font-size: 64px; color: var(--text-muted); margin-bottom: 24px;">
                        <i class="fas fa-wifi"></i>
                    </div>
                    <h2 style="font-size: 24px; color: var(--text-primary); margin-bottom: 12px;">Sistem RFID Management
                    </h2>
                    <p style="color: var(--text-secondary); max-width: 400px; line-height: 1.6;">Selamat datang di
                        Sistem RFID. Silakan gunakan menu navigasi di sebelah kiri untuk mengakses fitur pendaftaran
                        kartu, pemindaian, dan pengaturan reader HW-VX6330k v2.</p>
                </div>
            </div>

            <!-- PAGE: Live Scan Log -->
            <div class="page-view" id="page-live-scan" style="display:none;">
                <div class="scan-alert" id="scanAlert">
                    <div class="alert-icon" id="alertIcon"><i class="fas fa-check-circle"></i></div>
                    <div class="alert-info">
                        <h3 id="alertTitle">Card Terbaca!</h3>
                        <p id="alertDetail">UID: --- | Waktu: ---</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-tower-broadcast"></i> Data Scan Tag - HW-VX6330k v2</h2>
                        <span style="font-size:12px;color:var(--text-muted);">Auto-refresh setiap 3 detik</span>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <div class="log-scroll">
                            <table class="log-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>EPC / UID</th>
                                        <th>BN</th>
                                        <th>Status</th>
                                        <th>Waktu Scan</th>
                                    </tr>
                                </thead>
                                <tbody id="logTableBody">
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state"><i class="fas fa-wifi"></i>
                                                <p>Menunggu scan card dari reader HW-VX6330k v2...</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PAGE: Register Card (form + table) -->
            <div class="page-view" id="page-register-card" style="display:none;">
                <!-- Registration Form -->
                <div class="card" style="margin-bottom:24px;">
                    <div class="card-header">
                        <h2><i class="fas fa-plus-circle"></i> Pendaftaran Card Baru</h2>
                    </div>
                    <div class="card-body">
                        <form class="card-form" id="registerForm" onsubmit="registerCard(event)"
                            style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <div class="form-group"><label>UID Kartu</label><input type="text" id="inputUid"
                                    placeholder="Scan card ke reader untuk mengisi UID" required readonly
                                    style="background:var(--bg-secondary);cursor:not-allowed;opacity:0.85;"></div>
                            <div class="form-group"><label>BN (Batch Number)</label><input type="text" id="inputBn"
                                    placeholder="MASUKKAN BN" required oninput="this.value=this.value.toUpperCase()"
                                    style="text-transform:uppercase;font-weight:600;letter-spacing:1px;"></div>
                            <div style="grid-column:1/-1;">
                                <button type="submit" class="btn btn-primary" style="width:100%;"><i
                                        class="fas fa-save"></i> Daftarkan Card</button>
                            </div>
                        </form>
                        <p style="margin-top:14px;font-size:12px;color:var(--text-muted);"><i
                                class="fas fa-info-circle"></i> Tip: Tempelkan card ke reader — UID otomatis terisi.
                        </p>
                    </div>
                </div>

                <!-- Registered Cards Table -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-id-card"></i> Card Terdaftar</h2>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <span style="font-size:12px;color:var(--text-muted);" id="cardCount">0 cards</span>
                            <button class="btn btn-primary btn-sm" onclick="loadRegisterCardTable()"><i
                                    class="fas fa-sync"></i> Refresh</button>
                        </div>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <table class="log-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>UID</th>
                                    <th>BN</th>
                                    <th>Status</th>
                                    <th>Waktu Daftar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="registerCardBody">
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state"><i class="fas fa-id-card"></i>
                                            <p>Belum ada card terdaftar</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- PAGE: Register Setting -->
            <div class="page-view" id="page-register-setting" style="display:none;">
                <div class="content-grid">
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-sliders"></i> Pengaturan Register Reader</h2><span
                                style="font-size:12px;" id="readerStatusText"><span
                                    class="reader-status-dot offline"></span>Checking...</span>
                        </div>
                        <div class="card-body">
                            <div class="power-slider-container">
                                <div class="power-slider-header">
                                    <div>
                                        <div class="power-label">Power / Jarak Baca</div>
                                        <div class="power-value" id="powerDisplay">--</div>
                                    </div>
                                    <div style="text-align:right;">
                                        <div class="power-label">Estimasi Jarak</div>
                                        <div style="font-size:16px;font-weight:600;color:var(--accent-cyan);"
                                            id="distanceEstimate">--</div>
                                    </div>
                                </div>
                                <input type="range" min="0" max="30" value="15" class="power-slider" id="powerSlider"
                                    oninput="updatePowerDisplay(this.value)">
                                <div class="power-labels"><span>0 (Dekat)</span><span>15</span><span>30 (Jauh)</span>
                                </div>
                            </div>
                            <button class="btn btn-primary" style="width:100%;margin-top:16px;" onclick="applyPower()"
                                id="btnApplyPower"><i class="fas fa-check"></i> Terapkan Power</button>
                            <div class="power-info" id="readerInfoGrid">
                                <div class="power-info-item">
                                    <div class="info-label">COM Port</div>
                                    <div class="info-value" id="readerIp">--</div>
                                </div>
                                <div class="power-info-item">
                                    <div class="info-label">Baud Rate</div>
                                    <div class="info-value" id="readerPort">--</div>
                                </div>
                                <div class="power-info-item">
                                    <div class="info-label">Firmware</div>
                                    <div class="info-value" id="readerFirmware">--</div>
                                </div>
                                <div class="power-info-item">
                                    <div class="info-label">Power Aktif</div>
                                    <div class="info-value" id="readerCurrentPower">--</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-network-wired"></i> Konfigurasi Koneksi</h2>
                        </div>
                        <div class="card-body">
                            <div class="card-form">
                                <div class="form-group"><label>COM Port</label><input type="text" id="cfgIp"
                                        placeholder="COM7"></div>
                                <div class="form-group"><label>Baud Rate</label><input type="number" id="cfgPort"
                                        placeholder="9600"></div>
                                <div class="form-group"><label>Time Card (ms)</label><input type="number" id="cfgTime"
                                        placeholder="500" min="100" max="5000"></div>
                                <div class="form-group"><label>Jarak Baca (Power)</label><input type="number"
                                        id="cfgPower" placeholder="15" min="0" max="30"></div>
                                <button class="btn btn-primary" style="width:100%;"
                                    onclick="showToast('Konfigurasi disimpan!','success')"><i class="fas fa-save"></i>
                                    Simpan Konfigurasi</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PAGE: Register Audit Trail -->
            <div class="page-view" id="page-register-audit" style="display:none;">
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-scroll"></i> Audit Trail — Register Reader</h2><button
                            class="btn btn-primary btn-sm" onclick="loadAuditReg()"><i class="fas fa-sync"></i>
                            Refresh</button>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <table class="log-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Aksi</th>
                                    <th>UID</th>
                                    <th>BN</th>
                                    <th>Operator</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody id="auditRegBody">
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state"><i class="fas fa-scroll"></i>
                                            <p>Belum ada log aktivitas registrasi</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- PAGE: Scan Setting -->
            <div class="page-view" id="page-scan-setting" style="display:none;">
                <div class="content-grid">
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-sliders"></i> Pengaturan Scan Reader</h2>
                            <span style="font-size:12px;" id="readerStatusTextQ"><span
                                    class="reader-status-dot offline"></span>Checking...</span>
                        </div>
                        <div class="card-body">
                            <div class="power-slider-container">
                                <div class="power-slider-header">
                                    <div>
                                        <div class="power-label">Power / Jarak Baca</div>
                                        <div class="power-value" id="powerDisplayQ">--</div>
                                    </div>
                                    <div style="text-align:right;">
                                        <div class="power-label">Estimasi Jarak</div>
                                        <div style="font-size:16px;font-weight:600;color:var(--accent-cyan);"
                                            id="distanceEstimateQ">--</div>
                                    </div>
                                </div>
                                <input type="range" min="0" max="30" value="15" class="power-slider" id="powerSliderQ"
                                    oninput="updatePowerDisplay(this.value, 'Q')">
                                <div class="power-labels"><span>0 (Dekat)</span><span>15</span><span>30 (Jauh)</span>
                                </div>
                            </div>
                            <button class="btn btn-primary" style="width:100%;margin-top:16px;"
                                onclick="applyPower('Q')" id="btnApplyPowerQ"><i class="fas fa-check"></i> Terapkan
                                Power</button>
                            <div class="power-info" id="readerInfoGridQ">
                                <div class="power-info-item">
                                    <div class="info-label">IP Address</div>
                                    <div class="info-value" id="readerIpQ">--</div>
                                </div>
                                <div class="power-info-item">
                                    <div class="info-label">Port</div>
                                    <div class="info-value" id="readerPortQ">--</div>
                                </div>
                                <div class="power-info-item">
                                    <div class="info-label">Firmware</div>
                                    <div class="info-value" id="readerFirmwareQ">--</div>
                                </div>
                                <div class="power-info-item">
                                    <div class="info-label">Power Aktif</div>
                                    <div class="info-value" id="readerCurrentPowerQ">--</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-network-wired"></i> Konfigurasi Koneksi</h2>
                        </div>
                        <div class="card-body">
                            <div class="card-form">
                                <div class="form-group"><label>IP Address Reader</label><input type="text"
                                        id="scanCfgIp" placeholder="192.168.1.100"></div>
                                <div class="form-group"><label>Port</label><input type="number" id="scanCfgPort"
                                        placeholder="6000"></div>
                                <div class="form-group"><label>Time Card (ms)</label><input type="number"
                                        id="scanCfgTime" placeholder="500" min="100" max="5000"></div>
                                <div class="form-group"><label>Power / Jarak Baca</label><input type="number"
                                        id="scanCfgPower" placeholder="15" min="0" max="30"></div>
                                <button class="btn btn-primary" style="width:100%;"
                                    onclick="saveScanConfig()" id="btnSaveScanCfg"><i class="fas fa-save"></i>
                                    Simpan Setting</button>
                            </div>
                        </div>

                        <!-- NEW DIAGNOSTIC CARD -->
                        <div class="card" style="margin-top: 20px;">
                            <div class="card-header" style="background: rgba(var(--accent-blue-rgb), 0.1); border-bottom: 1px solid var(--border);">
                                <h2><i class="fas fa-microchip"></i> Diagnostik Hardware (Live)</h2>
                                <button class="btn btn-primary btn-sm" onclick="loadScanReaderStatus()">
                                    <i class="fas fa-sync"></i> Refresh Data
                                </button>
                            </div>
                            <div class="card-body">
                                <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 15px;">
                                    Data di bawah ini merupakan respon mentah (raw data) yang diterima dari script Python <code>main.py</code> (Hardware Reader sebenarnya) yang mengabarkan status aslinya ke server. Jika status "Online" tetapi reader tidak merespon/membaca, perhatikan kemungkinan masalah jaringan lokal.
                                </p>
                                <pre id="diagnosticData" style="background: var(--bg-primary); padding: 15px; border-radius: 8px; font-family: monospace; font-size: 12px; color: var(--text-primary); border: 1px solid var(--border); overflow-x: auto; white-space: pre-wrap;">Menunggu data diagnostik...</pre>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- PAGE: Scan Audit Trail -->
            <div class="page-view" id="page-scan-audit" style="display:none;">
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-scroll"></i> Audit Trail — Scan Reader</h2><button
                            class="btn btn-primary btn-sm" onclick="loadAuditScan()"><i class="fas fa-sync"></i>
                            Refresh</button>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <table class="log-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>UID</th>
                                    <th>BN</th>
                                    <th>Status</th>
                                    <th>Catatan</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody id="auditScanBody">
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state"><i class="fas fa-scroll"></i>
                                            <p>Belum ada log aktivitas scan</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- PAGE: Audit Trail Umum -->
            <div class="page-view" id="page-audit-umum" style="display:none;">
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-clipboard-list"></i> Audit Trail Umum</h2>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <select id="auditFilter" class="form-group"
                                style="padding:6px 12px;border-radius:6px;background:var(--bg-secondary);border:1px solid var(--border);color:var(--text-primary);font-size:12px;">
                                <option value="">Semua Aksi</option>
                                <option value="register">Register</option>
                                <option value="scan">Scan</option>
                                <option value="delete">Delete</option>
                                <option value="setting">Setting</option>
                            </select>
                            <button class="btn btn-primary btn-sm" onclick="loadAuditUmum()"><i class="fas fa-sync"></i>
                                Refresh</button>
                        </div>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <table class="log-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Modul</th>
                                    <th>Aksi</th>
                                    <th>Detail</th>
                                    <th>Operator</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody id="auditUmumBody">
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state"><i class="fas fa-clipboard-list"></i>
                                            <p>Belum ada log aktivitas sistem</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- PAGE: Profil -->
            <div class="page-view" id="page-profil" style="display:none;">
                <div class="content-grid">
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-user-circle"></i> Informasi Profil</h2>
                        </div>
                        <div class="card-body">
                            <div style="text-align:center;margin-bottom:24px;">
                                <div
                                    style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--sb-accent),#22d3ee);display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:700;color:#fff;margin:0 auto 12px;">
                                    A</div>
                                <h3 style="font-size:18px;font-weight:700;">Administrator</h3>
                                <span style="font-size:13px;color:var(--text-muted);">IT Functional Developer</span>
                            </div>
                            <div class="card-form">
                                <div class="form-group"><label>Nama Lengkap</label><input type="text"
                                        value="Administrator" placeholder="Nama Lengkap"></div>
                                <div class="form-group"><label>Email</label><input type="email" value="admin@rfid.com"
                                        placeholder="Email"></div>
                                <div class="form-group"><label>Jabatan</label><input type="text"
                                        value="IT Functional Developer" placeholder="Jabatan"></div>
                                <div class="form-group"><label>Departemen</label><input type="text"
                                        value="IT Department" placeholder="Departemen"></div>
                                <button class="btn btn-primary" style="width:100%;"
                                    onclick="showToast('Profil berhasil diperbarui!','success')"><i
                                        class="fas fa-save"></i> Simpan Perubahan</button>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-lock"></i> Keamanan Akun</h2>
                        </div>
                        <div class="card-body">
                            <div class="card-form">
                                <div class="form-group"><label>Password Lama</label><input type="password"
                                        placeholder="••••••••"></div>
                                <div class="form-group"><label>Password Baru</label><input type="password"
                                        placeholder="Minimal 8 karakter"></div>
                                <div class="form-group"><label>Konfirmasi Password</label><input type="password"
                                        placeholder="Ulangi password baru"></div>
                                <button class="btn btn-primary" style="width:100%;"
                                    onclick="showToast('Password berhasil diubah!','success')"><i
                                        class="fas fa-key"></i> Ubah Password</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PAGE: Level Access -->
            <div class="page-view" id="page-level-access" style="display:none;">
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-layer-group"></i> Level Access</h2><button class="btn btn-primary btn-sm"
                            onclick="showToast('Level Access disimpan!','success')"><i class="fas fa-save"></i>
                            Simpan</button>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <table class="log-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Level</th>
                                    <th>Deskripsi</th>
                                    <th>Hak Akses</th>
                                    <th>Jumlah User</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td><span class="badge"
                                            style="background:rgba(239,68,68,.15);color:#ef4444;border:1px solid rgba(239,68,68,.3);">Super
                                            Admin</span></td>
                                    <td>Akses penuh ke seluruh sistem</td>
                                    <td>All Modules</td>
                                    <td>1</td>
                                    <td><span class="badge badge-registered">Aktif</span></td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td><span class="badge"
                                            style="background:rgba(99,102,241,.15);color:#818cf8;border:1px solid rgba(99,102,241,.3);">Admin</span>
                                    </td>
                                    <td>Manajemen reader & card</td>
                                    <td>Register, Scan, Audit</td>
                                    <td>3</td>
                                    <td><span class="badge badge-registered">Aktif</span></td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td><span class="badge"
                                            style="background:rgba(34,197,94,.15);color:#22c55e;border:1px solid rgba(34,197,94,.3);">Supervisor</span>
                                    </td>
                                    <td>Monitoring & laporan</td>
                                    <td>Scan, Audit (Read)</td>
                                    <td>5</td>
                                    <td><span class="badge badge-registered">Aktif</span></td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td><span class="badge"
                                            style="background:rgba(251,146,60,.15);color:#fb923c;border:1px solid rgba(251,146,60,.3);">Operator</span>
                                    </td>
                                    <td>Operasional scan harian</td>
                                    <td>Scan only</td>
                                    <td>12</td>
                                    <td><span class="badge badge-registered">Aktif</span></td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td><span class="badge badge-unregistered">Guest</span></td>
                                    <td>Akses terbatas view only</td>
                                    <td>Dashboard (Read)</td>
                                    <td>0</td>
                                    <td><span class="badge badge-inactive">Non-Aktif</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- PAGE: Role & Permission -->
            <div class="page-view" id="page-role-permission" style="display:none;">
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-shield-halved"></i> Role &amp; Permission</h2><button
                            class="btn btn-primary btn-sm" onclick="showToast('Permission disimpan!','success')"><i
                                class="fas fa-save"></i> Simpan Semua</button>
                    </div>
                    <div class="card-body" style="overflow-x:auto;">
                        <table class="log-table" style="min-width:700px;">
                            <thead>
                                <tr>
                                    <th>Permission</th>
                                    <th style="text-align:center;">Super Admin</th>
                                    <th style="text-align:center;">Admin</th>
                                    <th style="text-align:center;">Supervisor</th>
                                    <th style="text-align:center;">Operator</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5"
                                        style="background:var(--bg-secondary);font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.5px;padding:10px 16px;color:var(--text-muted);">
                                        Register Reader</td>
                                </tr>
                                <tr>
                                    <td style="padding-left:24px;">Pendaftaran Card</td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox"></td>
                                    <td style="text-align:center;"><input type="checkbox"></td>
                                </tr>
                                <tr>
                                    <td style="padding-left:24px;">Lihat Card Terdaftar</td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox"></td>
                                </tr>
                                <tr>
                                    <td style="padding-left:24px;">Hapus Card</td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox"></td>
                                    <td style="text-align:center;"><input type="checkbox"></td>
                                </tr>
                                <tr>
                                    <td style="padding-left:24px;">Setting Register</td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox"></td>
                                    <td style="text-align:center;"><input type="checkbox"></td>
                                </tr>
                                <tr>
                                    <td colspan="5"
                                        style="background:var(--bg-secondary);font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.5px;padding:10px 16px;color:var(--text-muted);">
                                        Scan Reader</td>
                                </tr>
                                <tr>
                                    <td style="padding-left:24px;">Live Scan Log</td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                </tr>
                                <tr>
                                    <td style="padding-left:24px;">Setting Scan</td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox"></td>
                                    <td style="text-align:center;"><input type="checkbox"></td>
                                </tr>
                                <tr>
                                    <td style="padding-left:24px;">Audit Trail Scan</td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox"></td>
                                </tr>
                                <tr>
                                    <td colspan="5"
                                        style="background:var(--bg-secondary);font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.5px;padding:10px 16px;color:var(--text-muted);">
                                        Monitoring & Manajemen</td>
                                </tr>
                                <tr>
                                    <td style="padding-left:24px;">Audit Trail Umum</td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox"></td>
                                </tr>
                                <tr>
                                    <td style="padding-left:24px;">Level Access</td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox"></td>
                                    <td style="text-align:center;"><input type="checkbox"></td>
                                    <td style="text-align:center;"><input type="checkbox"></td>
                                </tr>
                                <tr>
                                    <td style="padding-left:24px;">Role &amp; Permission</td>
                                    <td style="text-align:center;"><input type="checkbox" checked></td>
                                    <td style="text-align:center;"><input type="checkbox"></td>
                                    <td style="text-align:center;"><input type="checkbox"></td>
                                    <td style="text-align:center;"><input type="checkbox"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div><!-- /container -->
    </div><!-- /main-wrap -->


    <script>
        const API_BASE = '/api';
        let lastLogId = 0;
        let alertTimeout = null;

        // ===== TOAST NOTIFICATION =====
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100px)';
                toast.style.transition = 'all 0.3s';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        // ===== SCAN ALERT =====
        let lastUnregisteredUid = null;

        function showScanAlert(data, isNew = false) {
            const alert = document.getElementById('scanAlert');
            const icon = document.getElementById('alertIcon');
            const title = document.getElementById('alertTitle');
            const detail = document.getElementById('alertDetail');

            if (data.status === 'registered') {
                alert.className = 'scan-alert show';
                icon.innerHTML = '<i class="fas fa-check-circle"></i>';
                title.textContent = `✅ BN: ${data.bn}`;
            } else {
                alert.className = 'scan-alert show unregistered';
                icon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
                title.textContent = '⚠️ Card Tidak Terdaftar';

                // Tampilkan modal assign BN dinonaktifkan sesuai permintaan (hanya monitoring)
                // if (isNew && data.uid !== lastUnregisteredUid) {
                //     lastUnregisteredUid = data.uid;
                //     openAssignModal(data.uid);
                // }
            }
            detail.textContent = `UID: ${data.uid} | Waktu: ${data.scanned_at}`;

            clearTimeout(alertTimeout);
            alertTimeout = setTimeout(() => {
                alert.classList.remove('show');
            }, 6000);
        }

        // ===== MODAL ASSIGN BN =====
        function openAssignModal(cardUid) {
            document.getElementById('modalUidDisplay').textContent = cardUid;
            document.getElementById('modalBn').value = '';
            document.getElementById('modalAssignBn').classList.add('show');
            setTimeout(() => document.getElementById('modalBn').focus(), 300);
        }

        function closeAssignModal() {
            document.getElementById('modalAssignBn').classList.remove('show');
        }

        async function submitAssignBn(e) {
            e.preventDefault();
            const uid = document.getElementById('modalUidDisplay').textContent.trim();
            const bn = document.getElementById('modalBn').value.trim().toUpperCase();
            const btn = e.submitter || e.target.querySelector('[type=submit]');

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

            try {
                const res = await fetch(`${API_BASE}/rfid/cards`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ uid, bn }),
                });
                const json = await res.json();
                if (json.success) {
                    showToast(`BN: ${bn} berhasil didaftarkan ke UID tersebut! ✅`);
                    closeAssignModal();
                    loadCards();
                    loadStats();
                    lastUnregisteredUid = null;
                } else {
                    const err = json.errors ? Object.values(json.errors).flat().join(', ') : json.message;
                    showToast(err, 'error');
                }
            } catch (err) {
                showToast('Gagal mendaftarkan card!', 'error');
            }

            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Daftarkan & Simpan';
        }

        // Tutup modal saat klik overlay
        document.getElementById('modalAssignBn').addEventListener('click', function (e) {
            if (e.target === this) closeAssignModal();
        });

        // ===== LOAD STATS =====
        async function loadStats() {
            try {
                const res = await fetch(`${API_BASE}/rfid/stats`);
                const json = await res.json();
                if (json.success) {
                    document.getElementById('statTotalCards').textContent = json.data.total_cards;
                    document.getElementById('statTotalScans').textContent = json.data.total_scans_today;
                    document.getElementById('statRegistered').textContent = json.data.registered_scans_today;
                    document.getElementById('statUnregistered').textContent = json.data.unregistered_scans_today;
                }
            } catch (e) {
                console.error('Stats error:', e);
            }
        }

        // ===== LOAD LOGS (Polling) =====
        async function loadLogs() {
            try {
                // Saat pertama kali (lastLogId = 0), ambil semua log tanpa filter
                const url = lastLogId > 0
                    ? `${API_BASE}/rfid/logs?last_id=${lastLogId}&limit=50`
                    : `${API_BASE}/rfid/logs?limit=50`;

                const res = await fetch(url);
                const json = await res.json();
                if (json.success && json.data.length > 0) {
                    const tbody = document.getElementById('logTableBody');
                    const isFirstLoad = lastLogId === 0;

                    // Remove empty state
                    const emptyState = tbody.querySelector('.empty-state');
                    if (emptyState) emptyState.closest('tr').remove();

                    // Add new rows at top (reverse because we want newest at the very top)
                    [...json.data].reverse().forEach(log => {
                        const tr = document.createElement('tr');
                        tr.className = 'new-row';
                        tr.innerHTML = `
                            <td style="color:var(--text-muted);font-size:12px;">${log.id}</td>
                            <td class="uid-cell">${log.uid}</td>
                            <td>${log.bn}</td>
                            <td><span class="badge badge-${log.status}">${log.status === 'registered' ? '✓ Terdaftar' : '✗ Tidak Terdaftar'}</span></td>
                            <td style="color:var(--text-secondary);font-size:13px;">${formatWIB(log.scanned_at)}</td>
                        `;
                        tbody.insertBefore(tr, tbody.firstChild);
                    });

                    // Hanya tampilkan alert & modal untuk scan BARU (bukan initial load)
                    if (!isFirstLoad) {
                        showScanAlert(json.data[0], true);
                    } else {
                        // Initial load: hanya tampilkan alert tanpa modal
                        showScanAlert(json.data[0], false);
                    }

                    lastLogId = json.latest_id;
                    loadStats();
                }
            } catch (e) {
                console.error('Logs error:', e);
            }
        }

        // ===== LOAD CARDS =====
        async function loadCards() {
            try {
                const res = await fetch(`${API_BASE}/rfid/cards`);
                const json = await res.json();
                if (json.success) {
                    const list = document.getElementById('cardList');
                    const count = document.getElementById('cardCount');
                    count.textContent = `${json.data.length} cards`;

                    if (json.data.length === 0) {
                        list.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-id-card"></i>
                            <p>Belum ada card terdaftar</p>
                        </div>`;
                        return;
                    }

                    list.innerHTML = json.data.map(card => `
                    <div class="card-item">
                        <div class="card-item-info">
                            <h4>BN: ${card.bn} <span class="badge badge-${card.status}" style="margin-left:8px;">${card.status}</span></h4>
                            <p>UID: ${card.uid}</p>
                        </div>
                        <div class="card-item-actions">
                            <button class="btn btn-danger btn-sm" onclick="deleteCard(${card.id}, '${card.bn}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
                }
            } catch (e) {
                console.error('Cards error:', e);
            }
        }

        // ===== REGISTER CARD (supports both forms) =====
        async function registerCard(e) {
            e.preventDefault();
            // detect which form fired the event
            const frm = e.target;
            const uidEl = frm.querySelector('[id^="inputUid"]');
            const bnEl = frm.querySelector('[id^="inputBn"]');
            const uid = uidEl ? uidEl.value.trim() : '';
            const bn = bnEl ? bnEl.value.trim().toUpperCase() : '';

            try {
                const res = await fetch(`${API_BASE}/rfid/cards`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ uid, bn }),
                });
                const json = await res.json();
                if (json.success) {
                    showToast(`BN: ${bn} berhasil didaftarkan! ✅`);
                    if (uidEl) uidEl.value = '';
                    if (bnEl) bnEl.value = '';
                    const nc = frm.querySelector('#inputNoCard'); if (nc) nc.value = '';
                    loadRegisterCardTable();
                    loadStats();
                } else {
                    const errors = json.errors ? Object.values(json.errors).flat().join(', ') : json.message;
                    showToast(errors, 'error');
                }
            } catch (err) {
                showToast('Gagal mendaftarkan card!', 'error');
            }
        }

        // ===== POLLING REGISTER SCANS =====
        async function pollRegisterScan() {
            try {
                const res = await fetch(`${API_BASE}/rfid/register-scan`);
                const json = await res.json();
                if (json.success && json.uid) {
                    // Fill whichever UID input is currently in an active/visible form
                    const targets = ['inputUid', 'inputUidQ'];
                    targets.forEach(id => {
                        const el = document.getElementById(id);
                        if (el && el.value !== json.uid) el.value = json.uid;
                    });
                    showToast(`UID ${json.uid} dideteksi dari reader!`, 'success');
                    fetch(`${API_BASE}/rfid/register-scan`, { method: 'DELETE' });
                }
            } catch (e) { /* silent */ }
        }

        // ===== DELETE CARD =====
        async function deleteCard(id, nama) {
            if (!confirm(`Hapus card "${nama}"?`)) return;

            try {
                const res = await fetch(`${API_BASE}/rfid/cards/${id}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json' },
                });

                const json = await res.json();
                if (json.success) {
                    showToast(`Card "${nama}" berhasil dihapus!`);
                    loadRegisterCardTable();
                    loadStats();
                }
            } catch (e) {
                showToast('Gagal menghapus card!', 'error');
            }
        }

        // ===== READER POWER =====
        function updatePowerDisplay(value, suffix = '') {
            const displayEl = document.getElementById('powerDisplay' + suffix);
            if (displayEl) displayEl.textContent = value;

            let distance = '';
            if (value <= 5) distance = '~10 cm';
            else if (value <= 10) distance = '~0.5-1 m';
            else if (value <= 15) distance = '~1-2 m';
            else if (value <= 20) distance = '~2-4 m';
            else if (value <= 25) distance = '~4-6 m';
            else distance = '~6-8 m';

            const distEl = document.getElementById('distanceEstimate' + suffix);
            if (distEl) distEl.textContent = distance;
        }

        async function loadReaderStatus() {
            try {
                const res = await fetch(`${API_BASE}/rfid/reader/status`);
                const json = await res.json();
                const isOnline = json.success && json.data.online;
                const statusHtml = isOnline
                    ? '<span class="reader-status-dot online"></span>Online'
                    : '<span class="reader-status-dot offline"></span>Offline';

                const el = document.getElementById('readerStatusText');
                if (el) el.innerHTML = statusHtml;

                if (isOnline) {
                    const d = json.data;
                    const setPairs = [
                        ['readerIp', d.ip || '--'],
                        ['readerPort', d.port || '--'],
                        ['readerFirmware', d.version ? 'v' + d.version : '--'],
                        ['readerCurrentPower', d.power !== undefined ? d.power : '--'],
                    ];
                    setPairs.forEach(([id, val]) => {
                        const el = document.getElementById(id);
                        if (el) el.textContent = val;
                    });
                    if (d.power !== undefined) {
                        const sl = document.getElementById('powerSlider');
                        if (sl) { sl.value = d.power; updatePowerDisplay(d.power); }
                    }
                    // Pre-fill config form with current settings
                    const cfgIp = document.getElementById('cfgIp');
                    const cfgPort = document.getElementById('cfgPort');
                    const cfgPower = document.getElementById('cfgPower');
                    if (cfgIp && d.ip) cfgIp.value = d.ip;
                    if (cfgPort && d.port) cfgPort.value = d.port;
                    if (cfgPower && d.power !== undefined) cfgPower.value = d.power;
                }
            } catch (e) {
                console.error('Reader status error:', e);
                ['readerStatusText'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.innerHTML = '<span class="reader-status-dot offline"></span>Error';
                });
            }
        }
        
        async function loadScanReaderStatus() {
            try {
                const res = await fetch(`${API_BASE}/rfid/scan-reader/status`);
                const json = await res.json();
                const isOnline = json.success && json.data.online;
                const statusHtml = isOnline
                    ? '<span class="reader-status-dot online"></span>Online'
                    : '<span class="reader-status-dot offline"></span>Offline';

                const el = document.getElementById('readerStatusTextQ');
                if (el) el.innerHTML = statusHtml;

                if (isOnline) {
                    const d = json.data;
                    const setPairs = [
                        ['readerIpQ', d.ip || '--'],
                        ['readerPortQ', d.port || '--'],
                        ['readerFirmwareQ', d.version ? 'v' + d.version : '--'],
                        ['readerCurrentPowerQ', d.power !== undefined ? d.power : '--'],
                    ];
                    
                    setPairs.forEach(([id, val]) => {
                        const el = document.getElementById(id);
                        if (el) el.textContent = val;
                    });
                    
                    
                    if (d.power !== undefined) {
                        const slQ = document.getElementById('powerSliderQ');
                        if (slQ) { slQ.value = d.power; updatePowerDisplay(d.power, 'Q'); }
                    }
                }
                
                // --- UPDATE DIAGNOSTICS UI ---
                const diagEl = document.getElementById('diagnosticData');
                if (diagEl) {
                    diagEl.textContent = JSON.stringify(json.data || json, null, 2);
                    if (!isOnline) {
                        diagEl.textContent += '\n\n// ALARM: main.py tidak berjalan atau tidak dapat mengirim heartbeat TCP ke Laravel. Pastikan script python menyala dan dapat mengakses API.';
                    }
                }
                // -----------------------------
                
            } catch (e) {
                console.error('Scan Reader status error:', e);
                const el = document.getElementById('readerStatusTextQ');
                if (el) el.innerHTML = '<span class="reader-status-dot offline"></span>Error';
                
                const diagEl = document.getElementById('diagnosticData');
                if (diagEl) diagEl.textContent = 'Terjadi kesalahan sistem JS saat parsing status:\n' + e.message;
            }
            
            // Walaupun online/offline, load config yang tersimpan di server
            try {
                const cfgRes = await fetch(`${API_BASE}/rfid/scan-reader/config`);
                const cfgJson = await cfgRes.json();
                if (cfgJson.success && cfgJson.data) {
                    const d = cfgJson.data;
                    const cfgIp = document.getElementById('scanCfgIp');
                    const cfgPort = document.getElementById('scanCfgPort');
                    const cfgTime = document.getElementById('scanCfgTime');
                    const cfgPower = document.getElementById('scanCfgPower');
                    if (cfgIp) cfgIp.value = d.ip;
                    if (cfgPort) cfgPort.value = d.port;
                    if (cfgTime) cfgTime.value = d.time;
                    if (cfgPower) cfgPower.value = d.power;
                    
                    // --- APPEND CONFIG TO DIAGNOSTICS ---
                    const diagEl = document.getElementById('diagnosticData');
                    if (diagEl && diagEl.textContent) {
                         diagEl.textContent += '\n\n=== KONFIGURASI TERSIMPAN (CACHE SERVER) ===\n' + JSON.stringify(d, null, 2);
                    }
                    // ------------------------------------
                }
            } catch (e) {}
        }

        async function saveScanConfig() {
            const ip = document.getElementById('scanCfgIp').value.trim();
            const port = parseInt(document.getElementById('scanCfgPort').value);
            const time = parseInt(document.getElementById('scanCfgTime').value) || 500;
            const power = parseInt(document.getElementById('scanCfgPower').value);
            
            const btn = document.getElementById('btnSaveScanCfg');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            
            try {
                const res = await fetch(`${API_BASE}/rfid/scan-reader/config`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ ip, port, time, power }),
                });
                const json = await res.json();
                if (json.success) {
                    showToast('Konfigurasi Scan Reader berhasil disimpan!', 'success');
                    
                    // Update slider/display power juga
                    const slQ = document.getElementById('powerSliderQ');
                    if (slQ) { slQ.value = power; updatePowerDisplay(power, 'Q'); }
                } else {
                    const msg = json.message || json.errors || 'Gagal menyimpan konfigurasi';
                    showToast(msg, 'error');
                }
            } catch (e) {
                showToast('Kesalahan sistem', 'error');
            }
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Simpan Setting';
        }

        async function pollCommandResult(cmdId, maxSeconds = 10, endpoint = '/rfid/reader/command-result/') {
            for (let i = 0; i < maxSeconds * 2; i++) {
                await new Promise(r => setTimeout(r, 500));
                try {
                    const res = await fetch(`${API_BASE}${endpoint}${cmdId}`);
                    const json = await res.json();
                    if (json.success || json.status !== 'pending') {
                        return json;
                    }
                } catch (e) {
                    console.error('Polling error:', e);
                }
            }
            return { success: false, error: 'Command timeout' };
        }

        async function applyPower(suffix = '') {
            const power = parseInt(document.getElementById('powerSlider' + suffix).value);
            const btn = document.getElementById('btnApplyPower' + suffix);
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            const endpointPower = suffix === 'Q' ? '/rfid/scan-reader/power' : '/rfid/reader/power';
            const endpointStatus = suffix === 'Q' ? '/rfid/scan-reader/command-result/' : '/rfid/reader/command-result/';

            try {
                // 1. Queue command
                const res = await fetch(`${API_BASE}${endpointPower}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ power }),
                });
                const initJson = await res.json();

                if (!initJson.success || !initJson.cmd_id) {
                    showToast(initJson.message || 'Gagal mengirim perintah', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check"></i> Terapkan Power';
                    return;
                }

                // 2. Poll for result
                const json = await pollCommandResult(initJson.cmd_id, 10, endpointStatus);

                if (json.success) {
                    showToast(`Power berhasil diubah ke ${power}`);
                    const cpEl = suffix === 'Q' ? 'readerCurrentPowerQ' : 'readerCurrentPower';
                    const cp = document.getElementById(cpEl);
                    if (cp) cp.textContent = power;
                } else {
                    const msg = json.message || json.error || 'Gagal mengubah power';
                    showToast(msg, 'error');
                }
            } catch (e) {
                showToast('Kesalahan sistem: ' + e.message, 'error');
            }

            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Terapkan Power';
        }

        // ===== LOAD REGISTER CARD TABLE =====
        function formatWIB(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            if (isNaN(d)) return dateStr;
            return d.toLocaleString('id-ID', { timeZone: 'Asia/Jakarta', day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
        }
        function loadRegisterCardTable() {
            fetch(`${API_BASE}/rfid/cards`).then(r => r.json()).then(json => {
                const tbody = document.getElementById('registerCardBody');
                const countEl = document.getElementById('cardCount');
                if (!json.success || !json.data.length) {
                    tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-id-card"></i><p>Belum ada card terdaftar</p></div></td></tr>';
                    if (countEl) countEl.textContent = '0 cards';
                    return;
                }
                if (countEl) countEl.textContent = json.data.length + ' cards';
                tbody.innerHTML = json.data.map((c, i) => `
                    <tr>
                        <td>${i + 1}</td>
                        <td class="uid-cell">${c.uid}</td>
                        <td>${c.bn || '-'}</td>
                        <td><span class="badge badge-${c.status}">${c.status === 'registered' ? '✓ Aktif' : '✗ Non-aktif'}</span></td>
                        <td style="color:var(--text-secondary);font-size:13px;">${formatWIB(c.created_at)}</td>
                        <td>
                            <button class="btn btn-danger btn-sm" onclick="deleteCard(${c.id},'${c.bn}')"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>`).join('');
            }).catch(() => { });
        }

        // ===== AUDIT TRAIL: Register (ambil log pendaftaran card dari /rfid/cards) =====
        async function loadAuditReg() {
            const tbody = document.getElementById('auditRegBody');
            tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat data...</p></div></td></tr>';
            try {
                const res = await fetch(`${API_BASE}/rfid/cards`);
                const json = await res.json();
                if (!json.success || !json.data.length) {
                    tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-scroll"></i><p>Belum ada log aktivitas registrasi</p></div></td></tr>';
                    return;
                }
                tbody.innerHTML = json.data.map((c, i) => `
                    <tr>
                        <td>${i + 1}</td>
                        <td><span class="badge" style="background:rgba(34,197,94,.15);color:#22c55e;border:1px solid rgba(34,197,94,.3);">Register</span></td>
                        <td class="uid-cell">${c.uid}</td>
                        <td>${c.bn || '-'}</td>
                        <td>Administrator</td>
                        <td style="color:var(--text-secondary);font-size:13px;">${formatWIB(c.created_at || c.registered_at)}</td>
                    </tr>`).join('');
            } catch (e) {
                tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Gagal memuat data</p></div></td></tr>';
            }
        }

        // ===== AUDIT TRAIL: Scan (ambil semua scan log dari /rfid/logs) =====
        async function loadAuditScan() {
            const tbody = document.getElementById('auditScanBody');
            tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat data...</p></div></td></tr>';
            try {
                const res = await fetch(`${API_BASE}/rfid/logs?limit=100`);
                const json = await res.json();
                if (!json.success || !json.data.length) {
                    tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-scroll"></i><p>Belum ada log scan</p></div></td></tr>';
                    return;
                }
                tbody.innerHTML = json.data.map((l, i) => `
                    <tr>
                        <td>${l.id}</td>
                        <td class="uid-cell">${l.uid}</td>
                        <td>${l.bn || '-'}</td>
                        <td><span class="badge badge-${l.status}">${l.status === 'registered' ? '✓ Terdaftar' : '✗ Tidak Terdaftar'}</span></td>
                        <td style="color:var(--text-secondary);font-size:13px;">${formatWIB(l.scanned_at)}</td>
                    </tr>`).join('');
            } catch (e) {
                tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Gagal memuat data</p></div></td></tr>';
            }
        }

        // ===== AUDIT TRAIL UMUM (logs + registrasi) =====
        async function loadAuditUmum() {
            const tbody = document.getElementById('auditUmumBody');
            const filter = document.getElementById('auditFilter') ? document.getElementById('auditFilter').value : '';
            tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memuat data...</p></div></td></tr>';
            try {
                // Fetch logs + cards concurrently
                const [logsRes, cardsRes] = await Promise.all([
                    fetch(`${API_BASE}/rfid/logs?limit=100`),
                    fetch(`${API_BASE}/rfid/cards`)
                ]);
                const logsJson = await logsRes.json();
                const cardsJson = await cardsRes.json();

                const rows = [];
                // Scan events
                if (logsJson.success) {
                    logsJson.data.forEach(l => rows.push({
                        modul: 'Scan Reader', aksi: 'Scan',
                        detail: `UID: ${l.uid} | BN: ${l.bn || '-'}`,
                        operator: 'System', waktu: l.scanned_at, type: 'scan',
                        status: l.status
                    }));
                }
                // Register events
                if (cardsJson.success) {
                    cardsJson.data.forEach(c => rows.push({
                        modul: 'Register Reader', aksi: 'Register Card',
                        detail: `UID: ${c.uid} | BN: ${c.bn || '-'}`,
                        operator: 'Administrator', waktu: c.created_at || '-', type: 'register',
                        status: 'registered'
                    }));
                }

                // Filter
                const filtered = filter ? rows.filter(r => r.type === filter || r.aksi.toLowerCase().includes(filter)) : rows;

                if (!filtered.length) {
                    tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-clipboard-list"></i><p>Tidak ada data</p></div></td></tr>';
                    return;
                }

                tbody.innerHTML = filtered.map((r, i) => `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${r.modul}</td>
                        <td>${r.aksi}</td>
                        <td style="font-size:12px;color:var(--text-muted);">${r.detail}</td>
                        <td>${r.operator}</td>
                        <td style="color:var(--text-secondary);font-size:13px;">${formatWIB(r.waktu)}</td>
                    </tr>`).join('');
            } catch (e) {
                tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Gagal memuat data</p></div></td></tr>';
            }
        }

        // ===== INIT =====
        document.addEventListener('DOMContentLoaded', function () {
            loadStats();
            loadLogs();
            // Reader status hanya load sekali saat startup (tidak di-interval agar tidak lag)
            loadReaderStatus();

            // Polling log scan baru setiap 3 detik
            setInterval(loadLogs, 3000);

            // Polling register scan setiap 3 detik (dari register_reader.py)
            setInterval(pollRegisterScan, 3000);

            // Refresh stats setiap 15 detik
            setInterval(loadStats, 15000);
        });
    </script>
</body>

</html>