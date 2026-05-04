<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login — Tronmatix</title>
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
        body::before {
            content: ''; position: fixed; inset: 0;
            background-image:
                linear-gradient(rgba(249,115,22,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(249,115,22,0.03) 1px, transparent 1px);
            background-size: 50px 50px; pointer-events: none;
        }
        body::after {
            content: ''; position: fixed; top: -200px; left: 50%;
            transform: translateX(-50%); width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(249,115,22,0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .auth-card {
            width: 100%; max-width: 420px;
            background: var(--dark-800);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px; overflow: hidden;
            position: relative; z-index: 1;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
            animation: card-in 0.5s ease;
        }
        @keyframes card-in {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .auth-card-top {
            height: 4px;
            background: linear-gradient(90deg, transparent, var(--orange), transparent);
            transition: background 0.4s ease;
        }
        .auth-body { padding: 36px 40px 40px; }

        /* ── Logo ─────────────────────────────────────────────────────────── */
        .auth-logo {
            display: flex; flex-direction: column; align-items: center;
            margin-bottom: 24px;
        }
        .brand-name { font-size: 22px; font-weight: 700; letter-spacing: 4px; color: #fff; margin-top: 10px; }
        .brand-sub  { font-size: 10px; letter-spacing: 5px; color: var(--orange); margin-top: 2px; }

        /* ── FULL-WIDTH SLIDING TOGGLE ────────────────────────────────────── */
        .mode-toggle-wrap {
            margin-top: 20px;
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            padding: 5px;
            position: relative;
        }

        /* The sliding background pill */
        .mode-slider {
            position: absolute;
            top: 5px; left: 5px;
            width: calc(50% - 5px);
            height: calc(100% - 10px);
            border-radius: 10px;
            background: var(--orange);
            box-shadow: 0 2px 12px rgba(249,115,22,0.4);
            transition: transform 0.3s cubic-bezier(0.34,1.2,0.64,1),
                        background 0.3s ease,
                        box-shadow 0.3s ease;
            pointer-events: none;
            z-index: 0;
        }
        .mode-slider.staff-side {
            transform: translateX(calc(100% + 0px));
            background: #3b82f6;
            box-shadow: 0 2px 12px rgba(59,130,246,0.4);
        }

        .mode-toggle-btns {
            display: flex;
            position: relative; z-index: 1;
        }
        .mode-btn {
            flex: 1;
            padding: 11px 0;
            border: none; border-radius: 10px;
            font-family: 'Rajdhani', sans-serif;
            font-size: 13px; font-weight: 800; letter-spacing: 2px;
            cursor: pointer; transition: color 0.25s ease;
            background: transparent;
            display: flex; align-items: center; justify-content: center; gap: 7px;
            color: rgba(255,255,255,0.35);
            position: relative; z-index: 1;
        }
        .mode-btn.active { color: #fff; }
        .mode-btn:not(.active):hover { color: rgba(255,255,255,0.65); }

        /* Subtle divider between the two buttons */
        .mode-divider {
            width: 1px;
            background: rgba(255,255,255,0.08);
            margin: 8px 0;
            flex-shrink: 0;
            transition: opacity 0.2s;
        }
        .mode-divider.hidden { opacity: 0; }

        /* ── Alerts ──────────────────────────────────────────────────────── */
        .alert {
            padding: 12px 16px; border-radius: 10px; margin-bottom: 20px;
            font-size: 13px; font-weight: 600;
            display: flex; align-items: center; gap: 8px;
            animation: slide-in 0.3s ease;
        }
        @keyframes slide-in {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .alert-error   { background: rgba(239,68,68,0.08);  border: 1px solid rgba(239,68,68,0.25);  color: #EF4444; }
        .alert-success { background: rgba(34,197,94,0.08);  border: 1px solid rgba(34,197,94,0.25);  color: #22C55E; }

        /* ── Form ────────────────────────────────────────────────────────── */
        .form-group  { margin-bottom: 18px; }
        .form-label  {
            display: block; font-size: 10px; letter-spacing: 2px;
            color: rgba(255,255,255,0.35); margin-bottom: 8px; text-transform: uppercase;
        }
        .input-wrap  { position: relative; }
        .input-icon  {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: rgba(255,255,255,0.25); display: flex;
        }
        .form-control {
            width: 100%; background: var(--dark-700);
            border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;
            padding: 13px 14px 13px 42px; color: #fff;
            font-family: 'Rajdhani', sans-serif; font-size: 15px; font-weight: 500;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            outline: none; border-color: var(--active-color, var(--orange));
            box-shadow: 0 0 0 3px rgba(var(--active-rgb, 249,115,22), 0.12);
        }
        .form-control::placeholder { color: rgba(255,255,255,0.2); }
        .form-control.is-invalid { border-color: #EF4444; }

        .pass-toggle {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: rgba(255,255,255,0.25);
            cursor: pointer; padding: 0; transition: color 0.2s;
        }
        .pass-toggle:hover { color: var(--active-color, var(--orange)); }

        .remember-row {
            display: flex; align-items: center; margin-bottom: 24px;
        }
        .checkbox-wrap { display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .checkbox-wrap input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--active-color, var(--orange)); cursor: pointer; }
        .checkbox-wrap span { font-size: 13px; color: rgba(255,255,255,0.45); }

        /* ── Submit button ────────────────────────────────────────────────── */
        .btn-submit {
            width: 100%; border: none; border-radius: 12px;
            padding: 14px; color: #fff;
            font-family: 'Rajdhani', sans-serif; font-size: 14px; font-weight: 700;
            letter-spacing: 2px; text-transform: uppercase; cursor: pointer;
            transition: all 0.3s ease;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            background: linear-gradient(135deg, #F97316, #ea580c);
            box-shadow: 0 4px 20px rgba(249,115,22,0.35);
        }
        .btn-submit:hover    { transform: translateY(-1px); box-shadow: 0 8px 28px rgba(249,115,22,0.45); }
        .btn-submit:active   { transform: translateY(0); }
        .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .btn-submit.staff-mode {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            box-shadow: 0 4px 20px rgba(59,130,246,0.35);
        }
        .btn-submit.staff-mode:hover { box-shadow: 0 8px 28px rgba(59,130,246,0.45); }

        .divider {
            display: flex; align-items: center; gap: 12px; margin: 22px 0;
        }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.07); }
        .divider span { font-size: 11px; color: rgba(255,255,255,0.2); letter-spacing: 2px; }

        .auth-footer { text-align: center; font-size: 13px; color: rgba(255,255,255,0.3); }
        .auth-footer a { color: var(--orange); text-decoration: none; font-weight: 600; }
        .auth-footer a:hover { text-decoration: underline; }

        .field-error {
            color: #EF4444; font-size: 11px; margin-top: 5px;
            display: flex; align-items: center; gap: 4px;
        }

        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner {
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff;
            border-radius: 50%; animation: spin 0.8s linear infinite; display: none;
        }
        .btn-submit.loading .spinner  { display: block; }
        .btn-submit.loading .btn-text { display: none; }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="auth-card-top" id="top-bar"></div>
    <div class="auth-body">

        {{-- Logo --}}
        <div class="auth-logo">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="44" height="44">
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

            {{-- ── Full-width sliding toggle ──────────────────────────────── --}}
            <div class="mode-toggle-wrap" style="width:100%;max-width:300px;">
                <div class="mode-slider" id="modeSlider"></div>
                <div class="mode-toggle-btns">
                    <button type="button" class="mode-btn active" id="btn-admin" onclick="setMode('admin')">
                        🛡️ ADMIN
                    </button>
                    <div class="mode-divider" id="modeDivider"></div>
                    <button type="button" class="mode-btn" id="btn-staff" onclick="setMode('staff')">
                        👥 STAFF
                    </button>
                </div>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('error'))
            <div class="alert alert-error">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Login Form --}}
        <form method="POST" action="{{ route('dashboard.login.post') }}" id="loginForm">
            @csrf
            <input type="hidden" name="mode" id="modeHint" value="admin" />

            <div class="form-group">
                <label class="form-label">Username or Email</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </span>
                    <input type="text" name="login"
                           class="form-control {{ $errors->has('login') ? 'is-invalid' : '' }}"
                           value="{{ old('login') }}"
                           placeholder="Admin username or email"
                           id="loginInput"
                           autocomplete="username" autofocus />
                </div>
                @error('login')
                    <div class="field-error">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    </span>
                    <input type="password" name="password" id="passwordInput"
                           class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                           placeholder="Enter password"
                           autocomplete="current-password" />
                    <button type="button" class="pass-toggle" onclick="togglePassword()">
                        <svg id="eyeIcon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <div class="field-error">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="remember-row">
                <label class="checkbox-wrap">
                    <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} />
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                <span class="spinner"></span>
                <span class="btn-text" id="submitText">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                         style="display:inline;vertical-align:middle;margin-right:6px;">
                        <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/>
                    </svg>
                    LOGIN TO DASHBOARD
                </span>
            </button>
        </form>

        <div class="divider" id="orDivider"><span>OR</span></div>
        <div class="auth-footer" id="requestAccessFooter" style="display:none;">
            Need access? <a href="{{ route('dashboard.request-access') }}">Request staff access</a>
        </div>

    </div>
</div>

<script>
    var currentMode = 'admin';

    function setMode(mode) {
        currentMode = mode;
        const isStaff = mode === 'staff';

        // Slide the pill
        document.getElementById('modeSlider').classList.toggle('staff-side', isStaff);

        // Active button text colour
        document.getElementById('btn-admin').classList.toggle('active', !isStaff);
        document.getElementById('btn-staff').classList.toggle('active', isStaff);

        // Hide divider when one side is selected (it's behind the pill)
        document.getElementById('modeDivider').classList.toggle('hidden', true);
        setTimeout(() => document.getElementById('modeDivider').classList.toggle('hidden', false), 300);

        // Update hidden mode field
        document.getElementById('modeHint').value = mode;

        // Placeholder
        document.getElementById('loginInput').placeholder = isStaff
            ? 'Staff username or email'
            : 'Admin username or email';

        // Card top bar colour
        document.getElementById('top-bar').style.background = isStaff
            ? 'linear-gradient(90deg, transparent, #3b82f6, transparent)'
            : 'linear-gradient(90deg, transparent, #F97316, transparent)';

        // Button colour
        document.getElementById('submitBtn').classList.toggle('staff-mode', isStaff);

        // Show/hide OR divider and Request Access link based on mode
        // Request Access should ONLY appear in Staff mode
        document.getElementById('orDivider').style.display = isStaff ? 'flex' : 'none';
        document.getElementById('requestAccessFooter').style.display = isStaff ? 'block' : 'none';
    }

    function togglePassword() {
        const input = document.getElementById('passwordInput');
        const icon  = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24M1 1l22 22"/>';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }
    }

    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.classList.add('loading');
        btn.disabled = true;
    });
</script>

</body>
</html>
