<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forgot Password — Tronmatix</title>
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

        .auth-card-top { height: 4px; background: linear-gradient(90deg, transparent, var(--orange), transparent); }
        .auth-body { padding: 36px 40px 40px; }

        .auth-logo {
            display: flex; flex-direction: column; align-items: center;
            margin-bottom: 24px;
        }
        .brand-name { font-size: 22px; font-weight: 700; letter-spacing: 4px; color: #fff; margin-top: 10px; }
        .brand-sub  { font-size: 10px; letter-spacing: 5px; color: var(--orange); margin-top: 2px; }

        .badge {
            margin-top: 14px;
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(249,115,22,0.12);
            border: 1px solid rgba(249,115,22,0.4);
            color: #F97316;
            font-size: 11px; font-weight: 700; letter-spacing: 2.5px;
            padding: 5px 18px; border-radius: 20px;
        }
        .badge.staff-mode {
            background: rgba(59,130,246,0.12);
            border: 1px solid rgba(59,130,246,0.4);
            color: #3b82f6;
        }

        .alert {
            padding: 12px 16px; border-radius: 10px; margin-bottom: 20px;
            font-size: 13px; font-weight: 600;
            display: flex; align-items: flex-start; gap: 8px;
        }
        .alert-error   { background: rgba(239,68,68,0.08);  border: 1px solid rgba(239,68,68,0.25);  color: #EF4444; }
        .alert-success { background: rgba(34,197,94,0.08);  border: 1px solid rgba(34,197,94,0.25);  color: #22C55E; }

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

        .auth-footer { text-align: center; font-size: 13px; color: rgba(255,255,255,0.3); }
        .auth-footer a { color: var(--orange); text-decoration: none; font-weight: 600; }
        .auth-footer a:hover { text-decoration: underline; }

        .toggle-row {
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 22px; font-size: 13px; color: rgba(255,255,255,0.45);
        }
        .toggle-row a { color: var(--orange); font-weight: 600; text-decoration: none; }
        .toggle-row a:hover { text-decoration: underline; }

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
            <div class="badge {{ $mode === 'staff' ? 'staff-mode' : '' }}" id="mode-badge">
                {{ $mode === 'staff' ? '👥 STAFF' : '🛡️ ADMIN' }} — FORGOT PASSWORD
            </div>
        </div>

        @if(session('status'))
            <div class="alert alert-success">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                {{ session('status') }}
            </div>
        @endif

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

        @php($cooldownSeconds = max((int) session('cooldown_seconds', 0), (int) session('ban_seconds', 0)))
        @if($cooldownSeconds > 0)
            <div class="alert alert-error">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <div>
                    @if(session('cooldown_seconds'))
                        You have already submitted this email. Please wait
                    @else
                        Too many attempts from this address. Please wait
                    @endif
                    <strong><span id="cooldownTimer" data-seconds="{{ $cooldownSeconds }}">0:00</span></strong>.
                </div>
            </div>
        @endif

        <div class="toggle-row" style="margin-top: 0;">
            🔑 Reset by email &nbsp;·&nbsp;
            <a href="{{ route('dashboard.password.phone', ['mode' => $mode]) }}">Use phone instead</a>
        </div>

        <form method="POST" action="{{ route('dashboard.password.email.post') }}" id="forgotForm">
            @csrf
            <input type="hidden" name="mode" value="{{ $mode }}" />

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </span>
                    <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                           value="{{ old('email') }}" placeholder="staff@tronmatix.com" required autofocus />
                </div>
                @error('email')<div style="color:#EF4444;font-size:11px;margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn-submit {{ $mode === 'staff' ? 'staff-mode' : '' }}" id="submitBtn">
                <span class="spinner"></span>
                <span class="btn-text">SEND RESET LINK</span>
            </button>
        </form>

        <div class="auth-footer" style="margin-top:22px;">
            Remembered your password?
            <a href="{{ route('dashboard.login') }}">Back to login</a>
        </div>

    </div>
</div>

<script>
    document.getElementById('forgotForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.classList.add('loading');
        btn.disabled = true;
    });

    // Live cooldown / ban countdown — disables the submit button until 0:00.
    (function () {
        const timer = document.getElementById('cooldownTimer');
        if (!timer) return;

        const btn = document.getElementById('submitBtn');
        let remaining = parseInt(timer.dataset.seconds, 10) || 0;

        const fmt = s => {
            const m = Math.floor(s / 60);
            const ss = s % 60;
            return m + ':' + String(ss).padStart(2, '0');
        };

        timer.textContent = fmt(remaining);
        if (remaining > 0) {
            if (btn) btn.disabled = true;
            const tick = setInterval(() => {
                remaining = Math.max(remaining - 1, 0);
                timer.textContent = fmt(remaining);
                if (remaining === 0) {
                    clearInterval(tick);
                    if (btn) btn.disabled = false;
                }
            }, 1000);
        }
    })();
</script>

</body>
</html>
