{{-- Access Denied UI Partial --}}
<div style="
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    min-height:30vh; text-align:center; padding:40px 20px;
    font-family: 'Rajdhani', 'Kdam Thmor Pro', sans-serif;
    animation:fadeUp .45s ease both;
">
    {{-- Lock icon --}}
    <div style="
        width:96px; height:96px; border-radius:28px; margin-bottom:28px;
        background:rgba(239,68,68,0.08); border:1.5px solid rgba(239,68,68,0.25);
        display:flex; align-items:center; justify-content:center; font-size: var(--title-size);
        box-shadow:0 0 60px rgba(239,68,68,0.12);
        animation:lockPulse 2.5s ease-in-out infinite;
    ">🔒</div>

    {{-- Title --}}
    <div style="font-size: var(--title-size); font-weight:900; color:#ef4444; margin-bottom:8px;">
        {{ strtoupper(__('dashboard.access.denied')) }}
    </div>
    <div style="font-size: var(--title-size); color:rgba(255,255,255,0.35); margin-bottom:32px; max-width:380px; line-height:1.6;">
        {{ __('dashboard.access.desc') }}
    </div>

    {{-- Role badge --}}
    <div style="
        display:inline-flex; align-items:center; gap:10px;
        padding:12px 24px; border-radius:16px; margin-bottom:32px;
        background:{{ $rm['color'] }}12; border:1.5px solid {{ $rm['color'] }}40;
    ">
        <span style="font-size: var(--title-size);">{{ $rm['icon'] }}</span>
        <div style="text-align:left;">
            <div style="font-size: var(--title-size); color:rgba(255,255,255,0.4); letter-spacing:2px; font-weight:500;">
                {{ strtoupper(__('dashboard.access.yourRole')) }}
            </div>
            <div style="font-size: var(--title-size); font-weight:500; color:{{ $rm['color'] }}; letter-spacing:1px;">
                {{ strtoupper($rm['label']) }}
            </div>
        </div>
        <div style="width:1px; height:32px; background:rgba(255,255,255,0.1); margin:0 4px;"></div>
        <div style="text-align:left;">
            <div style="font-size: var(--title-size); color:rgba(255,255,255,0.4); letter-spacing:2px; font-weight:500;">
                {{ strtoupper(__('dashboard.access.module')) }}
            </div>
            <div style="font-size: var(--title-size); font-weight:500; color:rgba(255,255,255,0.6); letter-spacing:1px;">
                {{ strtoupper(str_replace('_',' ',$feature)) }}
            </div>
        </div>
    </div>

    {{-- Permission matrix mini preview --}}
    @php
        use App\Models\AdminSetting;
        $_allFeatures = ['dashboard'=>'📊','products'=>'📦','orders'=>'📋','orders_edit'=>'✏️','users'=>'👥','discounts'=>'🏷️','settings'=>'⚙️','staff'=>'🛡️'];
        $_fDefaultsAll = AdminSetting::getDefaults();
    @endphp
    <div style="
        background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08);
        border-radius:16px; padding:20px 24px; margin-bottom:32px;
        max-width:480px; width:100%;
    ">
        <div style="font-size: var(--title-size); color:rgba(255,255,255,0.3); letter-spacing:2px; font-weight:700; margin-bottom:16px; text-align:left;">
            {{ strtoupper(__('dashboard.access.overview')) }}
        </div>
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:10px;">
            @foreach($_allFeatures as $_fKey => $_fIcon)
            @php
                $_fPermKey = "perm_{$role}_{$_fKey}";
                $_fHas = $role === 'superadmin' || (AdminSetting::get($_fPermKey, $_fDefaultsAll["{$role}_{$_fKey}"] ?? '0') === '1');
                $_fActive = $_fKey === $feature;
            @endphp
            <div style="
                display:flex; flex-direction:column; align-items:center; gap:4px;
                padding:10px 6px; border-radius:10px;
                background:{{ $_fActive ? 'rgba(239,68,68,0.10)' : ($_fHas ? 'rgba(34,197,94,0.07)' : 'rgba(255,255,255,0.03)') }};
                border:1px solid {{ $_fActive ? 'rgba(239,68,68,0.3)' : ($_fHas ? 'rgba(34,197,94,0.2)' : 'rgba(255,255,255,0.06)') }};
            ">
                <span style="font-size: var(--title-size); {{ !$_fHas ? 'opacity:0.3;' : '' }}">{{ $_fIcon }}</span>
                <span style="font-size: var(--title-size); letter-spacing:1px; font-weight:700;
                    color:{{ $_fActive ? '#ef4444' : ($_fHas ? '#22c55e' : 'rgba(255,255,255,0.2)') }};">
                    {{ $_fHas ? '✓' : '✗' }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Action buttons --}}
    <div style="display:flex; gap:12px; flex-wrap:wrap; justify-content:center;">
        <a href="{{ route('dashboard.index') }}" style="
            display:inline-flex; align-items:center; gap:8px;
            padding:12px 24px; border-radius:12px; text-decoration:none;
            background:#F97316; color:#fff; font-size: var(--title-size); font-weight:500;
            letter-spacing:1px; transition:all .2s;
            box-shadow:0 4px 16px rgba(249,115,22,0.3);
        " onmouseover="this.style.background='#fb923c'" onmouseout="this.style.background='#F97316'">
            🏠 {{ strtoupper(__('dashboard.btn.goToDashboard')) }}
        </a>
        <a href="javascript:history.back()" style="
            display:inline-flex; align-items:center; gap:8px;
            padding:12px 24px; border-radius:12px; text-decoration:none;
            background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12);
            color:rgba(255,255,255,0.6); font-size: var(--title-size); font-weight:500; letter-spacing:1px;
            transition:all .2s;
        " onmouseover="this.style.background='rgba(255,255,255,0.10)'" onmouseout="this.style.background='rgba(255,255,255,0.06)'">
            ← {{ strtoupper(__('dashboard.btn.goBack')) }}
        </a>
    </div>
</div>

<style>
@keyframes fadeUp   { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:none; } }
@keyframes lockPulse { 0%,100% { box-shadow:0 0 30px rgba(239,68,68,0.08); } 50% { box-shadow:0 0 60px rgba(239,68,68,0.22); } }
</style>
