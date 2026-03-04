<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RFID System — Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #0f0f23;
            --bg-secondary: #1a1a3e;
            --bg-card: #1e1e45;
            --bg-card-hover: #252560;
            --accent-blue: #4f8cff;
            --accent-cyan: #00d4ff;
            --accent-green: #00e676;
            --accent-red: #ff5252;
            --accent-orange: #ffab40;
            --accent-purple: #b47aff;
            --text-primary: #f0f0f0;
            --text-secondary: #a0a0c0;
            --text-muted: #6a6a9a;
            --border: #2a2a5a;
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            --shadow-glow-blue: 0 0 20px rgba(79, 140, 255, 0.3);
            --shadow-glow-green: 0 0 20px rgba(0, 230, 118, 0.3);
            --shadow-glow-red: 0 0 20px rgba(255, 82, 82, 0.3);
            --radius: 16px;
            --radius-sm: 10px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(ellipse at 20% 50%, rgba(79, 140, 255, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(0, 212, 255, 0.06) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 80%, rgba(180, 122, 255, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        /* Header */
        .header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(15, 15, 35, 0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-logo .icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-cyan));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #fff;
            box-shadow: var(--shadow-glow-blue);
        }

        .header-logo h1 {
            font-size: 22px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-logo span {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 400;
        }

        .header-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text-secondary);
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--accent-green);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(0, 230, 118, 0.5);
            }

            50% {
                box-shadow: 0 0 0 8px rgba(0, 230, 118, 0);
            }
        }

        /* Main Content */
        .container {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px 32px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            transition: all 0.3s ease;
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
            border-radius: var(--radius) var(--radius) 0 0;
        }

        .stat-card:nth-child(1)::before {
            background: linear-gradient(90deg, var(--accent-blue), var(--accent-cyan));
        }

        .stat-card:nth-child(2)::before {
            background: linear-gradient(90deg, var(--accent-green), #00c853);
        }

        .stat-card:nth-child(3)::before {
            background: linear-gradient(90deg, var(--accent-orange), #ff6d00);
        }

        .stat-card:nth-child(4)::before {
            background: linear-gradient(90deg, var(--accent-red), #d50000);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            border-color: var(--accent-blue);
            box-shadow: var(--shadow);
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 16px;
        }

        .stat-card:nth-child(1) .stat-icon {
            background: rgba(79, 140, 255, 0.15);
            color: var(--accent-blue);
        }

        .stat-card:nth-child(2) .stat-icon {
            background: rgba(0, 230, 118, 0.15);
            color: var(--accent-green);
        }

        .stat-card:nth-child(3) .stat-icon {
            background: rgba(255, 171, 64, 0.15);
            color: var(--accent-orange);
        }

        .stat-card:nth-child(4) .stat-icon {
            background: rgba(255, 82, 82, 0.15);
            color: var(--accent-red);
        }

        .stat-card .stat-value {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .stat-card .stat-label {
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* Sections Layout */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 24px;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Card Component */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-header h2 {
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-header h2 i {
            color: var(--accent-blue);
        }

        .card-body {
            padding: 20px 24px;
        }

        /* Live Scan Alert */
        .scan-alert {
            display: none;
            background: linear-gradient(135deg, rgba(0, 230, 118, 0.1), rgba(0, 212, 255, 0.1));
            border: 1px solid rgba(0, 230, 118, 0.3);
            border-radius: var(--radius);
            padding: 20px 24px;
            margin-bottom: 24px;
            animation: fadeInScale 0.4s ease;
        }

        .scan-alert.show {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .scan-alert.unregistered {
            background: linear-gradient(135deg, rgba(255, 82, 82, 0.1), rgba(255, 171, 64, 0.1));
            border-color: rgba(255, 82, 82, 0.3);
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .scan-alert .alert-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: rgba(0, 230, 118, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: var(--accent-green);
            flex-shrink: 0;
        }

        .scan-alert.unregistered .alert-icon {
            background: rgba(255, 82, 82, 0.2);
            color: var(--accent-red);
        }

        .scan-alert .alert-info h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .scan-alert .alert-info p {
            font-size: 13px;
            color: var(--text-secondary);
        }

        /* Log Table */
        .log-table {
            width: 100%;
            border-collapse: collapse;
        }

        .log-table thead th {
            padding: 12px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
        }

        .log-table tbody tr {
            border-bottom: 1px solid rgba(42, 42, 90, 0.5);
            transition: background 0.2s;
        }

        .log-table tbody tr:hover {
            background: rgba(79, 140, 255, 0.05);
        }

        .log-table tbody tr.new-row {
            animation: highlightRow 2s ease;
        }

        @keyframes highlightRow {
            0% {
                background: rgba(0, 230, 118, 0.15);
            }

            100% {
                background: transparent;
            }
        }

        .log-table tbody td {
            padding: 14px 16px;
            font-size: 14px;
        }

        .log-table .uid-cell {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: var(--accent-cyan);
            font-size: 13px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-registered {
            background: rgba(0, 230, 118, 0.15);
            color: var(--accent-green);
            border: 1px solid rgba(0, 230, 118, 0.3);
        }

        .badge-unregistered {
            background: rgba(255, 82, 82, 0.15);
            color: var(--accent-red);
            border: 1px solid rgba(255, 82, 82, 0.3);
        }

        .badge-active {
            background: rgba(0, 230, 118, 0.15);
            color: var(--accent-green);
        }

        .badge-inactive {
            background: rgba(255, 82, 82, 0.15);
            color: var(--accent-red);
        }

        /* Card Management */
        .card-form {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(79, 140, 255, 0.15);
        }

        .form-group input::placeholder {
            color: var(--text-muted);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-cyan));
            color: #fff;
        }

        .btn-primary:hover {
            box-shadow: var(--shadow-glow-blue);
            transform: translateY(-2px);
        }

        .btn-danger {
            background: rgba(255, 82, 82, 0.15);
            color: var(--accent-red);
            border: 1px solid rgba(255, 82, 82, 0.3);
        }

        .btn-danger:hover {
            background: rgba(255, 82, 82, 0.25);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 8px;
        }

        /* Card List */
        .card-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .card-list::-webkit-scrollbar {
            width: 6px;
        }

        .card-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .card-list::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 3px;
        }

        .card-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 0;
            border-bottom: 1px solid rgba(42, 42, 90, 0.3);
        }

        .card-item:last-child {
            border-bottom: none;
        }

        .card-item-info h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .card-item-info p {
            font-size: 12px;
            color: var(--text-muted);
            font-family: 'Courier New', monospace;
        }

        .card-item-actions {
            display: flex;
            gap: 8px;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 12px;
            opacity: 0.3;
        }

        .empty-state p {
            font-size: 14px;
        }

        /* Notification Toast */
        .toast-container {
            position: fixed;
            top: 80px;
            right: 24px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .toast {
            padding: 14px 20px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 500;
            box-shadow: var(--shadow);
            animation: slideIn 0.3s ease;
            min-width: 280px;
        }

        .toast-success {
            background: rgba(0, 230, 118, 0.15);
            border: 1px solid rgba(0, 230, 118, 0.3);
            color: var(--accent-green);
        }

        .toast-error {
            background: rgba(255, 82, 82, 0.15);
            border: 1px solid rgba(255, 82, 82, 0.3);
            color: var(--accent-red);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(100px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Log scroll */
        .log-scroll {
            max-height: 500px;
            overflow-y: auto;
        }

        .log-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .log-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .log-scroll::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 3px;
        }

        /* Reader Settings */
        .power-slider-container {
            padding: 8px 0;
        }

        .power-slider-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .power-value {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .power-label {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .power-slider {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 8px;
            border-radius: 4px;
            background: linear-gradient(90deg, var(--accent-green), var(--accent-orange), var(--accent-red));
            outline: none;
            opacity: 0.9;
            transition: opacity 0.2s;
        }

        .power-slider:hover {
            opacity: 1;
        }

        .power-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #fff;
            cursor: pointer;
            border: 3px solid var(--accent-blue);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            transition: all 0.2s;
        }

        .power-slider::-webkit-slider-thumb:hover {
            transform: scale(1.15);
            box-shadow: 0 2px 12px rgba(79, 140, 255, 0.5);
        }

        .power-slider::-moz-range-thumb {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #fff;
            cursor: pointer;
            border: 3px solid var(--accent-blue);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .power-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            font-size: 11px;
            color: var(--text-muted);
        }

        .power-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }

        .power-info-item {
            background: var(--bg-secondary);
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            text-align: center;
        }

        .power-info-item .info-label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 4px;
        }

        .power-info-item .info-value {
            font-size: 14px;
            font-weight: 600;
        }

        .reader-status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .reader-status-dot.online {
            background: var(--accent-green);
        }

        .reader-status-dot.offline {
            background: var(--accent-red);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header {
                padding: 12px 16px;
            }

            .container {
                padding: 16px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .stat-card .stat-value {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="header">
        <div class="header-logo">
            <div class="icon"><i class="fas fa-wifi"></i></div>
            <div>
                <h1>RFID System</h1>
                <span>HW-VX6330K v2 — Real-time Monitoring</span>
            </div>
        </div>
        <div class="header-status">
            <div class="status-dot"></div>
            <span>System Active</span>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <div class="container">

        <!-- Scan Alert -->
        <div class="scan-alert" id="scanAlert">
            <div class="alert-icon" id="alertIcon"><i class="fas fa-check-circle"></i></div>
            <div class="alert-info">
                <h3 id="alertTitle">Card Terbaca!</h3>
                <p id="alertDetail">UID: --- | Waktu: ---</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-id-card"></i></div>
                <div class="stat-value" id="statTotalCards">0</div>
                <div class="stat-label">Card Terdaftar</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-signal"></i></div>
                <div class="stat-value" id="statTotalScans">0</div>
                <div class="stat-label">Scan Hari Ini</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                <div class="stat-value" id="statRegistered">0</div>
                <div class="stat-label">Scan Terdaftar</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-user-xmark"></i></div>
                <div class="stat-value" id="statUnregistered">0</div>
                <div class="stat-label">Scan Tidak Terdaftar</div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-grid">

            <!-- Left: Live Logs -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-list-ul"></i> Live Scan Log</h2>
                    <span style="font-size:12px;color:var(--text-muted);">Auto-refresh setiap 2 detik</span>
                </div>
                <div class="card-body" style="padding:0;">
                    <div class="log-scroll">
                        <table class="log-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>UID</th>
                                    <th>Nama</th>
                                    <th>Status</th>
                                    <th>Waktu Scan</th>
                                </tr>
                            </thead>
                            <tbody id="logTableBody">
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <i class="fas fa-wifi"></i>
                                            <p>Menunggu scan card RFID...</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Card Management -->
            <div>

                <!-- Register Card Form -->
                <div class="card" style="margin-bottom:20px;">
                    <div class="card-header">
                        <h2><i class="fas fa-plus-circle"></i> Register Card Baru</h2>
                    </div>
                    <div class="card-body">
                        <form class="card-form" id="registerForm" onsubmit="registerCard(event)">
                            <div class="form-group">
                                <label>UID Kartu</label>
                                <input type="text" id="inputUid" placeholder="Contoh: 04A1B2C3D4E5" required>
                            </div>
                            <div class="form-group">
                                <label>Nama Pemilik</label>
                                <input type="text" id="inputNama" placeholder="Masukkan nama pemilik kartu" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Register Card
                            </button>
                        </form>
                        <p style="margin-top:12px;font-size:12px;color:var(--text-muted);">
                            <i class="fas fa-info-circle"></i> Tip: Tempelkan card ke reader untuk melihat UID-nya di
                            Live Log, lalu copy-paste UID untuk registrasi.
                        </p>
                    </div>
                </div>

                <!-- Reader Settings -->
                <div class="card" style="margin-bottom:20px;">
                    <div class="card-header">
                        <h2><i class="fas fa-sliders-h"></i> Pengaturan Reader</h2>
                        <span style="font-size:12px;" id="readerStatusText">
                            <span class="reader-status-dot offline"></span>Checking...
                        </span>
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
                            <div class="power-labels">
                                <span>0 (Dekat)</span>
                                <span>15</span>
                                <span>30 (Jauh)</span>
                            </div>
                        </div>
                        <button class="btn btn-primary" style="width:100%;margin-top:16px;" onclick="applyPower()"
                            id="btnApplyPower">
                            <i class="fas fa-check"></i> Terapkan Power
                        </button>
                        <div class="power-info" id="readerInfoGrid">
                            <div class="power-info-item">
                                <div class="info-label">IP Address</div>
                                <div class="info-value" id="readerIp">--</div>
                            </div>
                            <div class="power-info-item">
                                <div class="info-label">Port</div>
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

                <!-- Card List -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-id-card"></i> Card Terdaftar</h2>
                        <span style="font-size:12px;color:var(--text-muted);" id="cardCount">0 cards</span>
                    </div>
                    <div class="card-body" style="padding: 8px 24px;">
                        <div class="card-list" id="cardList">
                            <div class="empty-state">
                                <i class="fas fa-id-card"></i>
                                <p>Belum ada card terdaftar</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

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
        function showScanAlert(data) {
            const alert = document.getElementById('scanAlert');
            const icon = document.getElementById('alertIcon');
            const title = document.getElementById('alertTitle');
            const detail = document.getElementById('alertDetail');

            if (data.status === 'registered') {
                alert.className = 'scan-alert show';
                icon.innerHTML = '<i class="fas fa-check-circle"></i>';
                title.textContent = `✅ ${data.card_name}`;
            } else {
                alert.className = 'scan-alert show unregistered';
                icon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
                title.textContent = '⚠️ Card Tidak Terdaftar';
            }
            detail.textContent = `UID: ${data.uid} | Waktu: ${data.scanned_at}`;

            clearTimeout(alertTimeout);
            alertTimeout = setTimeout(() => {
                alert.classList.remove('show');
            }, 5000);
        }

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
                const res = await fetch(`${API_BASE}/rfid/logs?last_id=${lastLogId}&limit=50`);
                const json = await res.json();
                if (json.success && json.data.length > 0) {
                    const tbody = document.getElementById('logTableBody');

                    // Remove empty state
                    const emptyState = tbody.querySelector('.empty-state');
                    if (emptyState) {
                        emptyState.closest('tr').remove();
                    }

                    // Add new rows at top
                    json.data.forEach(log => {
                        const tr = document.createElement('tr');
                        tr.className = 'new-row';
                        tr.innerHTML = `
                        <td style="color:var(--text-muted);font-size:12px;">${log.id}</td>
                        <td class="uid-cell">${log.uid}</td>
                        <td>${log.card_name}</td>
                        <td><span class="badge badge-${log.status}">${log.status === 'registered' ? '✓ Terdaftar' : '✗ Tidak Terdaftar'}</span></td>
                        <td style="color:var(--text-secondary);font-size:13px;">${log.scanned_at}</td>
                    `;
                        tbody.insertBefore(tr, tbody.firstChild);

                        // Show scan alert for newest
                        showScanAlert(log);
                    });

                    lastLogId = json.latest_id;
                    loadStats(); // Refresh stats when new logs come
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
                            <h4>${card.nama} <span class="badge badge-${card.status}" style="margin-left:8px;">${card.status}</span></h4>
                            <p>${card.uid}</p>
                        </div>
                        <div class="card-item-actions">
                            <button class="btn btn-danger btn-sm" onclick="deleteCard(${card.id}, '${card.nama}')">
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

        // ===== REGISTER CARD =====
        async function registerCard(e) {
            e.preventDefault();
            const uid = document.getElementById('inputUid').value.trim();
            const nama = document.getElementById('inputNama').value.trim();

            try {
                const res = await fetch(`${API_BASE}/rfid/cards`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ uid, nama }),
                });

                const json = await res.json();
                if (json.success) {
                    showToast(`Card ${nama} berhasil didaftarkan!`);
                    document.getElementById('inputUid').value = '';
                    document.getElementById('inputNama').value = '';
                    loadCards();
                    loadStats();
                } else {
                    const errors = json.errors ? Object.values(json.errors).flat().join(', ') : json.message;
                    showToast(errors, 'error');
                }
            } catch (e) {
                showToast('Gagal mendaftarkan card!', 'error');
            }
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
                    loadCards();
                    loadStats();
                }
            } catch (e) {
                showToast('Gagal menghapus card!', 'error');
            }
        }

        // ===== READER POWER =====
        function updatePowerDisplay(value) {
            document.getElementById('powerDisplay').textContent = value;
            let distance = '';
            if (value <= 5) distance = '~10 cm';
            else if (value <= 10) distance = '~0.5-1 m';
            else if (value <= 15) distance = '~1-2 m';
            else if (value <= 20) distance = '~2-4 m';
            else if (value <= 25) distance = '~4-6 m';
            else distance = '~6-8 m';
            document.getElementById('distanceEstimate').textContent = distance;
        }

        async function loadReaderStatus() {
            try {
                const res = await fetch(`${API_BASE}/rfid/reader/status`);
                const json = await res.json();
                const statusEl = document.getElementById('readerStatusText');
                if (json.success && json.data.online) {
                    statusEl.innerHTML = '<span class="reader-status-dot online"></span>Online';
                    document.getElementById('readerIp').textContent = json.data.ip || '--';
                    document.getElementById('readerPort').textContent = json.data.port || '--';
                    if (json.data.version) {
                        document.getElementById('readerFirmware').textContent = 'v' + json.data.version;
                    }
                    if (json.data.power !== undefined) {
                        const power = json.data.power;
                        document.getElementById('readerCurrentPower').textContent = power;
                        document.getElementById('powerSlider').value = power;
                        updatePowerDisplay(power);
                    }
                } else {
                    statusEl.innerHTML = '<span class="reader-status-dot offline"></span>Offline';
                }
            } catch (e) {
                console.error('Reader status error:', e);
                document.getElementById('readerStatusText').innerHTML = '<span class="reader-status-dot offline"></span>Error';
            }
        }

        async function applyPower() {
            const power = parseInt(document.getElementById('powerSlider').value);
            const btn = document.getElementById('btnApplyPower');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

            try {
                const res = await fetch(`${API_BASE}/rfid/reader/power`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ power }),
                });
                const json = await res.json();
                if (json.success) {
                    showToast(`Power berhasil diubah ke ${power}`);
                    document.getElementById('readerCurrentPower').textContent = power;
                } else {
                    showToast(json.data?.message || 'Gagal mengubah power', 'error');
                }
            } catch (e) {
                showToast('Gagal terhubung ke reader', 'error');
            }

            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Terapkan Power';
        }

        // ===== INIT =====
        document.addEventListener('DOMContentLoaded', function () {
            loadStats();
            loadLogs();
            loadCards();
            loadReaderStatus();

            // Polling setiap 2 detik untuk log baru
            setInterval(loadLogs, 2000);

            // Refresh stats setiap 10 detik
            setInterval(loadStats, 10000);

            // Refresh reader status setiap 30 detik
            setInterval(loadReaderStatus, 30000);
        });
    </script>
</body>

</html>