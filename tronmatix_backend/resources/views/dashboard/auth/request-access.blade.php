<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Request Access — Tronmatix</title>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --orange: #F97316; --dark: #0A0A0A; --dark-800: #111111; --dark-700: #1A1A1A; }

        body {
            font-family: 'Rajdhani', sans-serif; background: var(--dark);
            min-height: 100vh; display: flex; align-items: center;
            justify-content: center; padding: 20px;
            position: relative; overflow: hidden;
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
            width: 100%; max-width: 480px; background: var(--dark-800);
            border: 1px solid rgba(255,255,255,0.08); border-radius: 20px;
            overflow: hidden; position: relative; z-index: 1;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5); animation: card-in 0.5s ease;
        }
        @keyframes card-in {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .auth-card-top { height: 4px; background: linear-gradient(90deg, transparent, var(--orange), transparent); }
        .auth-body     { padding: 36px 40px 40px; }

        .auth-logo {
            display: flex; flex-direction: column; align-items: center; margin-bottom: 24px;
        }
        .brand-name { font-size: 20px; font-weight: 700; letter-spacing: 4px; color: #fff; margin-top: 10px; }
        .brand-sub  { font-size: 9px; letter-spacing: 5px; color: var(--orange); margin-top: 2px; }

        /* ── Role badge strip ─────────────────────────────────────────────── */
        .role-badges {
            display: flex; gap: 6px; flex-wrap: wrap;
            justify-content: center; margin-top: 12px;
        }
        .role-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px; border-radius: 20px;
            font-size: 10px; font-weight: 700; letter-spacing: 1.5px;
        }
        .badge-editor    { background: rgba(59,130,246,0.10); border: 1px solid rgba(59,130,246,0.30); color: #3b82f6; }
        .badge-seller    { background: rgba(16,185,129,0.10); border: 1px solid rgba(16,185,129,0.30); color: #10b981; }
        .badge-delivery  { background: rgba(168,85,247,0.10); border: 1px solid rgba(168,85,247,0.30); color: #a855f7; }
        .badge-developer { background: rgba(6,182,212,0.10);  border: 1px solid rgba(6,182,212,0.30);  color: #06b6d4; }

        .alert {
            padding: 12px 16px; border-radius: 10px; margin-bottom: 18px;
            font-size: 13px; font-weight: 600;
            display: flex; align-items: flex-start; gap: 8px;
        }
        .alert-error   { background: rgba(239,68,68,0.08);  border: 1px solid rgba(239,68,68,0.25);  color: #EF4444; }
        .alert-success { background: rgba(34,197,94,0.08);  border: 1px solid rgba(34,197,94,0.25);  color: #22C55E; }
        .alert-info    { background: rgba(249,115,22,0.08); border: 1px solid rgba(249,115,22,0.25); color: var(--orange); }

        .form-row  { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-group { margin-bottom: 15px; }
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
        .form-control.no-icon { padding-left: 13px; }
        .form-control:focus {
            outline: none; border-color: var(--orange);
            box-shadow: 0 0 0 3px rgba(249,115,22,0.12);
        }
        .form-control::placeholder { color: rgba(255,255,255,0.2); }
        .form-control.is-invalid   { border-color: #EF4444; }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='rgba(255,255,255,0.3)' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 14px center;
            padding-right: 36px;
        }

        .pass-toggle {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: rgba(255,255,255,0.25);
            cursor: pointer; padding: 0; transition: color 0.2s;
        }
        .pass-toggle:hover { color: var(--orange); }

        .field-error { color: #EF4444; font-size: 11px; margin-top: 4px; }

        .strength-bar  { height: 3px; background: rgba(255,255,255,0.07); border-radius: 3px; margin-top: 8px; overflow: hidden; }
        .strength-fill { height: 100%; border-radius: 3px; transition: width 0.3s, background 0.3s; width: 0%; }
        .strength-label { font-size: 11px; margin-top: 4px; color: rgba(255,255,255,0.3); }

        .btn-submit {
            width: 100%; background: var(--orange); border: none; border-radius: 12px;
            padding: 14px; color: #fff; font-family: 'Rajdhani', sans-serif;
            font-size: 14px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
            cursor: pointer; transition: all 0.2s; margin-top: 8px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-submit:hover    { background: #ea580c; transform: translateY(-1px); box-shadow: 0 8px 24px rgba(249,115,22,0.35); }
        .btn-submit:active   { transform: translateY(0); }
        .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }

        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner {
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff;
            border-radius: 50%; animation: spin 0.8s linear infinite; display: none;
        }
        .btn-submit.loading .spinner  { display: block; }
        .btn-submit.loading .btn-text { display: none; }

        .auth-footer { text-align: center; font-size: 13px; color: rgba(255,255,255,0.3); margin-top: 20px; }
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
        </div>

        {{-- Info notice --}}
        <div class="alert alert-info" style="font-size:12px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>
            Your request will be reviewed by a superadmin before you can log in.
        </div>

        {{-- Errors --}}
        @if($errors->any())
            <div class="alert alert-error">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('dashboard.request-access.submit') }}" id="requestForm">
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
                           value="{{ old('email') }}" placeholder="you@tronmatix.com" />
                </div>
                @error('email')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            {{-- Requested Role — only valid staff roles, correct values --}}
            <div class="form-group">
                <label class="form-label">Requested Role</label>
                <div class="input-wrap">
                    <select name="requested_role"
                            class="form-control no-icon {{ $errors->has('requested_role') ? 'is-invalid' : '' }}">
                        <option value="editor"    {{ old('requested_role', 'editor') === 'editor'    ? 'selected' : '' }}>
                            ✏️ Editor — manage products &amp; content
                        </option>
                        <option value="seller"    {{ old('requested_role') === 'seller'    ? 'selected' : '' }}>
                            🏪 Seller — manage orders &amp; sales
                        </option>
                        <option value="delivery"  {{ old('requested_role') === 'delivery'  ? 'selected' : '' }}>
                            🚚 Delivery — handle order deliveries
                        </option>
                        <option value="developer" {{ old('requested_role') === 'developer' ? 'selected' : '' }}>
                            💻 Developer — technical &amp; system work
                        </option>
                    </select>
                </div>
                @error('requested_role')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            {{-- Message --}}
            <div class="form-group">
                <label class="form-label">Message (optional)</label>
                <textarea name="message" class="form-control no-icon" rows="2"
                          placeholder="Why do you need access?" style="resize:none;">{{ old('message') }}</textarea>
                @error('message')<div class="field-error">{{ $message }}</div>@enderror
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
                           placeholder="Min 8, upper + number"
                           oninput="checkStrength(this.value)" />
                    <button type="button" class="pass-toggle" onclick="togglePass('password')">
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
                    <button type="button" class="pass-toggle" onclick="togglePass('passwordConfirm')">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                <span class="spinner"></span>
                <span class="btn-text">SUBMIT REQUEST</span>
            </button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="{{ route('dashboard.login') }}">Login here</a>
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
        let score = 0;
        if (val.length >= 8)          score++;
        if (/[A-Z]/.test(val))        score++;
        if (/[0-9]/.test(val))        score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const levels = [
            { pct: '0%',   color: 'transparent', text: '' },
            { pct: '25%',  color: '#EF4444',      text: 'Weak' },
            { pct: '50%',  color: '#EAB308',      text: 'Fair' },
            { pct: '75%',  color: '#3B82F6',       text: 'Good' },
            { pct: '100%', color: '#22C55E',       text: 'Strong ✓' },
        ];
        const level = val.length === 0 ? 0 : score;
        fill.style.width      = levels[level].pct;
        fill.style.background = levels[level].color;
        label.textContent     = levels[level].text;
        label.style.color     = levels[level].color;
    }

    document.getElementById('requestForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.classList.add('loading');
        btn.disabled = true;
    });
</script>

</body>
</html>
