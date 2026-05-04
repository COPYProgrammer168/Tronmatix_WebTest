{{--
    USAGE: @include('dashboard._permission_check', ['feature' => 'orders'])
    Features: dashboard | products | orders | orders_edit | users | discounts | settings | staff
--}}
@php
    use Illuminate\Support\Facades\Auth;
    use App\Models\AdminSetting;

    $_adminUser  = Auth::guard('admin')->user() ?? Auth::guard('staff')->user(); // dual-guard
    $_role       = $_adminUser?->role ?? 'editor';
    $_feature    = $feature ?? 'dashboard';

    if ($_role === 'superadmin') {
        $_hasAccess = true;
    } else {
        $_permKey = "perm_{$_role}_{$_feature}";
        $_defaults = [
            // admin — full access, locked in controller
            'admin_dashboard'=>'1','admin_products'=>'1','admin_orders'=>'1',
            'admin_orders_edit'=>'1','admin_users'=>'1','admin_discounts'=>'1',
            'admin_settings'=>'1','admin_staff'=>'1',
            // editor — content only, no sensitive admin pages
            'editor_dashboard'=>'1','editor_products'=>'1','editor_orders'=>'1',
            'editor_orders_edit'=>'0','editor_users'=>'0','editor_discounts'=>'1',
            'editor_settings'=>'0','editor_staff'=>'0',
            // seller — products, orders & discounts
            'seller_dashboard'=>'1','seller_products'=>'1','seller_orders'=>'1',
            'seller_orders_edit'=>'1','seller_users'=>'0','seller_discounts'=>'1',
            'seller_settings'=>'0','seller_staff'=>'0',
            // delivery — orders view & edit only
            'delivery_dashboard'=>'1','delivery_products'=>'0','delivery_orders'=>'1',
            'delivery_orders_edit'=>'1','delivery_users'=>'0','delivery_discounts'=>'0',
            'delivery_settings'=>'0','delivery_staff'=>'0',
            // developer — read access, no admin-sensitive pages
            'developer_dashboard'=>'1','developer_products'=>'1','developer_orders'=>'1',
            'developer_orders_edit'=>'0','developer_users'=>'0','developer_discounts'=>'0',
            'developer_settings'=>'0','developer_staff'=>'0',
        ];
        $_savedValue = AdminSetting::get($_permKey, $_defaults["{$_role}_{$_feature}"] ?? '0');
        $_hasAccess  = $_savedValue === '1';
    }

    $_roleMeta = [
        'superadmin' => ['color' => '#F97316', 'icon' => '👑', 'label' => __('dashboard.roles.superadmin')],
        'admin'      => ['color' => '#F97316', 'icon' => '🛡️', 'label' => __('dashboard.roles.admin')],
        'editor'     => ['color' => '#3b82f6', 'icon' => '✏️',  'label' => __('dashboard.roles.editor')],
        'seller'     => ['color' => '#10b981', 'icon' => '🏪',  'label' => __('dashboard.roles.seller')],
        'delivery'   => ['color' => '#a855f7', 'icon' => '🚚',  'label' => __('dashboard.roles.delivery')],
        'developer'  => ['color' => '#06b6d4', 'icon' => '💻',  'label' => __('dashboard.roles.developer')],
    ];
    $_rm = $_roleMeta[$_role] ?? ['color' => '#6b7280', 'icon' => '❓', 'label' => strtoupper($_role)];
@endphp

@if(!$_hasAccess)
{{-- ══════════════════ ACCESS DENIED ══════════════════════════════════════ --}}
<div style="
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    min-height:60vh; text-align:center; padding:40px 20px;
    font-family:Rajdhani,sans-serif;
    animation:fadeUp .45s ease both;
