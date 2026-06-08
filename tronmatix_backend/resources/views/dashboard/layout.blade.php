<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <script>document.documentElement.setAttribute('data-theme',localStorage.getItem('tronmatix_theme')||'dark');</script>

    {{-- English font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet" />

    {{-- Khmer font — Kdam Thmor Pro via Google Fonts CDN --}}
    <link href="https://fonts.googleapis.com/css2?family=Kdam+Thmor+Pro&display=swap" rel="stylesheet" />

    <style>
        /* ── i18n typography vars ────────────────────────────────────────────── */
        :root {
            --font-en: 'Rajdhani', sans-serif;
            --font-kh: 'Kdam Thmor Pro', sans-serif;
            --lh-en: 1.5;
            --lh-kh: 1.5;   /* Khmer needs more line height for diacritics */

            /* ── English type scale (default) ─────────────────────────────────── */
            --text-nav:  20px;   /* nav items  */
            --text-xs:   12px;   /* badges, faint labels          */
            --text-sm:   14px;   /* table cells, secondary text   */
            --text-base: 16px;   /* body, buttons      */
            --text-md:   18px;   /* card titles, topbar items     */
            --text-lg:   20px;   /* topbar page title             */
            --text-xl:   22px;   /* section headings              */
            --text-2xl:  26px;   /* prominent titles              */

            --title-size: var(--text-md);
            --heading-size: var(--text-xl);
            --label-size:   var(--text-xs);
            --icon-size:    20px;
        }

        /* ── Khmer type scale — slightly larger across the board ─────────────── */
        /* Khmer glyphs are visually smaller at the same px + need stacking room  */
        :lang(km) {
            --text-nav:  16px;
            --text-xs:   12px;
            --text-sm:   14px;
            --text-base: 15px;
            --text-md:   16px;
            --text-lg:   20px;
            --text-xl:   22px;
            --font-size: var(--text-base);
        }

        .dashboard-font {
            font-size: var(--text-base);
            line-height: 1.5;
        }
        :lang(km) .chart-badge{
            font-family: var(--font-kh) !important;
            line-height: var(--lh-kh);
            font-size: var(--text-xs) !important;
            font-weight: 400 !important;
        }
        :lang(km) .card-title{
            font-family: var(--font-kh) !important;
            line-height: var(--lh-kh);
            font-size: var(--text-md) !important;
            font-weight: 400 !important;
        }

        :lang(km) .nav-section-label,
        :lang(km) .nav-item {
            font-family: var(--font-kh) !important;
            line-height: var(--lh-kh);
            font-size: var(--text-nav) !important;
            font-weight: 400 !important;
        }
        :lang(km) thead th,
        :lang(km) tbody td {
            font-family: var(--font-kh) !important;
            line-height: var(--lh-kh);
            font-size: var(--text-md) !important;
            font-weight: 400 !important;
        }
        :lang(km) h1, :lang(km) h2, :lang(km) h3, :lang(km) h4, :lang(km) h5, :lang(km) h6,
        :lang(km) .card-font {
            font-size: var(--text-md) !important;
        }

        :lang(km) .stat-label{
            font-size: var(--text-sm) !important;
            font-weight: 400 !important;
            margin-top: 5px !important;
        }
        :lang(km) .stat-value {
            font-size: clamp(1.4rem, 1.8vw + 1rem, 1.4rem) !important;
        }

        /* Apply Khmer font when lang=km is set on <html> */
        :lang(km) body,
        :lang(km) .btn,
        /* :lang(km) .form-label, */
        :lang(km) .topbar-font,
        :lang(km) .badge,
        :lang(km) .input {
            font-family: var(--font-kh) !important;
            line-height: var(--lh-kh);
            font-size: var(--text-base) !important;
            font-weight: 400 !important;
        }

        :lang(km) span {
            font-size: var(--text-sm);
            font-weight: 400;

        }

        /* Buttons need auto height so Khmer text doesn't clip */
        :lang(km) .btn {
            height: auto;
            min-height: 38px;
            padding-top: 7px;
            padding-bottom: 7px;
        }

        /* Table cells need extra vertical room for diacritics */
        :lang(km) thead th,
        :lang(km) tbody td {
            padding-top: 14px;
            padding-bottom: 14px;
        }

        /* Smooth language swap fade */
        .lang-fading { opacity: 0 !important; transition: opacity 0.15s ease !important; }
    </style>

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* ── Design tokens — Dark theme (default) ───────────────────────────── */
        :root {
            --orange:    #F97316;
            --sidebar-w: 240px;

            /* Surface layers */
            --bg:        #0A0A0A;
            --surface:   #111111;
            --surface-2: #1A1A1A;
            --surface-3: #222222;

            /* Text */
            --text:         #FFFFFF;
            --text-main:    #FFFFFF;
            --text-muted:   rgba(255,255,255,0.55);
            --text-xfaint:  rgba(255,255,255,0.20);

            /* Borders */
            --border:       rgba(255,255,255,0.07);
            --border-faint: rgba(255,255,255,0.04);
            --border-input: rgba(255,255,255,0.10);

            /* Interactive states */
            --hover-bg:     rgba(255,255,255,0.04);
            --active-bg:    rgba(249,115,22,0.08);
            --overlay:      rgba(0,0,0,0.75);

            /* Components */
            --toggle-off:   rgba(255,255,255,0.10);
            --bell-bg:      rgba(255,255,255,0.06);
            --bell-border:  rgba(255,255,255,0.10);
            --dropdown-bg:  #141414;
            --modal-bg:     #141414;
            --chart-badge:  rgba(255,255,255,0.05);
            --scrollbar:    rgba(255,255,255,0.08);
        }

        /* ── Light theme ─────────────────────────────────────────────────────── */
        [data-theme="light"] {
            --bg:        #F1F5F9;
            --surface:   #FFFFFF;
            --surface-2: #F8FAFC;
            --surface-3: #E2E8F0;

            --text:         #0F172A;
            --text-main:    #0F172A;
            --text-muted:   rgba(15,23,42,0.60);
            --text-faint:   rgba(15,23,42,0.40);
            --text-xfaint:  rgba(15,23,42,0.25);

            --border:       rgba(15,23,42,0.08);
            --border-faint: rgba(15,23,42,0.04);
            --border-input: rgba(15,23,42,0.15);

            --hover-bg:     rgba(15,23,42,0.04);
            --active-bg:    rgba(249,115,22,0.08);
            --overlay:      rgba(0,0,0,0.50);

            --toggle-off:   rgba(15,23,42,0.12);
            --bell-bg:      rgba(15,23,42,0.05);
            --bell-border:  rgba(15,23,42,0.12);
            --dropdown-bg:  #FFFFFF;
            --modal-bg:     #FFFFFF;
            --chart-badge:  rgba(15,23,42,0.06);
            --scrollbar:    rgba(15,23,42,0.08);
        }

        body {
            font-family: 'Rajdhani', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            font-size: var(--font-size);
            line-height: 1.6;
            transition: background 0.2s, color 0.2s;
        }

        /* ── Sidebar ──────────────────────────────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 200;
            transition: transform 0.3s ease;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

        .sidebar-logo img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }

        .brand-name {
            font-size: var(--text-md);
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--text);
        }

        .brand-sub {
            font-size: var(--text-xs);
            font-weight: 700;
            letter-spacing: 4px;
            color: var(--orange);
        }

        /* ── Nav ──────────────────────────────────────────────────────────────── */
        nav.sidebar-nav {
            flex: 1;
            padding: 8px 0;
            overflow-y: auto;
        }

        .nav-section-label {
            font-size: var(--text-md);
            color: var(--text-faint);
            padding: 8px 20px 5px;
            text-transform: uppercase;
            font-weight: 700;
            margin-top: 1px;
            letter-spacing: 1px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 22px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: var(--text-nav);
            font-weight: 600;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .nav-item:hover {
            color: var(--text);
            background: var(--hover-bg);
        }

        .nav-item.active {
            color: var(--orange);
            background: var(--active-bg);
            border-left-color: var(--orange);
        }

        .nav-item svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        /* ── Sidebar footer ───────────────────────────────────────────────────── */
        .sidebar-footer {
            padding: 14px 20px;
            border-top: 1px solid var(--border);
            font-size: var(--text-xs);
            color: var(--text-xfaint);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        /* ── Overlay (mobile) ─────────────────────────────────────────────────── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            z-index: 199;
            backdrop-filter: blur(2px);
        }

        .sidebar-overlay.active { display: block; }

        /* ── Main wrapper ─────────────────────────────────────────────────────── */
        .main {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Topbar ───────────────────────────────────────────────────────────── */
        .topbar {
            height: 60px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 25px 5px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        :lang(en) .topbar-title {
            font-family: 'Rajdhani', var(--font-kh), sans-serif !important;
            font-size: var(--text-2xl) !important;
            font-weight: 600 !important;
        }

        :lang(km) .topbar-title {
            font-family: var(--font-kh) !important;
            font-size: var(--text-lg) !important;
            font-weight: 400 !important;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Hamburger */
        .hamburger {
            display: none;
            background: none;
            border: 1px solid var(--border-input);
            border-radius: 8px;
            padding: 7px;
            cursor: pointer;
            color: var(--text-muted);
            transition: all 0.2s;
        }

        .hamburger:hover {
            border-color: var(--orange);
            color: var(--orange);
        }

        .hamburger svg {
            display: block;
            width: 18px;
            height: 18px;
        }

        .topbar-badge {
            background: var(--orange);
            color: #fff;
            font-size: var(--text-md);
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            white-space: nowrap;
        }

        .admin-avatar {
            width: 36px;
            height: 36px;
            background: var(--orange);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: var(--text-sm);
            flex-shrink: 0;
            overflow: hidden;
            border: 2px solid rgba(249,115,22,0.4);
        }

        .admin-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .admin-name {
            font-size: var(--text-sm);
            font-weight: 600;
            color: var(--text-muted);
            white-space: nowrap;
        }

        /* ── Content ──────────────────────────────────────────────────────────── */
        .content {
            padding: 24px;
            flex: 1;
        }

        /* ── Cards ────────────────────────────────────────────────────────────── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }

        .card-font {
            font-size: var(--text-md);
            font-weight: 700;
        }

        .card-body {
            padding: 20px;
        }

        /* ── Stats grid ───────────────────────────────────────────────────────── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 22px 20px;
            min-height: 100px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: border-color 0.2s, transform 0.2s;
        }

        .stat-card:hover {
            border-color: var(--orange);
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 46px;
            height: 46px;
            background: rgba(249,115,22,0.1);
            border: 1px solid rgba(249,115,22,0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-icon svg {
            width: 20px;
            height: 20px;
            stroke: var(--orange);
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
        }

        .stat-label {
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            white-space: nowrap;
        }

        /* ── Chart grid ───────────────────────────────────────────────────────── */
        .chart-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .card-title {
            font-size: var(--text-lg);
            font-weight: 700;
        }

        .chart-badge {
            font-size: var(--text-sm);
            color: rgba(255,255,255,0.35);
            background: rgba(255,255,255,0.05);
            padding: 3px 10px;
            border-radius: 20px;
            letter-spacing: 1px;
        }

        /* ── Table ────────────────────────────────────────────────────────────── */
        .table-wrap { overflow-x: auto;}

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: var(--text-sm);
        }

        thead th {
            text-align: left;
            padding: 12px 16px;
            font-size: var(--text-lg);
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
            font-weight: 600;
        }

        tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-faint);
            color: var(--text-muted);
            vertical-align: middle;
            font-size: var(--text-md);
        }

        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: var(--hover-bg); }

        /* ── Badges ───────────────────────────────────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: var(--title-size);
            font-weight: 500;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .badge-pending   { background: rgba(234,179,8,0.15);  color: #EAB308; border: 1px solid rgba(234,179,8,0.3); }
        .badge-confirmed { background: rgba(34,197,94,0.12);  color: #22C55E; border: 1px solid rgba(34,197,94,0.3); }
        .badge-paid      { background: rgba(34,197,94,0.15);  color: #22C55E; border: 1px solid rgba(34,197,94,0.3); }
        .badge-processing{ background: rgba(59,130,246,0.15); color: #3B82F6; border: 1px solid rgba(59,130,246,0.3); }
        .badge-shipped   { background: rgba(59,130,246,0.15); color: #3B82F6; border: 1px solid rgba(59,130,246,0.3); }
        .badge-delivered { background: rgba(249,115,22,0.15); color: #F97316; border: 1px solid rgba(249,115,22,0.3); }
        .badge-cancelled { background: rgba(239,68,68,0.15);  color: #EF4444; border: 1px solid rgba(239,68,68,0.3); }
        .badge-orange    { background: rgba(249,115,22,0.15); color: var(--orange); border: 1px solid rgba(249,115,22,0.3); }
        .badge-gray      { background: var(--hover-bg); color: var(--text-faint); border: 1px solid var(--border-input); }
        .badge-seller    { background: rgba(16,185,129,0.15);  color: #10b981; border: 1px solid rgba(16,185,129,0.3); }

        /* ── Buttons ──────────────────────────────────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 8px;
            font-family: 'Rajdhani', var(--font-kh), sans-serif;
            font-size: var(--font-size);
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-orange { background: var(--orange); color: #fff; }
        .btn-orange:hover { background: #FB923C; transform: translateY(-1px); }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border-input);
            color: var(--text-muted);
        }
        .btn-outline:hover { border-color: var(--orange); color: var(--orange); }

        .btn-danger {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            color: #EF4444;
        }
        .btn-danger:hover { background: rgba(239,68,68,0.2); }

        .btn-sm { padding: 5px 12px; font-size: var(--font-size); }

        /* ── Forms ────────────────────────────────────────────────────────────── */
        .form-group { margin-bottom: 18px; }

        .form-label {
            display: block;
            font-size: var(--font-size);
            letter-spacing: 1.5px;
            color: var(--text-faint);
            margin-bottom: 7px;
            font-weight: 700;
        }

        .form-control {
            width: 100%;
            background: var(--surface-2);
            border: 1px solid var(--border-input);
            border-radius: 10px;
            padding: 10px 14px;
            color: var(--text-primary);
            font-family: 'Rajdhani', var(--font-kh), sans-serif;
            font-size: var(--font-size);
            font-weight: 500;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-control:focus  { border-color: var(--orange); }
        .form-control::placeholder { color: var(--text-muted); opacity: 0.8; }
        .form-control option { background: var(--surface-2); color: var(--text); }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        /* ── Toggle switch ────────────────────────────────────────────────────── */
        .toggle-wrap { display: flex; align-items: center; gap: 10px; }

        .toggle { position: relative; width: 40px; height: 22px; }
        .toggle input { opacity: 0; width: 0; height: 0; }

        .toggle-slider {
            position: absolute;
            inset: 0;
            background: var(--toggle-off);
            border-radius: 22px;
            cursor: pointer;
            transition: 0.3s;
        }
        .toggle-slider::before {
            content: '';
            position: absolute;
            width: 16px; height: 16px;
            left: 3px; top: 3px;
            background: white;
            border-radius: 50%;
            transition: 0.3s;
        }
        .toggle input:checked + .toggle-slider { background: var(--orange); }
        .toggle input:checked + .toggle-slider::before { transform: translateX(18px); }

        /* ── Alerts ───────────────────────────────────────────────────────────── */
        .alert {
            padding: 12px 18px;
            border-radius: 10px;
            margin-bottom: 0;
            font-size: var(--font-size);
            font-weight: 600;
        }
        .alert-success {
            background: rgba(34,197,94,0.1);
            border: 1px solid rgba(34,197,94,0.3);
            color: #22C55E;
        }
        .alert-error {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            color: #EF4444;
        }

        /* ── Pagination ───────────────────────────────────────────────────────── */
        .pagination {
            display: flex;
            gap: 6px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 8px;
            border-radius: 8px;
            font-size: var(--font-size);
            font-weight: 600;
            text-decoration: none;
            border: 1px solid var(--border-input);
            color: var(--text-muted);
            transition: all 0.2s;
        }
        .pagination a:hover       { border-color: var(--orange); color: var(--orange); }
        .pagination .active       { background: var(--orange); border-color: var(--orange); color: #fff; }

        /* ── Product thumb ────────────────────────────────────────────────────── */
        .product-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            background: var(--hover-bg);
            flex-shrink: 0;
        }

        /* ── Flash animation ──────────────────────────────────────────────────── */
        .flash { animation: flash-in 0.4s ease; }

        @keyframes flash-in {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Profile link ─────────────────────────────────────────────────────── */
        .admin-profile-link {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 10px;
            border: 1px solid transparent;
            transition: all 0.2s;
        }
        .admin-profile-link:hover {
            background: var(--active-bg);
            border-color: rgba(249,115,22,0.3);
        }
        .admin-profile-link:hover .admin-avatar { box-shadow: 0 0 0 2px var(--orange); }
        .admin-profile-link:hover .admin-name   { color: var(--orange); }
        .admin-profile-link.active-profile {
            background: var(--active-bg);
            border-color: rgba(249,115,22,0.3);
        }
        .admin-profile-link.active-profile .admin-name { color: var(--orange); }

        /* ── Sidebar language toggle ─────────────────────────────────────────── */
        .sidebar-lang-toggle {
            padding: 10px 20px;
            border-top: 1px solid var(--border);
            display: none; /* hidden on desktop, shown on mobile/tablet */
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }
        .sidebar-lt-wrap {
            display: inline-flex;
            align-items: center;
            background: var(--bell-bg);
            border: 1.5px solid var(--bell-border);
            border-radius: 999px;
            padding: 3px;
            gap: 2px;
        }
        .sidebar-lt-btn {
            font-size: var(--font-size);
        }

        @media (max-width: 768px) {
            .sidebar-lang-toggle { display: flex; }
            #lang-toggle { display: none !important; }
        }
        @media (max-width: 768px) {
            :lang(km) .topbar-title {
                font-family: var(--font-kh) !important;
                font-size: var(--text-sm) !important;
                font-weight: 400 !important;
            }
        }

        /* ── Language Toggle ──────────────────────────────────────────────────── */
        #lang-toggle {
            display: inline-flex;
            align-items: center;
            background: var(--bell-bg);
            border: 1.5px solid var(--bell-border);
            border-radius: 999px;
            padding: 3px;
            gap: 2px;
            cursor: default;
            flex-shrink: 0;
        }
        .lt-btn {
            padding: 4px 11px;
            border-radius: 999px;
            font-size: 15px;
            font-weight: 700;
            border: none;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'Rajdhani', sans-serif;
        }
        .lt-btn:hover { color: var(--text); }
        .lt-btn.active {
            background: var(--orange);
            color: #fff;
        }
        .lt-btn.km-btn { font-family: var(--font-kh); font-size: 13px; font-weight: 500; }

        /* ══════════════════════════════════════════════════════════════════════
           LIGHT THEME OVERRIDES
        ══════════════════════════════════════════════════════════════════════ */
        [data-theme="light"] body { color: #0F172A; }
        [data-theme="light"] .card { background: #FFFFFF; border-color: rgba(15,23,42,0.08); }
        [data-theme="light"] .stat-card { background: #FFFFFF; border-color: rgba(15,23,42,0.08); }
        [data-theme="light"] .stat-value { color: #0F172A; }
        [data-theme="light"] .stat-label { color: rgba(15,23,42,0.45); }
        [data-theme="light"] .sidebar { background: #FFFFFF; border-color: rgba(15,23,42,0.08); }
        [data-theme="light"] .topbar  { background: #FFFFFF; border-color: rgba(15,23,42,0.08); }
        [data-theme="light"] .topbar-font { color: #0F172A; }
        [data-theme="light"] .nav-item { color: rgba(15,23,42,0.55); }
        [data-theme="light"] .nav-item:hover { color: #0F172A; background: rgba(15,23,42,0.04); }
        [data-theme="light"] .nav-section-label { color: rgba(15,23,42,0.40); }
        [data-theme="light"] .brand-name { color: #0F172A; }
        [data-theme="light"] .sidebar-footer { color: rgba(15,23,42,0.30); }
        [data-theme="light"] .card-header { border-color: rgba(15,23,42,0.07); }
        [data-theme="light"] .card-font  { color: #0F172A; }
        [data-theme="light"] thead th  { color: rgba(15,23,42,0.55); border-color: rgba(15,23,42,0.08); }
        [data-theme="light"] tbody td  { color: rgba(15,23,42,0.7);  border-color: rgba(15,23,42,0.04); }
        [data-theme="light"] tbody tr:hover td { background: rgba(15,23,42,0.02); }
        [data-theme="light"] .btn-outline { border-color: rgba(15,23,42,0.15); color: rgba(15,23,42,0.65); background: transparent; }
        [data-theme="light"] .btn-outline:hover { border-color: #F97316; color: #F97316; }
        [data-theme="light"] .form-control { background: #F8FAFC; border-color: rgba(15,23,42,0.15); color: #0F172A; }
        [data-theme="light"] .form-control::placeholder { color: rgba(15,23,42,0.35); }
        [data-theme="light"] .form-control option { background: #fff; color: #0F172A; }
        [data-theme="light"] .form-label { color: rgba(15,23,42,0.45); }
        [data-theme="light"] .badge-gray { background: rgba(15,23,42,0.06); color: rgba(15,23,42,0.55); border-color: rgba(15,23,42,0.12); }
        [data-theme="light"] .toggle-slider { background: rgba(15,23,42,0.12); }
        [data-theme="light"] .pagination a,
        [data-theme="light"] .pagination span { border-color: rgba(15,23,42,0.15); color: rgba(15,23,42,0.6); background: #fff; }
        [data-theme="light"] .pagination a:hover { border-color: #F97316; color: #F97316; }
        [data-theme="light"] .admin-name { color: rgba(15,23,42,0.65); }
        [data-theme="light"] .hamburger { border-color: rgba(15,23,42,0.15); color: rgba(15,23,42,0.5); }
        [data-theme="light"] .chart-badge { color: rgba(15,23,42,0.45); background: rgba(15,23,42,0.05); }
        [data-theme="light"] .filter-bar { background: #FFFFFF; border-color: rgba(15,23,42,0.08); }
        [data-theme="light"] .filter-input { background: #F8FAFC; border-color: rgba(15,23,42,0.12); color: #0F172A; }
        [data-theme="light"] .filter-input::placeholder { color: rgba(15,23,42,0.35); }
        [data-theme="light"] .filter-select { background: #F8FAFC; border-color: rgba(15,23,42,0.12); color: #0F172A; }
        [data-theme="light"] .filter-tab { border-color: rgba(15,23,42,0.12); color: rgba(15,23,42,0.55); }
        [data-theme="light"] .filter-tab:hover { border-color: #F97316; color: #F97316; }
        [data-theme="light"] .filter-tab.active { background: rgba(249,115,22,0.08); border-color: #F97316; color: #F97316; }
        [data-theme="light"] .count-pill { background: rgba(15,23,42,0.07); }
        [data-theme="light"] .filter-tab.active .count-pill { background: rgba(249,115,22,0.18); }
        [data-theme="light"] .search-input { background: #F8FAFC; border-color: rgba(15,23,42,0.12); color: #0F172A; }
        [data-theme="light"] .search-input::placeholder { color: rgba(15,23,42,0.35); }
        [data-theme="light"] .role-select { background: #F8FAFC; border-color: rgba(15,23,42,0.15); color: #0F172A; }
        [data-theme="light"] .flash-toast { background: #FFFFFF; border-color: rgba(15,23,42,0.12); color: #0F172A; }
        [data-theme="light"] .s-card-font { color: #0F172A; }
        [data-theme="light"] .s-card-sub   { color: rgba(15,23,42,0.45); }
        [data-theme="light"] .s-label { color: rgba(15,23,42,0.85); }
        [data-theme="light"] .s-desc  { color: rgba(15,23,42,0.45); }
        [data-theme="light"] .s-divider { background: rgba(15,23,42,0.06); }
        [data-theme="light"] .s-sub { background: rgba(249,115,22,0.04); border-color: rgba(249,115,22,0.15); }
        [data-theme="light"] .s-sub-label { color: rgba(15,23,42,0.45); }
        [data-theme="light"] .s-input { background: #F8FAFC; border-color: rgba(15,23,42,0.15); color: #0F172A; }
        [data-theme="light"] .s-input option { background: #fff; color: #0F172A; }
        [data-theme="light"] .s-num-input { background: #F8FAFC; border-color: rgba(15,23,42,0.15); color: #0F172A; }
        [data-theme="light"] .ts-track { background: rgba(15,23,42,0.12); border-color: rgba(15,23,42,0.12); }
        [data-theme="light"] .perm-check.perm-off { background: rgba(15,23,42,0.03); border-color: rgba(15,23,42,0.15); }
        [data-theme="light"] .perm-toggle:hover .perm-check.perm-off { border-color: rgba(15,23,42,0.3); background: rgba(15,23,42,0.06); }
        [data-theme="light"] .role-card { background: #FFFFFF; border-color: rgba(15,23,42,0.08); }
        [data-theme="light"] .role-card-desc { color: rgba(15,23,42,0.40); }
        [data-theme="light"] .role-filter { border-color: rgba(15,23,42,0.12); color: rgba(15,23,42,0.55); background: transparent; }
        [data-theme="light"] .role-filter.active { background: rgba(249,115,22,0.08) !important; border-color: rgba(249,115,22,0.4) !important; color: #F97316 !important; }
        [data-theme="light"] .gallery-thumb { background: #F8FAFC; border-color: rgba(15,23,42,0.12); }
        [data-theme="light"] .gallery-add-slot { background: #F8FAFC; border-color: rgba(15,23,42,0.15); color: rgba(15,23,42,0.35); }
        [data-theme="light"] .gallery-add-slot:hover { border-color: rgba(249,115,22,0.4); background: rgba(249,115,22,0.03); }
        [data-theme="light"] [style*="background:#1a1a1a"],
        [data-theme="light"] [style*="background: #1a1a1a"] { background: #FFFFFF !important; }
        [data-theme="light"] [style*="background:#141414"],
        [data-theme="light"] [style*="background: #141414"] { background: #F8FAFC !important; }
        [data-theme="light"] [style*="background:#111111"],
        [data-theme="light"] [style*="background: #111111"],
        [data-theme="light"] [style*="background:#111"],
        [data-theme="light"] [style*="background: #111"] { background: #F1F5F9 !important; }
        [data-theme="light"] [style*="background:#0A0A0A"],
        [data-theme="light"] [style*="background:#0d0d0d"],
        [data-theme="light"] [style*="background:#0b0b0b"] { background: #E2E8F0 !important; }
        [data-theme="light"] [style*="color:#fff"]:not(.btn-orange):not([class*="badge-"]):not([style*="background:#F97316"]):not([style*="background:linear-gradient"]) { color: #0F172A !important; }
        [data-theme="light"] [style*="color:rgba(255,255,255,0.8)"]  { color: rgba(15,23,42,0.80) !important; }
        [data-theme="light"] [style*="color:rgba(255,255,255,0.7)"]  { color: rgba(15,23,42,0.70) !important; }
        [data-theme="light"] [style*="color:rgba(255,255,255,0.6)"]  { color: rgba(15,23,42,0.60) !important; }
        [data-theme="light"] [style*="color:rgba(255,255,255,0.55)"] { color: rgba(15,23,42,0.60) !important; }
        [data-theme="light"] [style*="color:rgba(255,255,255,0.5)"]  { color: rgba(15,23,42,0.55) !important; }
        [data-theme="light"] [style*="color:rgba(255,255,255,0.45)"] { color: rgba(15,23,42,0.50) !important; }
        [data-theme="light"] [style*="color:rgba(255,255,255,0.4)"]  { color: rgba(15,23,42,0.45) !important; }
        [data-theme="light"] [style*="color:rgba(255,255,255,0.35)"] { color: rgba(15,23,42,0.40) !important; }
        [data-theme="light"] [style*="color:rgba(255,255,255,0.3)"]  { color: rgba(15,23,42,0.35) !important; }
        [data-theme="light"] [style*="color:rgba(255,255,255,0.25)"] { color: rgba(15,23,42,0.30) !important; }
        [data-theme="light"] [style*="color:rgba(255,255,255,0.2)"]  { color: rgba(15,23,42,0.25) !important; }
        [data-theme="light"] [style*="color:rgba(255,255,255,0.15)"] { color: rgba(15,23,42,0.20) !important; }
        [data-theme="light"] [style*="border:1px solid rgba(255,255,255,0.07)"]  { border-color: rgba(15,23,42,0.08) !important; }
        [data-theme="light"] [style*="border:1px solid rgba(255,255,255,0.1)"]   { border-color: rgba(15,23,42,0.10) !important; }
        [data-theme="light"] [style*="border:1px solid rgba(255,255,255,0.12)"]  { border-color: rgba(15,23,42,0.12) !important; }
        [data-theme="light"] [style*="border:1.5px solid rgba(255,255,255,0.1)"] { border-color: rgba(15,23,42,0.12) !important; }
        [data-theme="light"] [style*="border:1.5px solid rgba(255,255,255,0.12)"]{ border-color: rgba(15,23,42,0.14) !important; }
        [data-theme="light"] [style*="border:1.5px solid rgba(255,255,255,0.06)"]{ border-color: rgba(15,23,42,0.08) !important; }
        [data-theme="light"] [style*="background:rgba(255,255,255,0.03)"] { background: rgba(15,23,42,0.025) !important; }
        [data-theme="light"] [style*="background:rgba(255,255,255,0.04)"] { background: rgba(15,23,42,0.03)  !important; }
        [data-theme="light"] [style*="background:rgba(255,255,255,0.05)"] { background: rgba(15,23,42,0.04)  !important; }
        [data-theme="light"] [style*="background:rgba(255,255,255,0.06)"] { background: rgba(15,23,42,0.05)  !important; }
        [data-theme="light"] [style*="background:rgba(255,255,255,0.07)"] { background: rgba(15,23,42,0.05)  !important; }
        [data-theme="light"] [style*="background:rgba(255,255,255,0.08)"] { background: rgba(15,23,42,0.06)  !important; }
        [data-theme="light"] [style*="background:rgba(255,255,255,0.1)"]  { background: rgba(15,23,42,0.07)  !important; }
        [data-theme="light"] #bell-dropdown { background: #FFFFFF; border-color: rgba(15,23,42,0.10); box-shadow: 0 20px 60px rgba(15,23,42,0.12); }
        [data-theme="light"] .bell-item:hover { background: rgba(15,23,42,0.03); }
        [data-theme="light"] .bell-item { border-color: rgba(15,23,42,0.05); }
        [data-theme="light"] #staffReqModal > div { background: #FFFFFF; border-color: rgba(15,23,42,0.10); }
        [data-theme="light"] .popup-box { background: #FFFFFF; border-color: rgba(15,23,42,0.10); }
        [data-theme="light"] .product-thumb { background: rgba(15,23,42,0.04); }
        [data-theme="light"] #bell-list::-webkit-scrollbar-thumb { background: rgba(15,23,42,0.12); }
        [data-theme="light"] #avatar-preview-wrap { border-color: rgba(249,115,22,0.3); background: rgba(249,115,22,0.05); }
        [data-theme="light"] .alert-success { background: rgba(34,197,94,0.08); border-color: rgba(34,197,94,0.25); }
        [data-theme="light"] .alert-error   { background: rgba(239,68,68,0.08); border-color: rgba(239,68,68,0.25); }
        [data-theme="light"] #bannerModal > div { background: #FFFFFF; }
        [data-theme="light"] #catDropdown { background: #FFFFFF; border-color: rgba(249,115,22,0.3); }
        [data-theme="light"] #couponModal > div,
        [data-theme="light"] #badgeModal > div { background: #FFFFFF; }
        [data-theme="light"] .orders-search-input { background: #F8FAFC !important; border-color: rgba(15,23,42,0.14) !important; color: #0F172A !important; }
        [data-theme="light"] .orders-search-input::placeholder { color: rgba(15,23,42,0.35) !important; }
        [data-theme="light"] .orders-clear-btn { background: rgba(15,23,42,0.05) !important; color: rgba(15,23,42,0.50) !important; border-color: rgba(15,23,42,0.12) !important; }
        [data-theme="light"] .order-status-tab-inactive { background: rgba(15,23,42,0.05) !important; color: rgba(15,23,42,0.55) !important; border-color: rgba(15,23,42,0.12) !important; }
        [data-theme="light"] input[type="month"] { background: #F8FAFC !important; border-color: rgba(15,23,42,0.14) !important; color: #0F172A !important; }
        [data-theme="light"] #lang-toggle { background: rgba(15,23,42,0.05); border-color: rgba(15,23,42,0.12); }
        [data-theme="light"] .lt-btn { color: #000; }
        [data-theme="light"] .lt-btn.active { background: var(--orange); color: #fff; }

        /* ══════════════════════════════════════════════════════════════════════
           RESPONSIVE
        ══════════════════════════════════════════════════════════════════════ */
        @media (max-width: 1024px) {
            .chart-grid-2  { grid-template-columns: 1fr; }
            .form-grid-2   { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            /* ── Layout ── */
            .sidebar       { transform: translateX(-100%); }
            .sidebar.open  { transform: translateX(0); }
            .main          { margin-left: 0; min-width: 0; }
            .hamburger     { display: flex; }
            .admin-name    { display: none; }

            /* ── Topbar ── */
            .topbar        { padding: 0 14px; height: 56px; }
            .topbar-left   { min-width: 0; flex: 1; overflow: hidden; }
            .topbar-font   { font-size: 17px; letter-spacing: 0.5px; white-space: nowrap;
                             overflow: hidden; text-overflow: ellipsis; max-width: 100%; }
            .topbar-right  { flex-shrink: 0; gap: 6px; }

            /* ── Content ── */
            .content       { padding: 14px; min-width: 0; }

            /* ── Cards: min-width:0 so flex/grid children don't overflow ── */
            .card          { min-width: 0; }
            .card-header   { flex-direction: column; align-items: flex-start; gap: 6px;
                             padding: 14px 16px; min-width: 0; }
            .card-body     { min-width: 0; padding: 16px; }

            /* ── Stats ── */
            .stats-grid    { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .stat-card     { padding: 14px; gap: 10px; min-width: 0; }
            .stat-value    { font-size: clamp(1.4rem, 4vw, 2.0rem); }
            .stat-icon     { width: 38px; height: 38px; }
            .chart-grid-2  { grid-template-columns: 1fr; }

            /* ── Type scale — readable ── */
            :root {
                --text-xs:    13px;
                --text-sm:    15px;
                --text-base:  16px;
                --text-md:    17px;
                --text-lg:    19px;
                --title-size: 15px;
            }
            :lang(km) {
                --text-xs:   13px;
                --text-sm:   14px;
                --text-base: 15px;
                --text-md:   16px;
            }

            /* ── Table ── */
            .table-wrap    { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            table          { font-size: 14px; min-width: 860px; }
            thead th       { padding: 11px 12px; font-size: 13px; white-space: nowrap; }
            tbody td       { padding: 11px 12px; font-size: 14px; }

            /* disable sticky cols so horizontal scroll works freely */
            thead th:first-child, tbody td:first-child,
            thead th:last-child,  tbody td:last-child {
                position: static !important;
                z-index: auto !important;
                border-left: none !important;
                background: unset !important;
            }
            tbody tr:hover td:first-child,
            tbody tr:hover td:last-child { background: var(--hover-bg) !important; }

            /* ── Badges ── */
            .badge         { font-size: 13px !important; padding: 4px 10px; }

            /* ── Buttons ── */
            .btn           { min-height: 42px; padding: 10px 16px; font-size: 15px; }
            .btn-sm        { min-height: 36px; padding: 7px 14px; font-size: 13px; }

            /* ── Forms ── */
            .form-control  { font-size: 15px; padding: 11px 14px; }
            .form-grid-2   { grid-template-columns: 1fr; }
        }

        @media (max-width: 480px) {
            .content       { padding: 10px; }
            .topbar-badge  { display: none; }
            #lang-toggle   { display: none; }
            .stats-grid    { grid-template-columns: 1fr 1fr; gap: 8px; }
            .stat-card     { padding: 12px; gap: 8px; }
            .stat-value    { font-size: clamp(1.2rem, 3.5vw, 1.6rem); }
            .card-header   { padding: 12px; }
            .card-body     { padding: 12px; }

            :root {
                --text-xs:    13px;
                --text-sm:    14px;
                --text-base:  15px;
                --text-md:    16px;
                --title-size: 14px;
            }

            table          { font-size: 14px; }
            thead th       { padding: 9px 10px; font-size: 13px; }
            tbody td       { padding: 9px 10px; font-size: 14px; }
            .badge         { font-size: 12px !important; padding: 3px 8px; }
        }
    </style>

    @stack('styles')

    {{-- Apply saved theme before paint to prevent flash --}}
    <script>
        (function() {
            var t = localStorage.getItem('tronmatix_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>

<body>

    {{-- ── Sidebar overlay (mobile) ──────────────────────────────────────────── --}}
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()" style="background: var(--overlay);"></div>

    {{-- ── Sidebar ────────────────────────────────────────────────────────────── --}}
    <aside class="sidebar" id="sidebar">

        {{-- Logo --}}
        <div class="sidebar-logo">
            <a href="{{ route('dashboard.index') }}" style="display:flex; align-items:center; gap:12px; text-decoration:none;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="40" height="40">
                  <defs>
                    <linearGradient id="lg" x1="0%" y1="0%" x2="100%" y2="100%">
                      <stop offset="0%" style="stop-color:#FFB020"/>
                      <stop offset="100%" style="stop-color:#F97316"/>
                    </linearGradient>
                  </defs>
                  <polygon points="50,4 90,26 90,74 50,96 10,74 10,26" fill="#1e1e1e" stroke="#F97316" stroke-width="4"/>
                  <polygon points="54,18 32,54 48,54 44,82 68,46 52,46" fill="url(#lg)"/>
                </svg>
                <div>
                    <div class="brand-name">TRONMATIX</div>
                    <div class="brand-sub">COMPUTER</div>
                </div>
            </a>
        </div>

        {{-- Navigation --}}
        <nav class="sidebar-nav">

            <div class="nav-section-label">{{ __('dashboard.common.main') }}</div>
            <a href="{{ route('dashboard.index') }}"
               class="nav-item {{ request()->routeIs('dashboard.index') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
                {{ strtoupper(__('dashboard.nav.dashboard')) }}
            </a>

            <div class="nav-section-label">{{ __('dashboard.common.catalog') }}</div>
            <a href="{{ route('dashboard.products') }}"
               class="nav-item {{ request()->routeIs('dashboard.products*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                </svg>
                {{ strtoupper(__('dashboard.nav.products')) }}
            </a>
            <a href="{{ route('dashboard.banners') }}"
               class="nav-item {{ request()->routeIs('dashboard.banners*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path d="M3 9h18M9 21V9"/>
                </svg>
                {{ strtoupper(__('dashboard.nav.banners')) }}
            </a>

            <div class="nav-section-label">{{ __('dashboard.common.sales') }}</div>
            <a href="{{ route('dashboard.orders') }}"
               class="nav-item {{ request()->routeIs('dashboard.orders*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                    <rect x="9" y="3" width="6" height="4" rx="1"/>
                </svg>
                {{ strtoupper(__('dashboard.nav.orders')) }}
            </a>

            <div class="nav-section-label">{{ __('dashboard.common.catalog') == 'catalog' ? 'Users' : __('dashboard.nav.users') }}</div>
            <a href="{{ route('dashboard.users') }}"
               class="nav-item {{ request()->routeIs('dashboard.users*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                </svg>
                {{ strtoupper(__('dashboard.nav.users')) }}
            </a>

            <a href="{{ route('dashboard.feedback') }}"
               class="nav-item {{ request()->routeIs('dashboard.feedback*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                {{ strtoupper(__('dashboard.nav.feedback')) }}
            </a>
            <div class="nav-section-label">{{ __('dashboard.common.promotions') }}</div>
            <a href="{{ route('dashboard.discounts') }}"
               class="nav-item {{ request()->routeIs('dashboard.discounts*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                    <line x1="7" y1="7" x2="7.01" y2="7"/>
                </svg>
                {{ strtoupper(__('dashboard.nav.discounts')) }}
            </a>

            <div class="nav-section-label">{{ __('dashboard.common.system') }}</div>
            <a href="{{ route('dashboard.report') }}"
               class="nav-item {{ request()->routeIs('dashboard.report*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
                {{ strtoupper(__('dashboard.nav.reports')) }}
            </a>
            <a href="{{ route('dashboard.settings') }}"
               class="nav-item {{ request()->routeIs('dashboard.settings*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
                </svg>
                {{ strtoupper(__('dashboard.nav.settings')) }}
            </a>

            @php 
                $user = Auth::guard('admin')->user() ?? Auth::guard('staff')->user();
                $adminRole = $user?->role ?? 'editor'; 
            @endphp
            @if(in_array($adminRole, ['admin','superadmin']))
            <a href="{{ route('dashboard.staff') }}"
               class="nav-item {{ request()->routeIs('dashboard.staff*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                    <path d="M16 3.13a4 4 0 010 7.75"/>
                    <line x1="19" y1="8" x2="19" y2="14"/>
                    <line x1="22" y1="11" x2="16" y2="11"/>
                </svg>
                {{ strtoupper(__('dashboard.nav.staff')) }}
            </a>
            @endif

        </nav>

        {{-- Language toggle in sidebar (visible on mobile/tablet) --}}
        <div class="sidebar-lang-toggle">
            <span style="font-size: var(--font-size); font-weight:700; color:var(--text-faint);">LANGUAGE</span>
            <div class="sidebar-lt-wrap">
                <button class="lt-btn sidebar-lt-btn {{ app()->getLocale() === 'en' ? 'active' : '' }}"
                        onclick="setAppLang('en')" type="button" data-lang="en">EN</button>
                <button class="lt-btn km-btn sidebar-lt-btn {{ app()->getLocale() === 'km' ? 'active' : '' }}"
                        onclick="setAppLang('km')" type="button" data-lang="km">ខ្មែរ</button>
            </div>
        </div>

        {{-- Sidebar footer --}}
        <div class="sidebar-footer">
            <span>{{ __('dashboard.common.copyright') }}</span>
            <span>{{ __('dashboard.common.version') }}</span>
        </div>
    </aside>

    {{-- ── Main ──────────────────────────────────────────────────────────────── --}}
    <div class="main">

        {{-- Topbar --}}
        <header class="topbar">
            <div class="topbar-left">
                <button class="hamburger" id="hamburgerBtn" onclick="openSidebar()">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="3" y1="6"  x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
                <span class="topbar-title">@yield('title', strtoupper(__('dashboard.nav.dashboard')))</span>
            </div>

            <div class="topbar-right">

                {{-- ── Language Toggle ────────────────────────────────────────── --}}
                <div id="lang-toggle">
                    <button class="lt-btn {{ app()->getLocale() === 'en' ? 'active' : '' }}"
                            onclick="setAppLang('en')" type="button" font="English" data-lang="en">EN</button>
                    <button class="lt-btn km-btn {{ app()->getLocale() === 'km' ? 'active' : '' }}"
                            onclick="setAppLang('km')" type="button" font="ភាសាខ្មែរ" data-lang="km">ខ្មែរ</button>
                </div>

                {{-- Theme toggle --}}
                <button id="theme-toggle" onclick="toggleTheme()" font="Toggle light/dark theme" style="
                    background:var(--bell-bg); border:1.5px solid var(--bell-border);
                    border-radius:10px; width:40px; height:40px;
                    display:flex; align-items:center; justify-content:center;
                    cursor:pointer; transition:all .2s; font-size: var(--font-size);
                " onmouseover="this.style.borderColor='#F97316'"
                   onmouseout="this.style.borderColor=''">
                    <span id="theme-icon">☀️</span>
                </button>

                {{-- Notification bell --}}
                <div style="position:relative;" id="bell-wrapper">
                    <button id="bell-btn" onclick="toggleBell()" style="
                        position:relative; background:var(--bell-bg);
                        border:1.5px solid var(--bell-border); border-radius:10px;
                        width:40px; height:40px; display:flex; align-items:center; justify-content:center;
                        cursor:pointer; transition:all .2s; font-size: var(--font-size);
                    " onmouseover="this.style.borderColor='#F97316'"
                       onmouseout="if(!bellOpen)this.style.borderColor=''">
                        🔔
                        <span id="bell-dot" style="
                            display:none; position:absolute; top:6px; right:6px;
                            width:10px; height:10px; border-radius:50%;
                            background:#F97316; border:2px solid var(--surface);
                            animation:bellPulse 1.8s ease infinite;
                        "></span>
                    </button>

                    <div id="bell-dropdown" style="
                        display:none; position:fixed; top:60px; right:12px; z-index:500;
                        width:min(340px, calc(100vw - 24px)); background:var(--dropdown-bg);
                        border:1px solid var(--border);
                        border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,0.25);
                        overflow:hidden;
                    ">
                        <div style="padding:14px 18px; border-bottom:1px solid var(--border);
                             display:flex; align-items:center; justify-content:space-between;">
                            <span style="font-size: var(--font-size); font-weight:800; letter-spacing:2px; color:var(--text-muted);">
                                {{ strtoupper(__('dashboard.common.alerts')) }}
                            </span>
                            <a href="{{ route('dashboard.settings') }}" style="font-size: var(--font-size); color:rgba(249,115,22,0.6);
                                text-decoration:none; letter-spacing:1px;"
                               onmouseover="this.style.color='#F97316'" onmouseout="this.style.color='rgba(249,115,22,0.6)'">
                                ⚙ {{ strtoupper(__('dashboard.nav.settings')) }}
                            </a>
                        </div>
                        <div id="bell-list" style="max-height:min(320px, calc(100vh - 160px)); overflow-y:auto; padding:8px 0;">
                            <div style="padding:24px; text-align:center; color:var(--text-faint); font-family:Rajdhani,sans-serif;">
                                <div style="font-size: var(--font-size); margin-bottom:6px;">⏳</div>
                                Loading…
                            </div>
                        </div>
                        <div style="padding:10px 18px; border-top:1px solid var(--border); text-align:center;">
                            <a href="{{ route('dashboard.settings') }}" style="font-size: var(--font-size); color:var(--text-faint);
                                text-decoration:none; letter-spacing:1px;"
                               onmouseover="this.style.color='#F97316'" onmouseout="this.style.color=''">
                                {{ __('dashboard.settings.store') }} →
                            </a>
                        </div>
                    </div>
                </div>

                <span class="topbar-badge">
                    @php
                        $user = Auth::guard('admin')->user() ?? Auth::guard('staff')->user();
                    @endphp
                    {{ strtoupper($user?->role ?? 'STAFF') }}
                </span>

                @php
                    $_topbarAdmin = Auth::guard('admin')->user() ?? Auth::guard('staff')->user();
                    $_topbarAvatar = $_topbarAdmin?->avatar
                        ? (Str::startsWith($_topbarAdmin->avatar, ['http://','https://'])
                            ? $_topbarAdmin->avatar
                            : asset('storage/' . $_topbarAdmin->avatar))
                        : null;
                @endphp
                <a href="{{ route('dashboard.profile') }}" class="admin-profile-link" font="{{ __('dashboard.profile.editProfile') }}">
                    <div class="admin-avatar">
                        @if($_topbarAvatar)
                            <img src="{{ $_topbarAvatar }}"
                                 alt="{{ $_topbarAdmin->name }}"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
                            <span style="display:none;width:100%;height:100%;align-items:center;justify-content:center;font-weight:400;font-size: var(--font-size);">
                                {{ strtoupper(substr($_topbarAdmin->name ?? 'A', 0, 1)) }}
                            </span>
                        @else
                            {{ strtoupper(substr($_topbarAdmin->name ?? 'A', 0, 1)) }}
                        @endif
                    </div>
                    <span class="admin-name">
                        {{ $_topbarAdmin->name ?? 'Admin' }}
                    </span>
                </a>

                <form method="POST" action="{{ route('dashboard.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">
                        {{ __('dashboard.common.logout') }}
                    </button>
                </form>
            </div>
        </header>

        {{-- Flash messages --}}
        @unless(View::hasSection('suppress_flash'))
            @if(session('success'))
                <div style="padding: 14px 24px 0;">
                    <div class="alert alert-success flash">✓ {{ session('success') }}</div>
                </div>
            @endif
            @if(session('error'))
                <div style="padding: 14px 24px 0;">
                    <div class="alert alert-error flash">✗ {{ session('error') }}</div>
                </div>
            @endif
        @endunless

        {{-- Page content --}}
        <div class="content">
            @yield('content')
        </div>

    </div>

    {{-- ── Sidebar JS ───────────────────────────────────────────────────────── --}}
    <script>
        function openSidebar() {
            document.getElementById('sidebar').classList.add('open');
            document.getElementById('sidebarOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('active');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('.nav-item').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) closeSidebar();
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeSidebar();
        });

        (function() {
            var link = document.querySelector('.admin-profile-link');
            if (link && window.location.pathname.includes('/dashboard/profile')) {
                link.classList.add('active-profile');
            }
        })();
    </script>

    @stack('scripts')

    {{-- ── Notification Bell ─────────────────────────────────────────────────── --}}
    <style>
        @keyframes bellPulse {
            0%,100% { transform:scale(1);   opacity:1; }
            50%      { transform:scale(1.35); opacity:.7; }
        }
        @keyframes bellDropIn {
            from { opacity:0; transform:translateY(-8px) scale(.97); }
            to   { opacity:1; transform:translateY(0)    scale(1); }
        }
        #bell-list::-webkit-scrollbar { width:4px; }
        #bell-list::-webkit-scrollbar-thumb { background:var(--scrollbar); border-radius:2px; }
        .bell-item {
            display:flex; align-items:flex-start; gap:12px;
            padding:11px 18px; text-decoration:none;
            transition:background .15s; border-bottom:1px solid var(--border-faint);
        }
        .bell-item:last-child { border-bottom:none; }
        .bell-item:hover { background:var(--hover-bg); }
    </style>

    <script>
        var bellOpen = false;

        function toggleBell() { bellOpen ? closeBell() : openBell(); }

        function openBell() {
            bellOpen = true;
            const d   = document.getElementById('bell-dropdown');
            const btn = document.getElementById('bell-btn');
            d.style.display = 'block';
            d.style.animation = 'none';
            d.offsetHeight;
            d.style.animation = 'bellDropIn .25s cubic-bezier(0.34,1.2,0.64,1)';
            btn.style.borderColor = '#F97316';
            loadBellAlerts();
        }

        function closeBell() {
            bellOpen = false;
            document.getElementById('bell-dropdown').style.display = 'none';
            document.getElementById('bell-btn').style.borderColor  = '';
        }

        document.addEventListener('click', function(e) {
            if (bellOpen && !document.getElementById('bell-wrapper').contains(e.target)) closeBell();
        });

        // i18n strings for bell (PHP passes to JS)
        var _i18n = {
            noAlerts:     '{{ __("dashboard.common.noAlerts") }}',
            allClear:     '{{ __("dashboard.common.allClear") }}',
            failedAlerts: '{{ __("dashboard.common.failedAlerts") }}',
            alertsfont:  '{{ strtoupper(__("dashboard.common.alerts")) }}',
            staffAccess:  '{{ __("dashboard.common.staffAccess") }}',
            review:       '{{ strtoupper(__("dashboard.btn.view")) }}',
            accept:       '✓ {{ strtoupper(__("dashboard.btn.confirm")) }}',
            reject:       '✕ {{ strtoupper(__("dashboard.btn.delete")) }}',
        };

        function loadBellAlerts() {
            fetch('{{ route('dashboard.notifications') }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                const list   = document.getElementById('bell-list');
                const isDark = document.documentElement.getAttribute('data-theme') !== 'light';
                const mutedColor  = isDark ? 'rgba(255,255,255,0.35)' : 'rgba(15,23,42,0.45)';
                const faintColor  = isDark ? 'rgba(255,255,255,0.25)' : 'rgba(15,23,42,0.30)';
                const arrowColor  = isDark ? 'rgba(255,255,255,0.2)'  : 'rgba(15,23,42,0.25)';

                if (!data.alerts || data.alerts.length === 0) {
                    list.innerHTML = `
                        <div style="padding:28px; text-align:center; font-family:Rajdhani,sans-serif;">
                            <div style="font-size: var(--font-size); margin-bottom:8px;">🎉</div>
                            <div style="color:#22c55e; font-weight:700; font-size: var(--font-size); letter-spacing:1px;">${_i18n.allClear}</div>
                            <div style="color:${mutedColor}; font-size: var(--font-size); margin-top:4px;">${_i18n.noAlerts}</div>
                        </div>`;
                    return;
                }
                list.innerHTML = data.alerts.map(a => {
                    if (a.actionable && a.type === 'staff_request') {
                        return `
                        <div class="bell-item" style="flex-direction:column;gap:8px;align-items:stretch;">
                            <div style="display:flex;align-items:flex-start;gap:12px;">
                                <div style="width:36px;height:36px;border-radius:10px;flex-shrink:0;
                                     background:${a.color}18;border:1px solid ${a.color}44;
                                     display:flex;align-items:center;justify-content:center;font-size: var(--font-size);">
                                     ${a.icon}
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-family:Rajdhani,sans-serif;font-size: var(--font-size);font-weight:700;
                                         color:${a.color};letter-spacing:.5px;">${a.font}</div>
                                    <div style="font-size: var(--font-size);color:${mutedColor};margin-top:2px;line-height:1.4;">${a.body}</div>
                                    ${a.request_message ? `<div style="font-size: var(--font-size);color:${faintColor};margin-top:3px;font-style:italic;">"${a.request_message}"</div>` : ''}
                                </div>
                            </div>
                            <div style="display:flex;gap:8px;padding-left:48px;">
                                <button onclick="openStaffRequestModal(${JSON.stringify(a).replace(/"/g,'&quot;')})"
                                    style="flex:1;padding:7px 0;border-radius:8px;background:#a78bfa22;border:1px solid #a78bfa44;
                                           color:#a78bfa;font-family:Rajdhani,sans-serif;font-size: var(--font-size);
                                           font-weight:700;letter-spacing:1px;cursor:pointer;transition:all .15s;"
                                    onmouseover="this.style.background='#a78bfa33'"
                                    onmouseout="this.style.background='#a78bfa22'">
                                    👁 REVIEW
                                </button>
                                <button onclick="handleStaffRequest(${a.request_id},'accept',this)"
                                    style="flex:1;padding:7px 0;border-radius:8px;background:#22c55e22;border:1px solid #22c55e44;
                                           color:#22c55e;font-family:Rajdhani,sans-serif;font-size: var(--font-size);
                                           font-weight:700;letter-spacing:1px;cursor:pointer;transition:all .15s;"
                                    onmouseover="this.style.background='#22c55e33'"
                                    onmouseout="this.style.background='#22c55e22'">
                                    ✓ ACCEPT
                                </button>
                                <button onclick="handleStaffRequest(${a.request_id},'reject',this)"
                                    style="flex:1;padding:7px 0;border-radius:8px;background:#ef444422;border:1px solid #ef444444;
                                           color:#ef4444;font-family:Rajdhani,sans-serif;font-size: var(--font-size);
                                           font-weight:700;letter-spacing:1px;cursor:pointer;transition:all .15s;"
                                    onmouseover="this.style.background='#ef444433'"
                                    onmouseout="this.style.background='#ef444422'">
                                    ✕ REJECT
                                </button>
                            </div>
                        </div>`;
                    }
                    return `
                    <a href="${a.url}" class="bell-item" onclick="closeBell()">
                        <div style="width:36px;height:36px;border-radius:10px;flex-shrink:0;
                             background:${a.color}18;border:1px solid ${a.color}44;
                             display:flex;align-items:center;justify-content:center;font-size: var(--font-size);">
                             ${a.icon}
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-family:Rajdhani,sans-serif;font-size: var(--font-size);font-weight:700;
                                 color:${a.color};letter-spacing:.5px;">${a.font}</div>
                            <div style="font-size: var(--font-size);color:${mutedColor};margin-top:2px;line-height:1.4;">${a.body}</div>
                        </div>
                        <div style="font-size: var(--font-size);color:${arrowColor};flex-shrink:0;">›</div>
                    </a>`;
                }).join('');
            })
            .catch(() => {
                const isDark = document.documentElement.getAttribute('data-theme') !== 'light';
                const failColor = isDark ? 'rgba(255,255,255,0.3)' : 'rgba(15,23,42,0.4)';
                document.getElementById('bell-list').innerHTML =
                    `<div style="padding:20px;text-align:center;color:${failColor};font-size: var(--font-size);">${_i18n.failedAlerts}</div>`;
            });
        }

        function openStaffRequestModal(a) {
            closeBell();
            const m = document.getElementById('staffReqModal');
            document.getElementById('srm-name').textContent  = a.request_name;
            document.getElementById('srm-email').textContent = a.request_email;
            document.getElementById('srm-role').textContent  = (a.request_role || '').toUpperCase();
            document.getElementById('srm-msg').textContent   = a.request_message || '(no message)';
            document.getElementById('srm-accept').onclick    = () => handleStaffRequest(a.request_id, 'accept', document.getElementById('srm-accept'));
            document.getElementById('srm-reject').onclick    = () => handleStaffRequest(a.request_id, 'reject', document.getElementById('srm-reject'));
            m.style.display = 'flex';
            requestAnimationFrame(() => m.style.opacity = '1');
        }

        function closeStaffReqModal() {
            const m = document.getElementById('staffReqModal');
            m.style.opacity = '0';
            setTimeout(() => { m.style.display = 'none'; }, 200);
        }

        function handleStaffRequest(id, action, btn) {
            if (btn) { btn.disabled = true; btn.textContent = '…'; }
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const url = action === 'accept'
                ? `/dashboard/staff-requests/${id}/accept`
                : `/dashboard/staff-requests/${id}/reject`;

            fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                closeStaffReqModal();
                showInlineToast(data.message || data.error, action === 'accept' ? '#22c55e' : '#ef4444');
                loadBellAlerts();
            })
            .catch(() => {
                showInlineToast('Action failed. Please try again.', '#ef4444');
                if (btn) { btn.disabled = false; btn.textContent = action === 'accept' ? '✓ ACCEPT' : '✕ REJECT'; }
            });
        }

        function showInlineToast(msg, color) {
            const el = document.createElement('div');
            const modalBg = getComputedStyle(document.documentElement).getPropertyValue('--modal-bg').trim() || '#141414';
            el.style.cssText = `position:fixed;top:20px;left:50%;transform:translateX(-50%);
                z-index:99999;background:${modalBg};border:1px solid ${color}55;
                color:${color};padding:12px 24px;border-radius:12px;
                font-family:Rajdhani,sans-serif;font-size: var(--font-size);font-weight:700;
                letter-spacing:1px;box-shadow:0 8px 32px rgba(0,0,0,0.25);
                animation:atToastIn .3s ease;white-space:nowrap;`;
            el.textContent = msg;
            document.body.appendChild(el);
            setTimeout(() => { el.style.animation='atToastOut .3s ease forwards'; setTimeout(()=>el.remove(),300); }, 3500);
        }

        function getSeenAlerts() {
            try { return JSON.parse(localStorage.getItem('seen_alerts') || '[]'); }
            catch { return []; }
        }
        function markAlertsSeen(ids) {
            try { localStorage.setItem('seen_alerts', JSON.stringify(ids)); }
            catch {}
        }

        function showAlertToast(alert) {
            const id = 'atst_' + Date.now() + Math.random().toString(36).slice(2);
            const colors = {
                '#22c55e': { bg: 'rgba(34,197,94,0.12)',  border: 'rgba(34,197,94,0.35)',  text: '#22c55e' },
                '#F97316': { bg: 'rgba(249,115,22,0.12)', border: 'rgba(249,115,22,0.35)', text: '#F97316' },
                '#ef4444': { bg: 'rgba(239,68,68,0.12)',  border: 'rgba(239,68,68,0.35)',  text: '#ef4444' },
                '#3b82f6': { bg: 'rgba(59,130,246,0.12)', border: 'rgba(59,130,246,0.35)', text: '#3b82f6' },
            };
            const c = colors[alert.color] || colors['#F97316'];
            const modalBg = getComputedStyle(document.documentElement).getPropertyValue('--modal-bg').trim() || '#141414';
            const isDark = document.documentElement.getAttribute('data-theme') !== 'light';
            const bodyTextColor = isDark ? 'rgba(255,255,255,0.45)' : 'rgba(15,23,42,0.55)';
            const closeBtnBg    = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(15,23,42,0.06)';
            const closeBtnBorder= isDark ? 'rgba(255,255,255,0.1)'  : 'rgba(15,23,42,0.12)';
            const closeBtnColor = isDark ? 'rgba(255,255,255,0.3)'  : 'rgba(15,23,42,0.4)';

            const el = document.createElement('div');
            el.id = id;
            el.style.cssText = `
                position:fixed; top:24px; right:24px; z-index:99999;
                display:flex; align-items:flex-start; gap:12px;
                padding:14px 18px; border-radius:16px; max-width:340px;
                background:${modalBg}; border:1px solid ${c.border};
                box-shadow:0 16px 48px rgba(0,0,0,0.25);
                font-family:Rajdhani,sans-serif;
                animation:atToastIn .4s cubic-bezier(0.34,1.4,0.64,1);
                cursor:pointer; transition:transform .15s, box-shadow .15s;
            `;
            el.onmouseenter = () => { el.style.transform='translateY(-2px)'; el.style.boxShadow='0 20px 60px rgba(0,0,0,0.3)'; };
            el.onmouseleave = () => { el.style.transform=''; el.style.boxShadow='0 16px 48px rgba(0,0,0,0.25)'; };
            el.onclick = () => { if (alert.url) window.location.href = alert.url; };

            el.innerHTML = `
                <div style="width:40px;height:40px;border-radius:12px;flex-shrink:0;
                     background:${c.bg};border:1px solid ${c.border};
                     display:flex;align-items:center;justify-content:center;font-size: var(--font-size);">
                    ${alert.icon}
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size: var(--font-size);font-weight:800;color:${c.text};letter-spacing:1px;margin-bottom:2px;">
                        ${alert.font}
                    </div>
                    <div style="font-size: var(--font-size);color:${bodyTextColor};line-height:1.4;">
                        ${alert.body}
                    </div>
                </div>
                <button onclick="event.stopPropagation();dismissAlertToast('${id}')"
                    style="flex-shrink:0;width:24px;height:24px;border-radius:6px;background:${closeBtnBg};
                           border:1px solid ${closeBtnBorder};color:${closeBtnColor};
                           font-size: var(--font-size);cursor:pointer;display:flex;align-items:center;justify-content:center;">×</button>
                <div style="position:absolute;bottom:0;left:0;right:0;height:3px;border-radius:0 0 16px 16px;overflow:hidden;">
                    <div style="height:100%;background:${c.text};animation:atToastBar 6s linear forwards;border-radius:inherit;"></div>
                </div>
            `;
            el.style.position = 'fixed';
            document.body.appendChild(el);

            const toasts = document.querySelectorAll('[id^="atst_"]');
            toasts.forEach((t, i) => {
                if (t.id !== id) t.style.top = (24 + (toasts.length - 1 - i) * 80) + 'px';
            });

            setTimeout(() => dismissAlertToast(id), 6000);
        }

        // ── Order alert sound ──────────────────────────────────────────────
        function playOrderAlertSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                // Two-tone "ding-dong" beep
                [523.25, 659.25].forEach((freq, i) => {
                    const osc  = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type      = 'sine';
                    osc.frequency.value = freq;
                    const start = ctx.currentTime + i * 0.18;
                    gain.gain.setValueAtTime(0, start);
                    gain.gain.linearRampToValueAtTime(0.45, start + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.001, start + 0.35);
                    osc.start(start);
                    osc.stop(start + 0.35);
                });
            } catch(e) {}
        }

        function dismissAlertToast(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.style.animation = 'atToastOut .3s ease forwards';
            setTimeout(() => el?.remove(), 300);
        }

        function pollBellDot() {
            fetch('{{ route('dashboard.notifications') }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                const count = data.count ?? 0;
                document.getElementById('bell-dot').style.display = count > 0 ? 'block' : 'none';

                if (!data.alerts || data.alerts.length === 0) return;

                const seen    = getSeenAlerts();
                const current = data.alerts.map(a => a.id || a.font + a.body);
                const newAlerts = data.alerts.filter(a => {
                    const key = a.id || a.font + a.body;
                    return !seen.includes(key);
                });

                if (newAlerts.length > 0) playOrderAlertSound();
                newAlerts.forEach(a => showAlertToast(a));

                const allSeen = [...new Set([...seen, ...current])].slice(-50);
                markAlertsSeen(allSeen);
            })
            .catch(() => {});
        }

        pollBellDot();
        setInterval(pollBellDot, 15000);

        function applyTheme(t) {
            document.documentElement.setAttribute('data-theme', t);
            document.body.classList.toggle('theme-light', t === 'light');
            const icon = document.getElementById('theme-icon');
            if (icon) icon.textContent = t === 'light' ? '🌙' : '☀️';
            if (window.__updateChartTheme) window.__updateChartTheme(t);
        }

        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme') || 'dark';
            const next = current === 'dark' ? 'light' : 'dark';
            localStorage.setItem('tronmatix_theme', next);
            applyTheme(next);
        }

        (function() {
            const t = document.documentElement.getAttribute('data-theme') || 'dark';
            applyTheme(t);
        })();

        // ── Staff online heartbeat ──────────────────────────────────────
        (function() {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

            function sendHeartbeat() {
                fetch('{{ route("dashboard.staff.heartbeat") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
                    keepalive: true
                }).catch(() => {});
            }

            function sendOffline() {
                navigator.sendBeacon(
                    '{{ route("dashboard.staff.offline") }}',
                    new Blob([JSON.stringify({ _token: csrf })], { type: 'application/json' })
                );
            }

            sendHeartbeat();
            setInterval(sendHeartbeat, 30000);
            window.addEventListener('beforeunload', sendOffline);
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'hidden') sendOffline();
                else sendHeartbeat();
            });
        })();

        // ── Language switcher ───────────────────────────────────────────────
        function setAppLang(lang) {
            const currentLang = document.documentElement.lang || 'en';
            if (currentLang === lang) return;

            // Update toggle button active state immediately
            document.querySelectorAll('.lt-btn').forEach(function(btn) {
                btn.classList.toggle('active', btn.dataset.lang === lang);
            });

            // Smooth fade out
            document.body.style.transition = 'opacity 0.18s ease';
            document.body.style.opacity = '0';

            // Save preference to localStorage + cookie so middleware picks it up
            localStorage.setItem('app_lang', lang);
            document.cookie = 'app_lang=' + lang + ';path=/;max-age=31536000;SameSite=Lax';

            // Hit the lang route (sets session) then reload current page
            fetch('/lang/' + lang, { method: 'GET', credentials: 'same-origin' })
                .then(function() {
                    // Reload current URL so blade re-renders with new locale
                    window.location.reload();
                })
                .catch(function() {
                    window.location.href = '/lang/' + lang;
                });
        }
    </script>

    <style>
    @keyframes atToastIn  { from{opacity:0;transform:translateX(40px) scale(.95)} to{opacity:1;transform:none} }
    @keyframes atToastOut { to{opacity:0;transform:translateX(40px) scale(.95)} }
    @keyframes atToastBar { from{width:100%} to{width:0%} }
    @keyframes onlinePulse {
        0%, 100% { box-shadow: 0 0 4px #22c55e; }
        50%       { box-shadow: 0 0 12px #22c55e, 0 0 20px rgba(34,197,94,0.3); }
    }
    </style>

    {{-- Staff Request Modal (superadmin only) --}}
    @if((Auth::guard('admin')->user() ?? Auth::guard('staff')->user())?->role === 'superadmin')
    <div id="staffReqModal" style="
        display:none; opacity:0; transition:opacity .2s;
        position:fixed; inset:0; z-index:99000;
        background:var(--overlay); backdrop-filter:blur(4px);
        align-items:center; justify-content:center; padding:20px;
    " onclick="if(event.target===this)closeStaffReqModal()">
        <div style="
            background:var(--modal-bg); border:1px solid var(--border);
            border-radius:20px; width:100%; max-width:460px;
            box-shadow:0 30px 80px rgba(0,0,0,0.4);
            overflow:hidden; font-family:Rajdhani,sans-serif;
        ">
            <div style="height:3px;background:linear-gradient(90deg,transparent,#a78bfa,transparent);"></div>
            <div style="padding:28px 32px 32px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;">
                    <div>
                        <div style="font-size: var(--font-size);letter-spacing:3px;color:var(--text-faint);margin-bottom:3px;">
                            {{ strtoupper(__('dashboard.common.staffAccess')) }}
                        </div>
                        <div style="font-size: var(--font-size);font-weight:800;letter-spacing:1px;color:#a78bfa;" id="srm-name">—</div>
                    </div>
                    <button onclick="closeStaffReqModal()" style="
                        width:36px;height:36px;border-radius:10px;border:1px solid var(--border-input);
                        background:var(--hover-bg);color:var(--text-muted);
                        font-size: var(--font-size);cursor:pointer;display:flex;align-items:center;justify-content:center;
                    ">×</button>
                </div>

                <div style="display:grid;gap:12px;margin-bottom:22px;">
                    <div style="display:flex;gap:12px;padding:14px;background:var(--surface-2);border-radius:12px;border:1px solid var(--border);">
                        <div style="font-size: var(--font-size);width:36px;text-align:center;">📧</div>
                        <div>
                            <div style="font-size: var(--font-size);letter-spacing:2px;color:var(--text-faint);margin-bottom:2px;">
                                {{ strtoupper(__('dashboard.form.email')) }}
                            </div>
                            <div style="font-size: var(--font-size);font-weight:600;color:var(--text);" id="srm-email">—</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:12px;padding:14px;background:var(--surface-2);border-radius:12px;border:1px solid var(--border);">
                        <div style="font-size: var(--font-size);width:36px;text-align:center;">👤</div>
                        <div>
                            <div style="font-size: var(--font-size);letter-spacing:2px;color:var(--text-faint);margin-bottom:2px;">
                                {{ strtoupper(__('dashboard.access.yourRole')) }}
                            </div>
                            <div style="font-size: var(--font-size);font-weight:700;color:#a78bfa;letter-spacing:2px;" id="srm-role">—</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:12px;padding:14px;background:var(--surface-2);border-radius:12px;border:1px solid var(--border);">
                        <div style="font-size: var(--font-size);width:36px;text-align:center;">💬</div>
                        <div>
                            <div style="font-size: var(--font-size);letter-spacing:2px;color:var(--text-faint);margin-bottom:2px;">MESSAGE</div>
                            <div style="font-size: var(--font-size);color:var(--text-muted);font-style:italic;" id="srm-msg">—</div>
                        </div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <button id="srm-reject" style="
                        padding:13px;border-radius:12px;border:1px solid rgba(239,68,68,0.3);
                        background:rgba(239,68,68,0.08);color:#ef4444;
                        font-family:Rajdhani,sans-serif;font-size: var(--font-size);font-weight:700;
                        letter-spacing:2px;cursor:pointer;transition:all .2s;
                    " onmouseover="this.style.background='rgba(239,68,68,0.18)'"
                       onmouseout="this.style.background='rgba(239,68,68,0.08)'">
                        ✕ {{ strtoupper(__('dashboard.btn.cancel')) }}
                    </button>
                    <button id="srm-accept" style="
                        padding:13px;border-radius:12px;border:none;
                        background:#22c55e;color:#fff;
                        font-family:Rajdhani,sans-serif;font-size: var(--font-size);font-weight:700;
                        letter-spacing:2px;cursor:pointer;transition:all .2s;
                    " onmouseover="this.style.background='#16a34a'"
                       onmouseout="this.style.background='#22c55e'">
                        ✓ {{ strtoupper(__('dashboard.btn.confirm')) }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</body>
</html>
