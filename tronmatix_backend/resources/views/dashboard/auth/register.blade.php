<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create First Admin — Tronmatix</title>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --orange: #F97316; --dark: #0A0A0A; --dark-800: #111111; --dark-700: #1A1A1A; }

        body {
            font-family: 'Rajdhani', sans-serif;
            background: var(--dark);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 20px; position: relative; overflow: hidden;
        }

        /* Background grid */
        body::before {
            content: ''; position: fixed; inset: 0;
            background-image:
                linear-gradient(rgba(249,115,22,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(249,115,22,0.03) 1px, transparent 1px);
            background-size: 50px 50px; pointer-events: none;
        }

        /* Glow orb */
        body::after {
            content: ''; position: fixed; top: -200px; left: 50%;
            transform: translateX(-50%); width: 700px; height: 700px;
            background: radial-gradient(circle, rgba(249,115,22,0.07) 0%, transparent 70%);
            pointer-events: none;
        }

        .auth-card {
            width: 100%; max-width: 460px;
            background: var(--dark-800);
            border: 1px solid rgba(249,115,22,0.15);
            border-radius: 20px; overflow: hidden;
            position: relative; z-index: 1;
            box-shadow: 0 25px 60px rgba(0,0,0,0.6), 0 0 0 1px rgba(249,115,22,0.05);
            animation: card-in 0.5s ease;
        }
        @keyframes card-in {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Top animated bar — gold/orange for superadmin */
        .auth-card-top {
            height: 4px;
            background: linear-gradient(90deg, transparent, #FFB020, #F97316, #FFB020, transparent);
            background-size: 200% 100%;
            animation: shimmer 2.5s linear infinite;
        }
        @keyframes shimmer {
            0%   { background-position: 200% center; }
            100% { background-position: -200% center; }
        }

        .auth-body { padding: 40px; }

        /* ── Logo ─────────────────────────────────────────────────────────── */
        .auth-logo {
            display: flex; flex-direction: column; align-items: center;
            margin-bottom: 28px;
        }
        .logo-svg {
            filter: drop-shadow(0 0 18px rgba(249,115,22,0.5));
            animation: logo-pulse 2.5s ease-in-out infinite;
        }
        @keyframes logo-pulse {
            0%,100% { filter: drop-shadow(0 0 10px rgba(249,115,22,0.3)); }
            50%      { filter: drop-shadow(0 0 28px rgba(249,115,22,0.7)); }
        }
        .brand-name {
            font-size: 22px; font-weight: 700; letter-spacing: 4px; color: #fff;
            margin-top: 10px;
        }
        .brand-sub { font-size: 10px; letter-spacing: 5px; color: var(--orange); margin-top: 2px; }

        /* ── Superadmin badge ─────────────────────────────────────────────── */
        .superadmin-badge {
            margin-top: 14px;
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(249,115,22,0.12);
            border: 1px solid rgba(249,115,22,0.4);
            color: #F97316;
            font-size: 11px; font-weight: 700; letter-spacing: 2.5px;
            padding: 5px 18px; border-radius: 20px;
            box-shadow: 0 0 16px rgba(249,115,22,0.15);
        }
        .badge-dot {
            width: 7px; height: 7px; border-radius: 50%; background: #F97316;
            box-shadow: 0 0 6px #F97316;
            animation: badge-pulse 1.8s ease-in-out infinite;
        }
        @keyframes badge-pulse {
            0%,100% { opacity: 1; transform: scale(1); }
            50%      { opacity: 0.5; transform: scale(0.75); }
        }

        /* ── One-time warning banner ──────────────────────────────────────── */
        .onetime-notice {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 12px 16px; border-radius: 12px; margin-bottom: 22px;
            background: rgba(249,115,22,0.07);
            border: 1px solid rgba(249,115,22,0.25);
        }
        .onetime-icon {
            width: 32px; height: 32px; border-radius: 8px; flex-shrink: 0;
            background: rgba(249,115,22,0.15); border: 1px solid rgba(249,115,22,0.3);
            display: flex; align-items: center; justify-content: center; font-size: 16px;
        }
        .onetime-text { flex: 1; }
        .onetime-title {
            font-size: 11px; font-weight: 800; letter-spacing: 2px;
            color: #F97316; margin-bottom: 3px;
        }
        .onetime-desc { font-size: 12px; color: rgba(255,255,255,0.45); line-height: 1.5; }

        /* ── Alerts ───────────────────────────────────────────────────────── */
        .alert {
            padding: 12px 16px; border-radius: 10px; margin-bottom: 18px;
            font-size: 13px; font-weight: 600;
            display: flex; align-items: flex-start; gap: 8px;
        }
        .alert-error { background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.25); color: #EF4444; }

        /* ── Form ─────────────────────────────────────────────────────────── */
        .form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-group { margin-bottom: 16px; }
        .form-label {
            display: block; font-size: 10px; letter-spacing: 2px;
            color: rgba(255,255,255,0.35); margin-bottom: 7px; text-transform: uppercase;
        }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
            color: rgba(255,255,255,0.25);
        }
        .form-control {
            width: 100%; background: var(--dark-700);
            border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;
            padding: 12px 13px 12px 40px; color: #fff;
            font-family: 'Rajdhani', sans-serif; font-size: 14px; font-weight: 500;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            outline: none; border-color: var(--orange);
            box-shadow: 0 0 0 3px rgba(249,115,22,0.12);
        }
        .form-control::placeholder { color: rgba(255,255,255,0.2); }
        .form-control.is-invalid   { border-color: #EF4444; }

        .pass-toggle {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: rgba(255,255,255,0.25);
            cursor: pointer; padding: 0; transition: color 0.2s;
        }
        .pass-toggle:hover { color: var(--orange); }

        .field-error { color: #EF4444; font-size: 11px; margin-top: 4px; }

        /* ── Password strength ────────────────────────────────────────────── */
        .strength-bar  { height: 3px; background: rgba(255,255,255,0.07); border-radius: 3px; margin-top: 8px; overflow: hidden; }
        .strength-fill { height: 100%; border-radius: 3px; transition: width 0.3s, background 0.3s; width: 0%; }
        .strength-label { font-size: 11px; margin-top: 4px; color: rgba(255,255,255,0.3); }

        /* ── Submit button ────────────────────────────────────────────────── */
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #F97316, #ea580c);
            border: none; border-radius: 12px; padding: 14px; color: #fff;
            font-family: 'Rajdhani', sans-serif; font-size: 14px; font-weight: 700;
            letter-spacing: 2px; text-transform: uppercase; cursor: pointer;
            transition: all 0.2s; margin-top: 8px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 4px 20px rgba(249,115,22,0.3);
        }
        .btn-submit:hover    { transform: translateY(-1px); box-shadow: 0 8px 28px rgba(249,115,22,0.45); }
        .btn-submit:active   { transform: translateY(0); }
        .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner {
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff;
            border-radius: 50%; animation: spin 0.8s linear infinite; display: none;
        }
        .btn-submit.loading .spinner  { display: block; }
        .btn-submit.loading .btn-text { display: none; }

        .auth-footer {
            text-align: center; margin-top: 22px;
            font-size: 13px; color: rgba(255,255,255,0.3);
        }
        .auth-footer a { color: var(--orange); text-decoration: none; font-weight: 600; }
        .auth-footer a:hover { text-decoration: underline; }

        @media(max-width:480px) {
            .auth-body { padding: 28px 24px 32px; }
            .form-row  { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="auth-card-top"></div>
    <div class="auth-body">

        {{-- Logo + Superadmin badge --}}
        <div class="auth-logo">
            <svg class="logo-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="48" height="48">
                <defs>
                    <linearGradient id="lg" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#FFB020"/>
                        <stop offset="100%" style="stop-color:#F97316"/>
                    </linearGradient>
                </defs>
                <polygon points="50,4 90,26 90,74 50,96 10,74 10,26" fill="#1e1e1e" stroke="#F97316" stroke-width="4"/>
                <polygon points="54,18 32,54 48,54 44,82 68,46 52,46" fill="url(#lg)"/>
            </svg>
            <div class="brand-name">TRONMATIX</div>
            <div class="brand-sub">COMPUTER</div>
            <div class="superadmin-badge">
                <div class="badge-dot"></div>
                👑 SUPERADMIN SETUP
            </div>
        </div>

        {{-- One-time warning --}}
        <div class="onetime-notice">
            <div class="onetime-icon">🔐</div>
            <div class="onetime-text">
                <div class="onetime-title">FIRST-TIME SETUP ONLY</div>
                <div class="onetime-desc">
                    This page is only available when <strong style="color:rgba(255,255,255,0.7);">no admin accounts exist</strong>.
                    After creation, registration closes permanently. This account will have full <strong style="color:#F97316;">Superadmin</strong> access.
                </div>
            </div>
        </div>

        {{-- Validation errors --}}
        @if($errors->any())
            <div class="alert alert-error">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Register Form --}}
        <form method="POST" action="{{ route('dashboard.register.post') }}" id="registerForm">
            @csrf

            {{-- Name + Username --}}
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        <input type="text" name="name"
                               class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               value="{{ old('name') }}" placeholder="Full name" autofocus />
                    </div>
                    @error('name')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Username</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            </svg>
                        </span>
                        <input type="text" name="username"
                               class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}"
                               value="{{ old('username') }}" placeholder="username" />
                    </div>
                    @error('username')<div class="field-error">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Email --}}
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </span>
                    <input type="email" name="email"
                           class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                           value="{{ old('email') }}" placeholder="superadmin@tronmatix.com" />
                </div>
                @error('email')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    </span>
                    <input type="password" name="password" id="password"
                           class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                           placeholder="Min 8 chars, upper + number"
                           oninput="checkStrength(this.value)" />
                    <button type="button" class="pass-toggle" onclick="togglePass('password', this)">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                <div class="strength-label" id="strengthLabel"></div>
                @error('password')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            {{-- Confirm Password --}}
            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    </span>
                    <input type="password" name="password_confirmation" id="passwordConfirm"
                           class="form-control" placeholder="Repeat password" />
                    <button type="button" class="pass-toggle" onclick="togglePass('passwordConfirm', this)">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-submit" id="submitBtn">
                <span class="spinner"></span>
                <span class="btn-text">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                         style="display:inline;vertical-align:middle;margin-right:6px;">
                        <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                        <circle cx="8.5" cy="7" r="4"/>
                        <line x1="20" y1="8" x2="20" y2="14"/>
                        <line x1="23" y1="11" x2="17" y2="11"/>
                    </svg>
                    CREATE SUPERADMIN ACCOUNT
                </span>
            </button>
        </form>

        <div class="auth-footer">
            Already have an account?
            <a href="{{ route('dashboard.login') }}">Login here</a>
        </div>

    </div>
</div>

<script>
    function togglePass(id, btn) {
        const input = document.getElementById(id);
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    function checkStrength(val) {
        const fill  = document.getElementById('strengthFill');
        const label = document.getElementById('strengthLabel');
        let score   = 0;
        if (val.length >= 8)           score++;
        if (/[A-Z]/.test(val))         score++;
        if (/[0-9]/.test(val))         score++;
        if (/[^A-Za-z0-9]/.test(val))  score++;

        const levels = [
            { pct: '0%',   color: 'transparent', text: '' },
            { pct: '25%',  color: '#EF4444',      text: 'Weak' },
            { pct: '50%',  color: '#EAB308',      text: 'Fair' },
            { pct: '75%',  color: '#3B82F6',      text: 'Good' },
            { pct: '100%', color: '#22C55E',      text: 'Strong ✓' },
        ];
        const level        = val.length === 0 ? 0 : score;
        fill.style.width      = levels[level].pct;
        fill.style.background = levels[level].color;
        label.textContent     = levels[level].text;
        label.style.color     = levels[level].color;
    }

    document.getElementById('registerForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.classList.add('loading');
        btn.disabled = true;
    });
</script>

</body>
</html>