">
    {{-- Lock icon --}}
    <div style="
        width:96px; height:96px; border-radius:28px; margin-bottom:28px;
        background:rgba(239,68,68,0.08); border:1.5px solid rgba(239,68,68,0.25);
        display:flex; align-items:center; justify-content:center; font-size:46px;
        box-shadow:0 0 60px rgba(239,68,68,0.12);
        animation:lockPulse 2.5s ease-in-out infinite;
    ">🔒</div>

    {{-- Title --}}
    <div style="font-size:30px; font-weight:900; letter-spacing:3px; color:#ef4444; margin-bottom:8px;">
        {{ strtoupper(__('dashboard.access.denied')) }}
    </div>
    <div style="font-size:14px; color:rgba(255,255,255,0.35); margin-bottom:32px; max-width:380px; line-height:1.6;">
        {{ __('dashboard.access.desc') }}
    </div>

    {{-- Role badge --}}
    <div style="
        display:inline-flex; align-items:center; gap:10px;
        padding:12px 24px; border-radius:16px; margin-bottom:32px;
        background:{{ $_rm['color'] }}12; border:1.5px solid {{ $_rm['color'] }}40;
    ">
        <span style="font-size:22px;">{{ $_rm['icon'] }}</span>
        <div style="text-align:left;">
            <div style="font-size:10px; color:rgba(255,255,255,0.4); letter-spacing:2px; font-weight:700;">
                {{ strtoupper(__('dashboard.access.yourRole')) }}
            </div>
            <div style="font-size:16px; font-weight:800; color:{{ $_rm['color'] }}; letter-spacing:1px;">
                {{ strtoupper($_rm['label']) }}
            </div>
        </div>
        <div style="width:1px; height:32px; background:rgba(255,255,255,0.1); margin:0 4px;"></div>
        <div style="text-align:left;">
            <div style="font-size:10px; color:rgba(255,255,255,0.4); letter-spacing:2px; font-weight:700;">
                {{ strtoupper(__('dashboard.access.module')) }}
            </div>
            <div style="font-size:16px; font-weight:800; color:rgba(255,255,255,0.6); letter-spacing:1px;">
                {{ strtoupper(str_replace('_',' ',$_feature)) }}
            </div>
        </div>
    </div>

    {{-- Permission matrix mini preview --}}
    @php
        $_allFeatures = ['dashboard'=>'📊','products'=>'📦','orders'=>'📋','orders_edit'=>'✏️','users'=>'👥','discounts'=>'🏷️','settings'=>'⚙️','staff'=>'🛡️'];
        $_fDefaultsAll = [
            'admin_dashboard'=>'1','admin_products'=>'1','admin_orders'=>'1',
            'admin_orders_edit'=>'1','admin_users'=>'1','admin_discounts'=>'1',
            'admin_settings'=>'1','admin_staff'=>'1',
            'editor_dashboard'=>'1','editor_products'=>'1','editor_orders'=>'1',
            'editor_orders_edit'=>'0','editor_users'=>'0','editor_discounts'=>'1',
            'editor_settings'=>'0','editor_staff'=>'0',
            'seller_dashboard'=>'1','seller_products'=>'1','seller_orders'=>'1',
            'seller_orders_edit'=>'1','seller_users'=>'0','seller_discounts'=>'1',
            'seller_settings'=>'0','seller_staff'=>'0',
            'delivery_dashboard'=>'1','delivery_products'=>'0','delivery_orders'=>'1',
            'delivery_orders_edit'=>'1','delivery_users'=>'0','delivery_discounts'=>'0',
            'delivery_settings'=>'0','delivery_staff'=>'0',
            'developer_dashboard'=>'1','developer_products'=>'1','developer_orders'=>'1',
            'developer_orders_edit'=>'0','developer_users'=>'0','developer_discounts'=>'0',
            'developer_settings'=>'0','developer_staff'=>'0',
        ];
    @endphp
    <div style="
        background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08);
        border-radius:16px; padding:20px 24px; margin-bottom:32px;
        max-width:480px; width:100%;
    ">
        <div style="font-size:11px; color:rgba(255,255,255,0.3); letter-spacing:2px; font-weight:700; margin-bottom:16px; text-align:left;">
            {{ strtoupper(__('dashboard.access.overview')) }}
        </div>
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:10px;">
            @foreach($_allFeatures as $_fKey => $_fIcon)
            @php
                $_fPermKey = "perm_{$_role}_{$_fKey}";
                $_fHas = $_role === 'superadmin' || (AdminSetting::get($_fPermKey, $_fDefaultsAll["{$_role}_{$_fKey}"] ?? '0') === '1');
                $_fActive = $_fKey === $_feature;
            @endphp
            <div style="
                display:flex; flex-direction:column; align-items:center; gap:4px;
                padding:10px 6px; border-radius:10px;
                background:{{ $_fActive ? 'rgba(239,68,68,0.10)' : ($_fHas ? 'rgba(34,197,94,0.07)' : 'rgba(255,255,255,0.03)') }};
                border:1px solid {{ $_fActive ? 'rgba(239,68,68,0.3)' : ($_fHas ? 'rgba(34,197,94,0.2)' : 'rgba(255,255,255,0.06)') }};
            ">
                <span style="font-size:18px; {{ !$_fHas ? 'opacity:0.3;' : '' }}">{{ $_fIcon }}</span>
                <span style="font-size:9px; letter-spacing:1px; font-weight:700;
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
            background:#F97316; color:#fff; font-size:14px; font-weight:700;
            letter-spacing:1px; transition:all .2s;
            box-shadow:0 4px 16px rgba(249,115,22,0.3);
        " onmouseover="this.style.background='#fb923c'" onmouseout="this.style.background='#F97316'">
            🏠 {{ strtoupper(__('dashboard.btn.goToDashboard')) }}
        </a>
        <a href="javascript:history.back()" style="
            display:inline-flex; align-items:center; gap:8px;
            padding:12px 24px; border-radius:12px; text-decoration:none;
            background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12);
            color:rgba(255,255,255,0.6); font-size:14px; font-weight:700; letter-spacing:1px;
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

@php $_permDenied = true; @endphp
@else
@php $_permDenied = false; @endphp
@endif
