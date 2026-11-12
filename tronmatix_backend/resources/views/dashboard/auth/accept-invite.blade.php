<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Activate Staff Account — Tronmatix</title>
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

        .auth-card-top {
            height: 4px;
            background: linear-gradient(90deg, transparent, #F97316, transparent);
        }

        .auth-body { padding: 40px; }

        .auth-logo {
            display: flex; flex-direction: column; align-items: center;
            margin-bottom: 24px;
        }
        .brand-name { font-size: 22px; font-weight: 700; letter-spacing: 4px; color: #fff; margin-top: 10px; }
        .brand-sub  { font-size: 10px; letter-spacing: 5px; color: var(--orange); margin-top: 2px; }

        .invite-badge {
            margin-top: 14px;
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(249,115,22,0.12);
            border: 1px solid rgba(249,115,22,0.4);
            color: #F97316;
            font-size: 11px; font-weight: 700; letter-spacing: 2.5px;
            padding: 5px 18px; border-radius: 20px;
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

        .alert {
            padding: 12px 16px; border-radius: 10px; margin-bottom: 18px;
            font-size: 13px; font-weight: 600;
            display: flex; align-items: flex-start; gap: 8px;
        }
        .alert-error { background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.25); color: #EF4444; }

        .form-group { margin-bottom: 16px; }
        .form-label {
            display: block; font-size: 10px; letter-spacing: 2px;
            color: rgba(255,255,255,0.35); margin-bottom: 7px; text-transform: uppercase;
        }
        .form-control {
            width: 100%; background: var(--dark-700);
            border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;
            padding: 12px 14px; color: #fff;
            font-family: 'Rajdhani', sans-serif; font-size: 14px; font-weight: 500;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            outline: none; border-color: var(--orange);
            box-shadow: 0 0 0 3px rgba(249,115,22,0.12);
        }
        .form-control::placeholder { color: rgba(255,255,255,0.2); }
        .form-control.is-invalid   { border-color: #EF4444; }
        .form-control[readonly] {
            opacity: 0.7; cursor: not-allowed; color: rgba(255,255,255,0.5);
        }

        .strength-bar  { height: 3px; background: rgba(255,255,255,0.07); border-radius: 3px; margin-top: 8px; overflow: hidden; }
        .strength-fill { height: 100%; border-radius: 3px; transition: width 0.3s, background 0.3s; width: 0%; }
        .strength-label { font-size: 11px; margin-top: 4px; color: rgba(255,255,255,0.3); }

        .pass-toggle {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: rgba(255,255,255,0.25);
            cursor: pointer; padding: 0; transition: color 0.2s;
        }
        .pass-toggle:hover { color: var(--orange); }
        .field-error { color: #EF4444; font-size: 11px; margin-top: 4px; }

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
    </style>
</head>
<body>

<div class="auth-card">
    <div class="auth-card-top"></div>
    <div class="auth-body">

        <div class="auth-logo">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="48" height="48">
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
            <div class="invite-badge">
                <div class="badge-dot"></div>
                🛡️ STAFF ACCOUNT ACTIVATION
            </div>
        </div>

        {{-- Info notice --}}
        <div class="onetime-notice">
            <div class="onetime-icon">🔐</div>
            <div class="onetime-text">
                <div class="onetime-title">SET YOUR PASSWORD</div>
                <div class="onetime-desc">
                    You've been invited as <strong style="color:#F97316;">{{ strtoupper($link->invite->role) }}</strong>.
                    Set a password below to activate your account. After that, log in through the dashboard
                    STAFF portal with the email shown.
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

        {{-- Accept form --}}
        <form method="POST" action="{{ route('dashboard.invite.accept', $link->token) }}" id="acceptForm">
            @csrf

            {{-- Read-only identity fields --}}
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" value="{{ $link->invite->name }}" readonly />
            </div>
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" value="{{ $link->invite->username }}" readonly />
            </div>
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="{{ $link->invite->email }}" placeholder="Enter your email"
                    {{ $link->invite->email ? 'readonly' : 'required' }} />
                @if(!$link->invite->email)
                    <div style="font-size:11px;color:rgba(255,255,255,0.35);margin-top:4px;">Set your email to use for login.</div>
                @endif
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label class="form-label">Password</label>
                <div style="position:relative;">
                    <input type="password" name="password" id="password"
                           class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                           placeholder="Min 8 chars, upper + number"
                           oninput="checkStrength(this.value)" autofocus />
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

            {{-- Confirm password --}}
            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <div style="position:relative;">
                    <input type="password" name="password_confirmation" id="passwordConfirm"
                           class="form-control" placeholder="Repeat password" />
                    <button type="button" class="pass-toggle" onclick="togglePass('passwordConfirm', this)">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                <span class="spinner"></span>
                <span class="btn-text">🔓 ACTIVATE ACCOUNT</span>
            </button>
        </form>

        <div class="auth-footer">
            Already active?
            <a href="{{ route('dashboard.login') }}">Login here</a>
        </div>

    </div>
</div>

<script>
    function togglePass(id) {
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

    document.getElementById('acceptForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.classList.add('loading');
        btn.disabled = true;
    });
</script>

</body>
</html>
