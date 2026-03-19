<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Tronmatix Admin — @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --orange:    #F97316;
            --dark:      #0A0A0A;
            --dark-800:  #111111;
            --dark-700:  #1A1A1A;
            --dark-600:  #222222;
            --sidebar-w: 240px;
        }

        body {
            font-family: 'Rajdhani', sans-serif;
            background: var(--dark);
            color: #fff;
            min-height: 100vh;
            font-size: 16px;
        }

        /* ── Sidebar ──────────────────────────────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--dark-800);
            border-right: 1px solid rgba(255,255,255,0.06);
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
            border-bottom: 1px solid rgba(255,255,255,0.06);
            flex-shrink: 0;
        }

        .sidebar-logo img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }

        .brand-name {
            font-size: 17px;
            font-weight: 700;
            letter-spacing: 2px;
            color: #fff;
        }

        .brand-sub {
            font-size: 11px;
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
            font-size: 13px;
            letter-spacing: 3px;
            color: rgba(255,255,255,0.5);
            padding: 16px 20px 5px;
            text-transform: uppercase;
            font-weight: 700;
            margin-top: 4px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 20px;
            color: rgba(255,255,255,0.55);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 1px;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .nav-item:hover {
            color: #fff;
            background: rgba(255,255,255,0.04);
        }

        .nav-item.active {
            color: var(--orange);
            background: rgba(249,115,22,0.08);
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
            border-top: 1px solid rgba(255,255,255,0.06);
            font-size: 12px;
            color: rgba(255,255,255,0.2);
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
            background: var(--dark-800);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .topbar-title {
            font-size: 19px;
            font-weight: 700;
            letter-spacing: 2px;
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
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 7px;
            cursor: pointer;
            color: rgba(255,255,255,0.7);
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
            font-size: 12px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            letter-spacing: 1px;
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
            font-size: 14px;
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
            font-size: 14px;
            font-weight: 600;
            color: rgba(255,255,255,0.8);
            white-space: nowrap;
        }

        /* ── Content ──────────────────────────────────────────────────────────── */
        .content {
            padding: 24px;
            flex: 1;
        }

        /* ── Cards ────────────────────────────────────────────────────────────── */
        .card {
            background: var(--dark-800);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 14px;
            overflow: hidden;
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }

        /* FIX: card-title was wrongly using padding:20px — it sits INSIDE
           card-header which already has its own padding. */
        .card-title {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1.5px;
        }

        /* FIX: card-body was missing — referenced by orders-show, products-form, etc. */
        .card-body {
            padding: 20px;
        }

        /* ── Stats grid ───────────────────────────────────────────────────────── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--dark-800);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 14px;
            padding: 18px;
            display: flex;
            align-items: center;
            gap: 14px;
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
            line-height: 1;
        }

        .stat-label {
            font-size: 11px;
            color: rgba(255,255,255,0.4);
            letter-spacing: 1px;
            margin-top: 4px;
        }

        /* ── Chart grid ───────────────────────────────────────────────────────── */
        .chart-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .chart-badge {
            font-size: 12px;
            color: rgba(255,255,255,0.35);
            background: rgba(255,255,255,0.05);
            padding: 3px 10px;
            border-radius: 20px;
            letter-spacing: 1px;
            white-space: nowrap;
        }

        /* ── Table ────────────────────────────────────────────────────────────── */
        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        thead th {
            text-align: left;
            padding: 12px 16px;
            font-size: 12px;
            letter-spacing: 2px;
            color: rgba(255,255,255,0.5);
            border-bottom: 1px solid rgba(255,255,255,0.07);
            white-space: nowrap;
            font-weight: 700;
        }

        tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            color: rgba(255,255,255,0.8);
            vertical-align: middle;
            font-size: 14px;
        }

        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: rgba(255,255,255,0.02); }

        /* ── Badges ───────────────────────────────────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
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
        .badge-gray      { background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.4); border: 1px solid rgba(255,255,255,0.1); }
        .badge-seller    { background: rgba(16,185,129,0.15);  color: #10b981; border: 1px solid rgba(16,185,129,0.3); }

        /* ── Buttons ──────────────────────────────────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 8px;
            font-family: 'Rajdhani', sans-serif;
            font-size: 14px;
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
            border: 1px solid rgba(255,255,255,0.15);
            color: rgba(255,255,255,0.7);
        }
        .btn-outline:hover { border-color: var(--orange); color: var(--orange); }

        .btn-danger {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            color: #EF4444;
        }
        .btn-danger:hover { background: rgba(239,68,68,0.2); }

        .btn-sm { padding: 5px 12px; font-size: 12px; }

        /* ── Forms ────────────────────────────────────────────────────────────── */
        .form-group { margin-bottom: 18px; }

        .form-label {
            display: block;
            font-size: 12px;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.4);
            margin-bottom: 7px;
            font-weight: 700;
        }

        /* FIX: was always orange — default to subtle, orange only on focus */
        .form-control {
            width: 100%;
            background: var(--dark-700);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 10px 14px;
            color: #fff;
            font-family: 'Rajdhani', sans-serif;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-control:focus  { border-color: var(--orange); }
        .form-control::placeholder { color: rgba(255,255,255,0.25); }
        .form-control option { background: #1A1A1A; }

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
            background: rgba(255,255,255,0.1);
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
            font-size: 15px;
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
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.6);
            transition: all 0.2s;
        }
        .pagination a:hover       { border-color: var(--orange); color: var(--orange); }
        .pagination .active       { background: var(--orange); border-color: var(--orange); color: #fff; }

        /* ── Product thumb ────────────────────────────────────────────────────── */
        .product-thumb {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 8px;
            background: rgba(255,255,255,0.05);
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
            background: rgba(249,115,22,0.08);
            border-color: rgba(249,115,22,0.3);
        }
        .admin-profile-link:hover .admin-avatar { box-shadow: 0 0 0 2px var(--orange); }
        .admin-profile-link:hover .admin-name   { color: var(--orange); }
        .admin-profile-link.active-profile {
            background: rgba(249,115,22,0.08);
            border-color: rgba(249,115,22,0.3);
        }
        .admin-profile-link.active-profile .admin-name { color: var(--orange); }

        /* ══════════════════════════════════════════════════════════════════════
           RESPONSIVE
        ══════════════════════════════════════════════════════════════════════ */
        @media (max-width: 1024px) {
            .chart-grid-2  { grid-template-columns: 1fr; }
            .form-grid-2   { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .sidebar  { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main     { margin-left: 0; }
            .hamburger { display: flex; }
            .admin-name { display: none; }
            .content  { padding: 14px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .stat-card  { padding: 14px; gap: 10px; }
            .stat-value { font-size: 22px; }
            .stat-icon  { width: 38px; height: 38px; }
            .chart-grid-2 { grid-template-columns: 1fr; }
            .card-header { flex-direction: column; align-items: flex-start; }
            .topbar { padding: 0 14px; }
            table { font-size: 13px; }
            thead th { padding: 10px 10px; font-size: 10px; }
            tbody td  { padding: 10px 10px; }
        }

        @media (max-width: 480px) {
            .stats-grid  { grid-template-columns: 1fr 1fr; gap: 8px; }
            .topbar-badge { display: none; }
            .content  { padding: 10px; }
            .stat-card { padding: 12px; gap: 8px; }
            .stat-value { font-size: 20px; }
            .card-header { padding: 12px 14px; }
            .card-body   { padding: 14px; }
        }
    </style>

    @stack('styles')
</head>

<body>

    {{-- ── Sidebar overlay (mobile) ───────────────────────────────────────────── --}}
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    {{-- ── Sidebar ─────────────────────────────────────────────────────────────── --}}
    <aside class="sidebar" id="sidebar">

        {{-- Logo --}}
        <div class="sidebar-logo">
            <a href="{{ route('dashboard.index') }}" style="display:flex; align-items:center; gap:12px; text-decoration:none;">
                {{-- Inline SVG logo — no file dependency, always renders --}}
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

        {{-- ── Navigation — ALL items are inside this <nav> ─────────────────────── --}}
        <nav class="sidebar-nav">

            {{-- MAIN --}}
            <div class="nav-section-label">Main</div>
            <a href="{{ route('dashboard.index') }}"
               class="nav-item {{ request()->routeIs('dashboard.index') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
                DASHBOARD
            </a>

            {{-- CATALOG --}}
            <div class="nav-section-label">Catalog</div>
            <a href="{{ route('dashboard.products') }}"
               class="nav-item {{ request()->routeIs('dashboard.products*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                </svg>
                PRODUCTS
            </a>
            <a href="{{ route('dashboard.banners') }}"
               class="nav-item {{ request()->routeIs('dashboard.banners*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path d="M3 9h18M9 21V9"/>
                </svg>
                BANNERS
            </a>

            {{-- SALES --}}
            <div class="nav-section-label">Sales</div>
            <a href="{{ route('dashboard.orders') }}"
               class="nav-item {{ request()->routeIs('dashboard.orders*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                    <rect x="9" y="3" width="6" height="4" rx="1"/>
                </svg>
                ORDERS
            </a>

            {{-- USERS --}}
            <div class="nav-section-label">Users</div>
            <a href="{{ route('dashboard.users') }}"
               class="nav-item {{ request()->routeIs('dashboard.users*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                </svg>
                USERS
            </a>

            {{-- PROMOTIONS --}}
            <div class="nav-section-label">Promotions</div>
            <a href="{{ route('dashboard.discounts') }}"
               class="nav-item {{ request()->routeIs('dashboard.discounts*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                    <line x1="7" y1="7" x2="7.01" y2="7"/>
                </svg>
                DISCOUNTS
            </a>

            {{-- SYSTEM --}}
            <div class="nav-section-label">System</div>
            <a href="{{ route('dashboard.settings') }}"
               class="nav-item {{ request()->routeIs('dashboard.settings*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
                </svg>
                SETTINGS
            </a>

            {{-- Staff & Permissions — visible only to admin / superadmin --}}
            @php $adminRole = Auth::guard('admin')->user()->role ?? 'viewer'; @endphp
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
                STAFF &amp; ROLES
            </a>
            @endif

        </nav>
        {{-- ── End nav ──────────────────────────────────────────────────────────── --}}

        {{-- Sidebar footer --}}
        <div class="sidebar-footer">
            <span>Tronmatix © 2025</span>
            <span>v1.0.0</span>
        </div>
    </aside>

    {{-- ── Main ─────────────────────────────────────────────────────────────────── --}}
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
                <span class="topbar-title">@yield('title', 'DASHBOARD')</span>
            </div>

            <div class="topbar-right">
                {{-- Notification bell --}}
                <div style="position:relative;" id="bell-wrapper">
                    <button id="bell-btn" onclick="toggleBell()" style="
                        position:relative; background:rgba(255,255,255,0.06);
                        border:1.5px solid rgba(255,255,255,0.1); border-radius:10px;
                        width:40px; height:40px; display:flex; align-items:center; justify-content:center;
                        cursor:pointer; transition:all .2s; font-size:18px;
                    " onmouseover="this.style.borderColor='#F97316'"
                       onmouseout="if(!bellOpen)this.style.borderColor='rgba(255,255,255,0.1)'">
                        🔔
                        <span id="bell-dot" style="
                            display:none; position:absolute; top:6px; right:6px;
                            width:10px; height:10px; border-radius:50%;
                            background:#F97316; border:2px solid #111;
                            animation:bellPulse 1.8s ease infinite;
                        "></span>
                    </button>

                    <div id="bell-dropdown" style="
                        display:none; position:absolute; top:48px; right:0; z-index:500;
                        width:320px; background:#141414; border:1px solid rgba(255,255,255,0.1);
                        border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,0.6);
                        overflow:hidden;
                    ">
                        <div style="padding:14px 18px; border-bottom:1px solid rgba(255,255,255,0.07);
                             display:flex; align-items:center; justify-content:space-between;">
                            <span style="font-size:13px; font-weight:800; letter-spacing:2px; color:rgba(255,255,255,0.6);">ALERTS</span>
                            <a href="{{ route('dashboard.settings') }}" style="font-size:11px; color:rgba(249,115,22,0.6);
                                text-decoration:none; letter-spacing:1px;"
                               onmouseover="this.style.color='#F97316'" onmouseout="this.style.color='rgba(249,115,22,0.6)'">
                                ⚙ SETTINGS
                            </a>
                        </div>
                        <div id="bell-list" style="max-height:320px; overflow-y:auto; padding:8px 0;">
                            <div style="padding:24px; text-align:center; color:rgba(255,255,255,0.25); font-family:Rajdhani,sans-serif;">
                                <div style="font-size:24px; margin-bottom:6px;">⏳</div>
                                Loading…
                            </div>
                        </div>
                        <div style="padding:10px 18px; border-top:1px solid rgba(255,255,255,0.07); text-align:center;">
                            <a href="{{ route('dashboard.settings') }}" style="font-size:12px; color:rgba(255,255,255,0.3);
                                text-decoration:none; letter-spacing:1px;"
                               onmouseover="this.style.color='#F97316'" onmouseout="this.style.color='rgba(255,255,255,0.3)'">
                                View All Settings →
                            </a>
                        </div>
                    </div>
                </div>

                <span class="topbar-badge">
                    {{ strtoupper(Auth::guard('admin')->user()->role ?? 'ADMIN') }}
                </span>

                @php
                    $_topbarAdmin = Auth::guard('admin')->user();
                    $_topbarAvatar = $_topbarAdmin->avatar
                        ? (Str::startsWith($_topbarAdmin->avatar, ['http://','https://'])
                            ? $_topbarAdmin->avatar
                            : asset('storage/' . $_topbarAdmin->avatar))
                        : null;
                @endphp
                <a href="{{ route('dashboard.profile') }}" class="admin-profile-link" title="My Profile">
                    <div class="admin-avatar">
                        @if($_topbarAvatar)
                            <img src="{{ $_topbarAvatar }}"
                                 alt="{{ $_topbarAdmin->name }}"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
                            <span style="display:none;width:100%;height:100%;align-items:center;justify-content:center;font-weight:700;font-size:14px;">
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
                    <button type="submit" class="btn btn-danger btn-sm">LOGOUT</button>
                </form>
            </div>
        </header>

        {{-- ── Flash messages
             FIX: only show layout flash when the page hasn't declared its own
             (pages that show floating toasts use @section('suppress_flash') @endsection)
        --}}
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

    {{-- ── Sidebar JS ───────────────────────────────────────────────────────────── --}}
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

        // Highlight profile link on profile page
        (function() {
            var link = document.querySelector('.admin-profile-link');
            if (link && window.location.pathname.includes('/dashboard/profile')) {
                link.classList.add('active-profile');
            }
        })();
    </script>

    @stack('scripts')

    {{-- ── Notification Bell ────────────────────────────────────────────────────── --}}
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
        #bell-list::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.1); border-radius:2px; }
        .bell-item {
            display:flex; align-items:flex-start; gap:12px;
            padding:11px 18px; text-decoration:none;
            transition:background .15s; border-bottom:1px solid rgba(255,255,255,0.04);
        }
        .bell-item:last-child { border-bottom:none; }
        .bell-item:hover { background:rgba(255,255,255,0.04); }
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
            document.getElementById('bell-btn').style.borderColor  = 'rgba(255,255,255,0.1)';
        }

        document.addEventListener('click', function(e) {
            if (bellOpen && !document.getElementById('bell-wrapper').contains(e.target)) closeBell();
        });

        function loadBellAlerts() {
            fetch('{{ route('dashboard.notifications') }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                const list = document.getElementById('bell-list');
                if (!data.alerts || data.alerts.length === 0) {
                    list.innerHTML = `
                        <div style="padding:28px; text-align:center; font-family:Rajdhani,sans-serif;">
                            <div style="font-size:30px; margin-bottom:8px;">🎉</div>
                            <div style="color:#22c55e; font-weight:700; font-size:14px; letter-spacing:1px;">All Clear!</div>
                            <div style="color:rgba(255,255,255,0.3); font-size:12px; margin-top:4px;">No alerts right now</div>
                        </div>`;
                    return;
                }
                list.innerHTML = data.alerts.map(a => `
                    <a href="${a.url}" class="bell-item" onclick="closeBell()">
                        <div style="width:36px;height:36px;border-radius:10px;flex-shrink:0;
                             background:${a.color}18;border:1px solid ${a.color}44;
                             display:flex;align-items:center;justify-content:center;font-size:18px;">
                             ${a.icon}
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-family:Rajdhani,sans-serif;font-size:13px;font-weight:700;
                                 color:${a.color};letter-spacing:.5px;">${a.title}</div>
                            <div style="font-size:12px;color:rgba(255,255,255,0.35);margin-top:2px;line-height:1.4;">${a.body}</div>
                        </div>
                        <div style="font-size:16px;color:rgba(255,255,255,0.2);flex-shrink:0;">›</div>
                    </a>`).join('');
            })
            .catch(() => {
                document.getElementById('bell-list').innerHTML =
                    '<div style="padding:20px;text-align:center;color:rgba(255,255,255,0.3);font-size:13px;">Failed to load alerts</div>';
            });
        }

        function pollBellDot() {
            fetch('{{ route('dashboard.notifications') }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('bell-dot').style.display = (data.count > 0) ? 'block' : 'none';
            })
            .catch(() => {});
        }

        pollBellDot();
        setInterval(pollBellDot, 60000);
    </script>

</body>
</html>