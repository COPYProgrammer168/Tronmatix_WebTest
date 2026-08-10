{{--
    USAGE: @include('dashboard._permission_check', ['feature' => 'orders'])
    Roles and features are read from the dynamic DB tables; fall back to hardcoded defaults.
--}}
@php
    use Illuminate\Support\Facades\Auth;
    use App\Models\AdminSetting;
    use App\Models\Role;

    $_adminUser = Auth::guard('admin')->user() ?? Auth::guard('staff')->user(); // dual-guard
    $_role      = $_adminUser?->role ?? 'editor';
    $_feature   = $feature ?? 'dashboard';

    if ($_role === 'superadmin') {
        $_hasAccess = true;
    } else {
        $_permKey    = "perm_{$_role}_{$_feature}";
        $_defaults   = AdminSetting::getDefaults();
        $_savedValue = AdminSetting::get($_permKey, $_defaults["{$_role}_{$_feature}"] ?? '0');
        $_hasAccess  = $_savedValue === '1';
    }

    // PHP globals persist across Blade @include scope boundaries
    $GLOBALS['_tronmatix_perm_denied'] = !$_hasAccess;

    if (!$_hasAccess) {
        $_rm = AdminSetting::dynamicRoleMeta()[$_role]
            ?? Role::metaMap()[$_role]
            ?? ['color' => '#6b7280', 'icon' => '❓', 'label' => strtoupper($_role)];
    }
@endphp

@if(!$_hasAccess)
    @include('dashboard.partials.access-denied', ['feature' => $_feature, 'role' => $_role, 'rm' => $_rm])
@endif
