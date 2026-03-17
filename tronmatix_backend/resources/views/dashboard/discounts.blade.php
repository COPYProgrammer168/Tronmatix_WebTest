@extends('dashboard.layout')
@section('title', 'DISCOUNTS')

@section('content')

@php
    use App\Models\AdminSetting;
    $_pRole = Auth::guard('admin')->user()?->role ?? 'viewer';
    $_pFeat = 'products';
    $_pKey  = "perm_{$_pRole}_{$_pFeat}";
    $_pDef  = [
        'admin_dashboard'=>'1','admin_products'=>'1','admin_orders'=>'1',
        'admin_orders_edit'=>'1','admin_users'=>'1','admin_discounts'=>'1',
        'admin_settings'=>'1','admin_staff'=>'1',
        'editor_dashboard'=>'1','editor_products'=>'1','editor_orders'=>'1',
        'editor_orders_edit'=>'0','editor_users'=>'0','editor_discounts'=>'1',
        'editor_settings'=>'0','editor_staff'=>'0',
        'viewer_dashboard'=>'1','viewer_products'=>'0','viewer_orders'=>'1',
        'viewer_orders_edit'=>'0','viewer_users'=>'0','viewer_discounts'=>'0',
        'viewer_settings'=>'0','viewer_staff'=>'0',
    ];
    $_pAccess = $_pRole === 'superadmin'
        || (AdminSetting::get($_pKey, $_pDef["{$_pRole}_{$_pFeat}"] ?? '0') === '1');
    $_pRoleMeta = [
        'superadmin'=>['color'=>'#F97316','icon'=>'👑','label'=>'Super Admin'],
        'admin'     =>['color'=>'#F97316','icon'=>'🛡️','label'=>'Admin'],
        'editor'    =>['color'=>'#3b82f6','icon'=>'✏️', 'label'=>'Editor'],
        'viewer'    =>['color'=>'#a78bfa','icon'=>'👁️', 'label'=>'Viewer'],
    ];
    $_pRM = $_pRoleMeta[$_pRole] ?? $_pRoleMeta['viewer'];
    $_pAllFeats = ['dashboard'=>'📊','products'=>'📦','orders'=>'📋',
                   'orders_edit'=>'✏️','users'=>'👥','discounts'=>'🏷️',
                   'settings'=>'⚙️','staff'=>'🛡️'];
@endphp

@if(!$_pAccess)
<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:60vh;text-align:center;padding:40px 20px;font-family:Rajdhani,sans-serif;">
    <div style="font-size:46px;margin-bottom:20px;">🔒</div>
    <div style="font-size:28px;font-weight:900;letter-spacing:3px;color:#ef4444;margin-bottom:8px;">ACCESS DENIED</div>
    <div style="font-size:14px;color:rgba(255,255,255,0.35);margin-bottom:24px;">Contact a <span style="color:#F97316;font-weight:700;">Super Admin</span> to request access.</div>
    <a href="{{ route('dashboard.index') }}" style="padding:12px 24px;border-radius:12px;text-decoration:none;background:#F97316;color:#fff;font-size:14px;font-weight:700;">🏠 GO TO DASHBOARD</a>
</div>
@else



{{-- Header --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
        <p style="color:rgba(255,255,255,0.8); font-size:20px;">{{ $discounts->count() }} coupons total</p>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <button onclick="openModal()" class="btn btn-orange" style="font-size:15px; display:inline-flex; align-items:center; gap:7px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            ADD DISCOUNT
        </button>
    </div>
</div>

{{-- Stats row --}}
@php
    $activeCount     = $discounts->where('is_active', true)->filter(fn($d) => !($d->expires_at && $d->expires_at->isPast()) && !($d->max_uses && $d->used_count >= $d->max_uses))->count();
    $expiredCount    = $discounts->filter(fn($d) => $d->expires_at && $d->expires_at->isPast())->count();
    $exhaustedCount  = $discounts->filter(fn($d) => $d->max_uses && $d->used_count >= $d->max_uses)->count();
    $disabledCount   = $discounts->where('is_active', false)->count();
    $totalUses       = $discounts->sum('used_count');
@endphp
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:12px; margin-bottom:24px;">
    @foreach([
        ['label'=>'ACTIVE',    'val'=>$activeCount,    'color'=>'#22c55e'],
        ['label'=>'EXPIRED',   'val'=>$expiredCount,   'color'=>'#ef4444'],
        ['label'=>'EXHAUSTED', 'val'=>$exhaustedCount, 'color'=>'#a855f7'],
        ['label'=>'DISABLED',  'val'=>$disabledCount,  'color'=>'#6b7280'],
        ['label'=>'TOTAL USES','val'=>$totalUses,       'color'=>'#F97316'],
    ] as $stat)
    <div style="background:#1a1a1a; border:1px solid rgba(255,255,255,0.07); border-radius:12px; padding:14px 18px;">
        <div style="font-size:11px; letter-spacing:2px; color:rgba(255,255,255,0.4); margin-bottom:4px;">{{ $stat['label'] }}</div>
        <div style="font-size:26px; font-weight:900; color:{{ $stat['color'] }};">{{ $stat['val'] }}</div>
    </div>
    @endforeach
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header">
        <span class="card-title" style="font-weight:500; font-size:22px">ALL DISCOUNT COUPONS</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>CODE</th>
                    <th>TYPE / VALUE</th>
                    <th>CATEGORIES</th>
                    <th>BADGE</th>
                    <th>MIN ORDER</th>
                    <th>USAGE</th>
                    <th>EXPIRES</th>
                    <th>STATUS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($discounts as $d)
                @php
                    $isExpired    = $d->expires_at && $d->expires_at->isPast();
                    $isExhausted  = $d->max_uses && $d->used_count >= $d->max_uses;
                    $status = !$d->is_active ? 'disabled'
                        : ($isExpired    ? 'expired'
                        : ($isExhausted  ? 'exhausted'
                        : 'active'));

                    $usagePct  = $d->max_uses ? min(100, round($d->used_count / $d->max_uses * 100)) : null;
                    $barColor  = $usagePct >= 100 ? '#a855f7' : ($usagePct >= 75 ? '#ef4444' : '#F97316');
                @endphp
                <tr>
                    {{-- CODE --}}
                    <td>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-family:monospace; font-weight:700; color:#F97316; font-size:15px; letter-spacing:1px;">
                                {{ $d->code }}
                            </span>
                            <button onclick="copyCode('{{ $d->code }}')" title="Copy code"
                                style="background:none; border:none; cursor:pointer; color:rgba(255,255,255,0.3); padding:2px;"
                                onmouseenter="this.style.color='#F97316'" onmouseleave="this.style.color='rgba(255,255,255,0.3)'">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                                </svg>
                            </button>
                        </div>
                    </td>

                    {{-- TYPE / VALUE --}}
                    <td>
                        <div style="display:flex; flex-direction:column; gap:3px;">
                            <span class="badge badge-gray" style="width:fit-content;">
                                {{ $d->type === 'percentage' ? 'PERCENT %' : 'FIXED $' }}
                            </span>
                            <span style="font-weight:900; color:#fff; font-size:17px; letter-spacing:0.5px;">
                                {{ $d->type === 'percentage' ? $d->value.'%' : '$'.number_format($d->value, 2) }}
                            </span>
                        </div>
                    </td>

                    {{-- CATEGORIES --}}
                    <td>
                        @if($d->categories && count($d->categories) > 0)
                            <div style="display:flex; flex-wrap:wrap; gap:4px; max-width:200px;">
                                @foreach($d->categories as $cat)
                                    <span class="badge badge-gray" style="font-size:11px;">{{ $cat }}</span>
                                @endforeach
                            </div>
                        @else
                            <span style="color:rgba(255,255,255,0.35); font-size:12px; font-style:italic;">All categories</span>
                        @endif
                    </td>

                    {{-- BADGE --}}
                    <td>
                        @php $bc = $d->badge_config; @endphp
                        @if($bc && !empty($bc['text']))
                            <div style="display:flex; flex-direction:column; gap:4px;">
                                <div style="display:inline-flex; align-items:center; gap:5px;
                                            padding:3px 10px; border-radius:20px; font-size:11px; font-weight:800; letter-spacing:.5px; width:fit-content;
                                            background:{{ $bc['bg'] ?? 'rgba(249,115,22,0.18)' }};
                                            border:1.5px solid {{ $bc['border'] ?? 'rgba(249,115,22,0.55)' }};
                                            color:{{ $bc['color'] ?? '#F97316' }};">
                                    {{ $bc['icon'] ?? '🏷️' }} {{ $bc['text'] }}
                                </div>
                                <button onclick="openBadgeModal({{ $d->id }}, {{ json_encode($bc) }})"
                                    style="font-size:10px; font-weight:700; letter-spacing:1px; padding:2px 8px;
                                           background:none; border:1px solid rgba(255,255,255,0.15);
                                           color:rgba(255,255,255,0.4); border-radius:6px; cursor:pointer;
                                           width:fit-content; font-family:Rajdhani,sans-serif;"
                                    onmouseover="this.style.borderColor='#F97316';this.style.color='#F97316'"
                                    onmouseout="this.style.borderColor='rgba(255,255,255,0.15)';this.style.color='rgba(255,255,255,0.4)'">
                                    EDIT
                                </button>
                            </div>
                        @else
                            <button onclick="openBadgeModal({{ $d->id }}, null)"
                                style="font-size:11px; font-weight:700; letter-spacing:1px; padding:3px 10px;
                                       background:rgba(167,139,250,0.08); border:1px solid rgba(167,139,250,0.25);
                                       color:rgba(167,139,250,0.6); border-radius:8px; cursor:pointer;
                                       font-family:Rajdhani,sans-serif;"
                                onmouseover="this.style.background='rgba(167,139,250,0.18)';this.style.color='#a78bfa'"
                                onmouseout="this.style.background='rgba(167,139,250,0.08)';this.style.color='rgba(167,139,250,0.6)'">
                                + ADD
                            </button>
                        @endif
                    </td>

                    {{-- MIN ORDER --}}
                    <td style="color:rgba(255,255,255,0.5);">
                        {{ $d->min_order > 0 ? '$'.number_format($d->min_order, 2) : '—' }}
                    </td>

                    {{-- USAGE with progress bar --}}
                    <td>
                        <div style="min-width:100px;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                <span style="color:rgba(255,255,255,0.8); font-weight:700; font-size:14px;">
                                    {{ $d->used_count }}
                                    @if($d->max_uses)
                                        <span style="color:rgba(255,255,255,0.3); font-weight:400;">/ {{ $d->max_uses }}</span>
                                    @else
                                        <span style="color:rgba(255,255,255,0.3); font-weight:400;">/ ∞</span>
                                    @endif
                                </span>
                                @if($usagePct !== null)
                                    <span style="font-size:11px; color:{{ $barColor }}; font-weight:700;">{{ $usagePct }}%</span>
                                @endif
                            </div>
                            @if($d->max_uses)
                            <div style="height:5px; background:rgba(255,255,255,0.08); border-radius:99px; overflow:hidden;">
                                <div style="height:100%; width:{{ $usagePct }}%; background:{{ $barColor }}; border-radius:99px; transition:width .4s ease;"></div>
                            </div>
                            @endif
                        </div>
                    </td>

                    {{-- EXPIRES --}}
                    <td>
                        @if($d->expires_at)
                            <div style="display:flex; flex-direction:column; gap:2px;">
                                <span style="color:{{ $isExpired ? '#ef4444' : 'rgba(255,255,255,0.4)' }}; font-size:13px; font-weight:{{ $isExpired ? '700' : '400' }};">
                                    {{ $d->expires_at->format('d M Y') }}
                                </span>
                                @if(!$isExpired)
                                    <span style="color:rgba(255,255,255,0.25); font-size:11px;">
                                        in {{ $d->expires_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span style="color:#ef4444; font-size:11px;">EXPIRED</span>
                                @endif
                            </div>
                        @else
                            <span style="color:rgba(255,255,255,0.2); font-size:13px;">—</span>
                        @endif
                    </td>

                    {{-- STATUS BADGE --}}
                    <td>
                        @if($status === 'active')
                            <span class="badge" style="background:rgba(34,197,94,0.15); color:#22c55e; border:1px solid rgba(34,197,94,0.35); font-weight:800; font-size:12px; padding:4px 10px; border-radius:20px;">
                                ● ACTIVE
                            </span>
                        @elseif($status === 'expired')
                            <span class="badge" style="background:rgba(239,68,68,0.15); color:#ef4444; border:1px solid rgba(239,68,68,0.35); font-weight:800; font-size:12px; padding:4px 10px; border-radius:20px;">
                                ✕ EXPIRED
                            </span>
                        @elseif($status === 'exhausted')
                            <span class="badge" style="background:rgba(168,85,247,0.15); color:#a855f7; border:1px solid rgba(168,85,247,0.35); font-weight:800; font-size:12px; padding:4px 10px; border-radius:20px;">
                                ■ EXHAUSTED
                            </span>
                        @else
                            <span class="badge" style="background:rgba(107,114,128,0.15); color:#9ca3af; border:1px solid rgba(107,114,128,0.35); font-weight:800; font-size:12px; padding:4px 10px; border-radius:20px;">
                                ○ DISABLED
                            </span>
                        @endif
                    </td>

                    {{-- ACTIONS --}}
                    <td>
                        <div style="display:flex; gap:8px;">
                            <button
                                onclick="openModal({{ $d->id }}, '{{ $d->code }}', '{{ $d->type }}', {{ $d->value }}, {{ $d->min_order }}, {{ $d->max_uses ?? 'null' }}, '{{ $d->expires_at ? $d->expires_at->format('Y-m-d') : '' }}', {{ $d->is_active ? 'true' : 'false' }}, {{ json_encode($d->categories ?? []) }})"
                                class="btn btn-outline btn-sm">
                                EDIT
                            </button>
                            <form method="POST" action="{{ route('dashboard.discounts.destroy', $d) }}"
                                  onsubmit="return confirm('Delete discount code {{ $d->code }}?')" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm"
                                    style="border:1px solid #ef4444; color:#ef4444; background:transparent;">
                                    DEL
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; color:rgba(255,255,255,0.3); padding:50px;">
                        No discount codes yet
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal --}}
<div id="discountModal" style="display:none; position:fixed; inset:0; z-index:1000;
     background:rgba(0,0,0,0.7); align-items:center; justify-content:center;">
    <div style="background:#1a1a1a; border:1px solid rgba(255,255,255,0.1); border-radius:16px;
                padding:28px; width:100%; max-width:500px; margin:0 16px; max-height:90vh; overflow-y:auto;">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <h2 id="modalTitle" style="font-size:20px; font-weight:900; color:#fff; letter-spacing:2px;">ADD DISCOUNT</h2>
            <button onclick="closeModal()" style="background:none; border:none; color:rgba(255,255,255,0.4); font-size:24px; cursor:pointer;">✕</button>
        </div>

        <form id="discountForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" id="discountId" name="id" value="">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div style="grid-column:1/-1;">
                    <label class="form-label">CODE *</label>
                    <input type="text" name="code" id="fCode" class="form-control" required
                           placeholder="e.g. SAVE20" style="text-transform:uppercase; letter-spacing:2px;"
                           oninput="this.value=this.value.toUpperCase()">
                </div>
                <div>
                    <label class="form-label">TYPE *</label>
                    <select name="type" id="fType" class="form-control" required>
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Amount ($)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">VALUE *</label>
                    <input type="number" name="value" id="fValue" class="form-control" required
                           min="0" step="0.01" placeholder="e.g. 20">
                </div>
                <div>
                    <label class="form-label">MIN ORDER ($)</label>
                    <input type="number" name="min_order" id="fMinOrder" class="form-control"
                           min="0" step="0.01" placeholder="0 = no minimum">
                </div>
                <div>
                    <label class="form-label">MAX USES</label>
                    <input type="number" name="max_uses" id="fMaxUses" class="form-control"
                           min="1" placeholder="Blank = unlimited">
                </div>
                <div style="grid-column:1/-1;">
                    <label class="form-label">EXPIRY DATE</label>
                    <input type="date" name="expires_at" id="fExpires" class="form-control">
                </div>
                <div style="grid-column:1/-1; position:relative;">
                    <label class="form-label">
                        APPLY TO CATEGORIES
                        <span style="color:rgba(255,255,255,0.3); font-size:11px; font-weight:400;">(empty = applies to all)</span>
                    </label>

                    @php
                    $catGroups = [
                        'PC BUILD'    => ['PC BUILD UNDER 1K','PC BUILD UNDER 2K','PC BUILD UNDER 3K','PC BUILD UNDER 4K','PC BUILD UNDER 5K','PC BUILD 5K UP'],
                        'MONITOR'     => ['MONITOR 25INCH','MONITOR 27INCH','MONITOR 32INCH','MONITOR 34INCH','MONITOR 39INCH','MONITOR 42INCH','MONITOR 48INCH','MONITOR 49INCH'],
                        'PC PART'     => ['CPU','RAM','MAINBOARD','COOLING','M2','VGA','CASE','POWER SUPPLY','FAN'],
                        'HOT ITEM'    => ['BEST PRICE','BEST SET'],
                        'ACCESSORY'   => ['KEYBOARD','MOUSE','HEADSET','EARPHONE','MONITOR STAND','SPEAKER','MICROPHONE','WEBCAM','MOUSEPAD','LIGHTBAR','ROUTER'],
                        'TABLE CHAIR' => ['DX RACER','SECRETLAB','RAZER','CONSAIR','FANTECH','COOLER MASTER','TTR RACING'],
                    ];
                    @endphp

                    <div id="catHiddenInputs"></div>

                    <div id="catTags" onclick="toggleCatDropdown()"
                         style="min-height:42px; background:#111; border:1px solid rgba(255,255,255,0.15);
                                border-radius:8px; padding:6px 36px 6px 10px; cursor:pointer;
                                display:flex; flex-wrap:wrap; gap:6px; align-items:center;
                                position:relative; transition:border-color .2s;">
                        <span id="catPlaceholder" style="color:rgba(255,255,255,0.3); font-size:13px; user-select:none;">
                            Select categories…
                        </span>
                        <svg style="position:absolute; right:10px; top:50%; transform:translateY(-50%); pointer-events:none;"
                             width="14" height="14" fill="none" stroke="rgba(255,255,255,0.4)" stroke-width="2" viewBox="0 0 24 24">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </div>

                    <div id="catDropdown"
                         style="display:none; position:absolute; z-index:200; background:#1e1e1e;
                                border:1px solid rgba(249,115,22,0.4); border-radius:10px;
                                padding:12px; width:460px; max-height:320px; overflow-y:auto;
                                box-shadow:0 8px 32px rgba(0,0,0,0.6); margin-top:4px;">
                        <input id="catSearch" type="text" placeholder="🔍 Search category…"
                               oninput="filterCats(this.value)"
                               style="width:100%; background:#111; border:1px solid rgba(255,255,255,0.1);
                                      color:#fff; border-radius:6px; padding:6px 10px; font-size:13px;
                                      margin-bottom:10px; outline:none; box-sizing:border-box;"
                               onclick="event.stopPropagation()">

                        @foreach($catGroups as $group => $subs)
                        <div class="cat-group" style="margin-bottom:10px;">
                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:5px;">
                                <input type="checkbox" class="group-checkbox" data-group="{{ $group }}"
                                       style="accent-color:#F97316; width:14px; height:14px; cursor:pointer;"
                                       onclick="toggleGroup('{{ $group }}', this.checked); event.stopPropagation()">
                                <span style="color:#F97316; font-weight:800; font-size:12px; letter-spacing:1.5px; user-select:none;">
                                    {{ $group }}
                                </span>
                            </div>
                            <div style="display:flex; flex-wrap:wrap; gap:6px; padding-left:22px;">
                                @foreach($subs as $cat)
                                <label class="cat-chip"
                                       data-cat="{{ $cat }}" data-group="{{ $group }}"
                                       style="display:inline-flex; align-items:center; gap:5px;
                                              background:#2a2a2a; border:1.5px solid transparent;
                                              border-radius:20px; padding:3px 10px; cursor:pointer;
                                              font-size:12px; color:rgba(255,255,255,0.7);
                                              transition:all .15s; user-select:none;"
                                       onclick="toggleCat('{{ $cat }}', '{{ $group }}'); event.stopPropagation()">
                                    <span class="chip-dot"
                                          style="width:7px; height:7px; border-radius:50%;
                                                 background:transparent; border:1.5px solid #666; flex-shrink:0;"></span>
                                    {{ $cat }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div style="grid-column:1/-1; display:flex; align-items:center; gap:10px;">
                    <input type="checkbox" name="is_active" id="fActive" value="1" checked
                           style="width:18px; height:18px; accent-color:#F97316;">
                    <label for="fActive" class="form-label" style="margin:0; cursor:pointer;">Active (can be used by customers)</label>
                </div>
            </div>

            <div style="margin-top:24px; display:flex; gap:12px;">
                <button type="button" onclick="closeModal()" class="btn btn-outline" style="flex:1;">CANCEL</button>
                <button type="submit" class="btn btn-orange" style="flex:1; font-size:16px;">SAVE DISCOUNT</button>
            </div>
        </form>
    </div>
</div>

{{-- Toast --}}
<div id="copyToast" style="display:none; position:fixed; bottom:24px; right:24px; z-index:2000;
     background:#22c55e; color:#fff; font-weight:700; font-size:14px; letter-spacing:1px;
     padding:10px 20px; border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,0.4);">
    ✓ Code copied!
</div>

<script>
// ── Copy code helper ──────────────────────────────────────────────────────────
function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        const t = document.getElementById('copyToast')
        t.style.display = 'block'
        setTimeout(() => t.style.display = 'none', 2000)
    })
}

// ── Category multi-select dropdown ────────────────────────────────────────────
let selectedCats = []

function toggleCatDropdown() {
    const dd = document.getElementById('catDropdown')
    const isOpen = dd.style.display !== 'none'
    dd.style.display = isOpen ? 'none' : 'block'
    if (!isOpen) document.getElementById('catSearch').focus()
}

function toggleCat(cat, group) {
    const idx = selectedCats.indexOf(cat)
    if (idx === -1) selectedCats.push(cat)
    else selectedCats.splice(idx, 1)
    renderCatState()
    updateGroupCheckbox(group)
}

function toggleGroup(group, checked) {
    const chips = document.querySelectorAll(`[data-group="${group}"]`)
    chips.forEach(chip => {
        const cat = chip.dataset.cat
        if (!cat) return
        if (checked && !selectedCats.includes(cat)) selectedCats.push(cat)
        else if (!checked) selectedCats = selectedCats.filter(c => c !== cat)
    })
    renderCatState()
}

function updateGroupCheckbox(group) {
    const chips = document.querySelectorAll(`label[data-group="${group}"]`)
    const total = chips.length
    const checked = [...chips].filter(c => selectedCats.includes(c.dataset.cat)).length
    const cb = document.querySelector(`.group-checkbox[data-group="${group}"]`)
    if (cb) {
        cb.checked = checked === total && total > 0
        cb.indeterminate = checked > 0 && checked < total
    }
}

function renderCatState() {
    document.querySelectorAll('label[data-cat]').forEach(chip => {
        const cat = chip.dataset.cat
        const active = selectedCats.includes(cat)
        chip.style.background = active ? 'rgba(249,115,22,0.2)' : '#2a2a2a'
        chip.style.borderColor = active ? '#F97316' : 'transparent'
        chip.style.color = active ? '#fff' : 'rgba(255,255,255,0.7)'
        chip.querySelector('.chip-dot').style.background = active ? '#F97316' : 'transparent'
        chip.querySelector('.chip-dot').style.borderColor = active ? '#F97316' : '#666'
    })

    const tagsEl = document.getElementById('catTags')
    const placeholder = document.getElementById('catPlaceholder')
    tagsEl.querySelectorAll('.selected-tag').forEach(t => t.remove())

    if (selectedCats.length === 0) {
        placeholder.style.display = ''
    } else {
        placeholder.style.display = 'none'
        selectedCats.forEach(cat => {
            const tag = document.createElement('span')
            tag.className = 'selected-tag'
            tag.style.cssText = 'background:#F97316; color:#fff; font-size:11px; font-weight:700; border-radius:20px; padding:2px 10px; display:inline-flex; align-items:center; gap:5px; letter-spacing:.5px;'
            tag.innerHTML = `${cat} <span onclick="removeCat('${cat}'); event.stopPropagation()" style="cursor:pointer; opacity:.8; font-size:13px; line-height:1;">&times;</span>`
            tagsEl.insertBefore(tag, tagsEl.lastElementChild)
        })
    }

    const hiddenDiv = document.getElementById('catHiddenInputs')
    hiddenDiv.innerHTML = selectedCats.map(c =>
        `<input type="hidden" name="categories[]" value="${c}">`
    ).join('')
}

function removeCat(cat) {
    selectedCats = selectedCats.filter(c => c !== cat)
    const group = document.querySelector(`label[data-cat="${cat}"]`)?.dataset.group
    renderCatState()
    if (group) updateGroupCheckbox(group)
}

function filterCats(query) {
    const q = query.toLowerCase()
    document.querySelectorAll('.cat-group').forEach(group => {
        let anyVisible = false
        group.querySelectorAll('label[data-cat]').forEach(chip => {
            const match = chip.dataset.cat.toLowerCase().includes(q)
            chip.style.display = match ? '' : 'none'
            if (match) anyVisible = true
        })
        group.style.display = anyVisible ? '' : 'none'
    })
}

document.addEventListener('click', function(e) {
    const dd = document.getElementById('catDropdown')
    const tags = document.getElementById('catTags')
    if (dd && !dd.contains(e.target) && !tags.contains(e.target)) {
        dd.style.display = 'none'
    }
})

// ── Modal open/close ──────────────────────────────────────────────────────────
function openModal(id, code, type, value, minOrder, maxUses, expires, isActive, categories) {
    const modal = document.getElementById('discountModal')
    modal.style.display = 'flex'

    selectedCats = categories && categories.length ? [...categories] : []
    renderCatState()
    document.querySelectorAll('.group-checkbox').forEach(cb => updateGroupCheckbox(cb.dataset.group))
    document.getElementById('catSearch').value = ''
    filterCats('')
    document.getElementById('catDropdown').style.display = 'none'

    if (id) {
        document.getElementById('modalTitle').textContent = 'EDIT DISCOUNT'
        document.getElementById('discountForm').action = `/dashboard/discounts/${id}`
        document.getElementById('formMethod').value = 'PUT'
        document.getElementById('discountId').value = id
        document.getElementById('fCode').value = code
        document.getElementById('fType').value = type
        document.getElementById('fValue').value = value
        document.getElementById('fMinOrder').value = minOrder || ''
        document.getElementById('fMaxUses').value = maxUses || ''
        document.getElementById('fExpires').value = expires || ''
        document.getElementById('fActive').checked = isActive
    } else {
        document.getElementById('modalTitle').textContent = 'ADD DISCOUNT'
        document.getElementById('discountForm').action = '{{ route("dashboard.discounts.store") }}'
        document.getElementById('formMethod').value = 'POST'
        document.getElementById('discountId').value = ''
        document.getElementById('discountForm').reset()
        document.getElementById('fActive').checked = true
        selectedCats = []
        renderCatState()
    }
}

function closeModal() {
    document.getElementById('discountModal').style.display = 'none'
    document.getElementById('catDropdown').style.display = 'none'
}

document.getElementById('discountModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal()
})
</script>


{{-- ══════════════════════ BADGE MODAL ══════════════════════════════════════ --}}
<div id="badgeModal" style="display:none; position:fixed; inset:0; z-index:2000;
     background:rgba(0,0,0,0.78); align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div style="background:#141414; border:1px solid rgba(249,115,22,0.2); border-radius:20px;
                padding:28px; width:100%; max-width:480px; margin:0 16px; max-height:92vh; overflow-y:auto;
                box-shadow:0 32px 80px rgba(0,0,0,0.7);">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:22px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:36px; height:36px; border-radius:10px; background:rgba(249,115,22,0.12);
                            border:1px solid rgba(249,115,22,0.3); display:flex; align-items:center;
                            justify-content:center; font-size:17px;">🏷️</div>
                <div>
                    <div style="font-size:17px; font-weight:900; color:#fff; letter-spacing:2px;" id="badgeModalTitle">DISCOUNT BADGE</div>
                    <div style="font-size:11px; color:rgba(255,255,255,0.3); margin-top:1px;" id="badgeModalSubtitle">Badge auto-applies on product cards</div>
                </div>
            </div>
            <button onclick="closeBadgeModal()"
                style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1);
                       color:rgba(255,255,255,0.5); width:32px; height:32px; border-radius:8px;
                       cursor:pointer; font-size:16px; display:flex; align-items:center; justify-content:center;">✕</button>
        </div>

        {{-- Live preview --}}
        <div style="background:#0d0d0d; border:1px solid rgba(255,255,255,0.07); border-radius:12px;
                    padding:16px; margin-bottom:20px; text-align:center;">
            <div style="font-size:10px; letter-spacing:2px; color:rgba(255,255,255,0.3); margin-bottom:12px; font-weight:700;">LIVE PREVIEW</div>
            <div id="bPreview" style="
                display:inline-flex; align-items:center; gap:5px; padding:5px 14px;
                border-radius:20px; font-size:13px; font-weight:800; letter-spacing:1px; transition:all .25s;">
                <span id="bPrevIcon">🏷️</span>
                <span id="bPrevText">SALE</span>
            </div>
        </div>

        {{-- Badge text --}}
        <div style="margin-bottom:14px;">
            <label style="font-size:11px; font-weight:700; letter-spacing:2px; color:rgba(255,255,255,0.4); display:block; margin-bottom:6px;">BADGE TEXT *</label>
            <input type="text" id="bText" placeholder="e.g. SALE, HOT, 20% OFF, NEW" maxlength="18"
                   style="width:100%; background:#111; border:1px solid rgba(255,255,255,0.12); color:#fff;
                          border-radius:8px; padding:10px 14px; font-size:14px; font-weight:800; letter-spacing:1.5px;
                          box-sizing:border-box; outline:none; font-family:Rajdhani,sans-serif;"
                   oninput="this.value=this.value.toUpperCase(); syncPreview()"
                   onfocus="this.style.borderColor='#F97316'" onblur="this.style.borderColor='rgba(255,255,255,0.12)'">
        </div>

        {{-- Icon picker --}}
        <div style="margin-bottom:14px;">
            <label style="font-size:11px; font-weight:700; letter-spacing:2px; color:rgba(255,255,255,0.4); display:block; margin-bottom:8px;">ICON</label>
            <div style="display:flex; flex-wrap:wrap; gap:6px;">
                @foreach(['🏷️','🔥','⚡','💥','🎯','✨','🎁','💎','🚀','⭐','🆕','💰'] as $ico)
                <button type="button" onclick="selectBIcon('{{ $ico }}')" data-bico="{{ $ico }}"
                    style="width:36px; height:36px; border-radius:8px; font-size:19px; cursor:pointer;
                           background:rgba(255,255,255,0.05); border:1.5px solid rgba(255,255,255,0.1);
                           transition:all .15s; display:flex; align-items:center; justify-content:center;">
                    {{ $ico }}
                </button>
                @endforeach
            </div>
        </div>

        {{-- Color presets --}}
        <div style="margin-bottom:14px;">
            <label style="font-size:11px; font-weight:700; letter-spacing:2px; color:rgba(255,255,255,0.4); display:block; margin-bottom:8px;">COLOR</label>
            <div style="display:flex; flex-wrap:wrap; gap:6px;">
                @php $bcp = [
                    ['l'=>'Orange','bg'=>'rgba(249,115,22,0.18)','bd'=>'rgba(249,115,22,0.55)','c'=>'#F97316'],
                    ['l'=>'Red',   'bg'=>'rgba(239,68,68,0.18)',  'bd'=>'rgba(239,68,68,0.55)',  'c'=>'#ef4444'],
                    ['l'=>'Green', 'bg'=>'rgba(34,197,94,0.18)',  'bd'=>'rgba(34,197,94,0.55)',  'c'=>'#22c55e'],
                    ['l'=>'Blue',  'bg'=>'rgba(59,130,246,0.18)', 'bd'=>'rgba(59,130,246,0.55)', 'c'=>'#3b82f6'],
                    ['l'=>'Purple','bg'=>'rgba(167,139,250,0.18)','bd'=>'rgba(167,139,250,0.55)','c'=>'#a78bfa'],
                    ['l'=>'Yellow','bg'=>'rgba(234,179,8,0.18)',  'bd'=>'rgba(234,179,8,0.55)',  'c'=>'#eab308'],
                    ['l'=>'Solid', 'bg'=>'#F97316',               'bd'=>'#F97316',               'c'=>'#fff'],
                ]; @endphp
                @foreach($bcp as $i => $p)
                <button type="button" data-bbg="{{ $p['bg'] }}" data-bbd="{{ $p['bd'] }}" data-bc="{{ $p['c'] }}"
                    onclick="selectBColor(this)"
                    style="padding:5px 12px; border-radius:20px; cursor:pointer; font-size:11px; font-weight:800;
                           letter-spacing:1px; font-family:Rajdhani,sans-serif; transition:all .15s;
                           background:{{ $p['bg'] }}; border:1.5px solid {{ $p['bd'] }}; color:{{ $p['c'] }};
                           {{ $i===0 ? 'outline:2px solid rgba(255,255,255,0.4);outline-offset:2px;' : '' }}"
                    onmouseover="this.style.filter='brightness(1.2)'" onmouseout="this.style.filter=''">
                    {{ $p['l'] }}
                </button>
                @endforeach
            </div>
        </div>

        {{-- Actions --}}
        <div style="display:flex; gap:10px; margin-top:4px;">
            <button type="button" onclick="closeBadgeModal()"
                style="flex:1; padding:10px; border-radius:10px; cursor:pointer; font-size:13px; font-weight:700;
                       background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1);
                       color:rgba(255,255,255,0.5); font-family:Rajdhani,sans-serif;">CANCEL</button>
            <button type="button" id="badgeClearBtn" onclick="saveBadge(true)"
                style="flex:1; padding:10px; border-radius:10px; cursor:pointer; font-size:13px; font-weight:700;
                       background:rgba(239,68,68,0.1); border:1.5px solid rgba(239,68,68,0.3);
                       color:#ef4444; font-family:Rajdhani,sans-serif; display:none;"
                onmouseover="this.style.background='rgba(239,68,68,0.2)'" onmouseout="this.style.background='rgba(239,68,68,0.1)'">
                🗑 CLEAR BADGE</button>
            <button type="button" onclick="saveBadge(false)"
                style="flex:2; padding:10px; border-radius:10px; cursor:pointer; font-size:14px; font-weight:800;
                       background:rgba(167,139,250,0.15); border:1.5px solid rgba(167,139,250,0.4); color:#a78bfa;
                       font-family:Rajdhani,sans-serif; letter-spacing:1px;"
                onmouseover="this.style.background='rgba(167,139,250,0.25)'" onmouseout="this.style.background='rgba(167,139,250,0.15)'">
                💾 SAVE BADGE</button>
        </div>
    </div>
</div>

{{-- Badge save toast --}}
<div id="badgeCopiedToast" style="display:none; position:fixed; bottom:24px; right:24px; z-index:9999;
     background:linear-gradient(135deg,#a78bfa,#8b5cf6); color:#fff; font-weight:700; font-size:14px;
     letter-spacing:1px; padding:12px 22px; border-radius:12px; box-shadow:0 4px 24px rgba(139,92,246,0.5);">
    ✓ Badge saved!
</div>

<script>
let _bIco = '🏷️', _bBg = 'rgba(249,115,22,0.18)', _bBd = 'rgba(249,115,22,0.55)', _bC = '#F97316'
let _bDiscountId = null  // which discount this badge belongs to

function openBadgeModal(discountId, existingBadge) {
    _bDiscountId = discountId
    document.getElementById('badgeModal').style.display = 'flex'

    // Populate from existing badge_config if present
    if (existingBadge && existingBadge.text) {
        document.getElementById('bText').value = existingBadge.text || ''
        _bIco = existingBadge.icon  || '🏷️'
        _bBg  = existingBadge.bg    || 'rgba(249,115,22,0.18)'
        _bBd  = existingBadge.border|| 'rgba(249,115,22,0.55)'
        _bC   = existingBadge.color || '#F97316'
        document.getElementById('badgeModalTitle').textContent    = 'EDIT BADGE'
        document.getElementById('badgeModalSubtitle').textContent = `Discount #${discountId}`
        document.getElementById('badgeClearBtn').style.display    = 'block'
    } else {
        document.getElementById('bText').value = ''
        _bIco = '🏷️'
        _bBg  = 'rgba(249,115,22,0.18)'
        _bBd  = 'rgba(249,115,22,0.55)'
        _bC   = '#F97316'
        document.getElementById('badgeModalTitle').textContent    = 'ADD BADGE'
        document.getElementById('badgeModalSubtitle').textContent = `Discount #${discountId}`
        document.getElementById('badgeClearBtn').style.display    = 'none'
    }

    // Sync icon button highlights
    document.querySelectorAll('[data-bico]').forEach(b => {
        const on = b.dataset.bico === _bIco
        b.style.background  = on ? 'rgba(249,115,22,0.2)' : 'rgba(255,255,255,0.05)'
        b.style.borderColor = on ? '#F97316' : 'rgba(255,255,255,0.1)'
        b.style.transform   = on ? 'scale(1.18)' : 'scale(1)'
    })

    // Sync color button highlights
    document.querySelectorAll('[data-bbg]').forEach(b => {
        b.style.outline      = b.dataset.bbg === _bBg ? '2px solid rgba(255,255,255,0.45)' : 'none'
        b.style.outlineOffset = '2px'
    })

    syncPreview()
}

function closeBadgeModal() { document.getElementById('badgeModal').style.display = 'none' }
document.getElementById('badgeModal').addEventListener('click', function(e) { if(e.target===this) closeBadgeModal() })

function selectBIcon(ico) {
    _bIco = ico
    document.querySelectorAll('[data-bico]').forEach(b => {
        const on = b.dataset.bico === ico
        b.style.background  = on ? 'rgba(249,115,22,0.2)' : 'rgba(255,255,255,0.05)'
        b.style.borderColor = on ? '#F97316' : 'rgba(255,255,255,0.1)'
        b.style.transform   = on ? 'scale(1.18)' : 'scale(1)'
    })
    syncPreview()
}

function selectBColor(btn) {
    _bBg = btn.dataset.bbg; _bBd = btn.dataset.bbd; _bC = btn.dataset.bc
    document.querySelectorAll('[data-bbg]').forEach(b => {
        b.style.outline = b === btn ? '2px solid rgba(255,255,255,0.45)' : 'none'
        b.style.outlineOffset = '2px'
    })
    syncPreview()
}

function syncPreview() {
    const text = document.getElementById('bText').value.trim() || 'SALE'
    const prev = document.getElementById('bPreview')
    document.getElementById('bPrevIcon').textContent = _bIco
    document.getElementById('bPrevText').textContent = text
    prev.style.background = _bBg
    prev.style.border     = `1.5px solid ${_bBd}`
    prev.style.color      = _bC
}

// ── Save badge via web route (session-authenticated, no 401) ─────────────────
// Using FormData POST with _method=PATCH so it goes through the web middleware
// stack (session + CSRF) instead of the Sanctum API guard that was causing 401.
async function saveBadge(clear) {
    if (!_bDiscountId) return

    const text = document.getElementById('bText').value.trim()
    if (!clear && !text) {
        document.getElementById('bText').style.borderColor = '#ef4444'
        document.getElementById('bText').focus()
        setTimeout(() => document.getElementById('bText').style.borderColor = 'rgba(255,255,255,0.12)', 2000)
        return
    }

    // Grab CSRF token from meta tag (set in layout) or from any existing form on the page
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_token"]')?.value
        || ''

    const fd = new FormData()
    fd.append('_token',  csrf)
    fd.append('_method', 'PATCH')

    if (clear) {
        fd.append('clear_badge', '1')
    } else {
        fd.append('badge_config[text]',   text)
        fd.append('badge_config[icon]',   _bIco)
        fd.append('badge_config[bg]',     _bBg)
        fd.append('badge_config[border]', _bBd)
        fd.append('badge_config[color]',  _bC)
    }

    try {
        const res = await fetch(`/dashboard/discounts/${_bDiscountId}/badge`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: fd,
        })

        if (!res.ok) {
            const body = await res.text()
            throw new Error(`HTTP ${res.status}: ${body.slice(0, 300)}`)
        }

        const toast = document.getElementById('badgeCopiedToast')
        toast.textContent = clear ? '🗑 Badge cleared!' : '✓ Badge saved!'
        toast.style.background = clear
            ? 'linear-gradient(135deg,#ef4444,#dc2626)'
            : 'linear-gradient(135deg,#a78bfa,#8b5cf6)'
        toast.style.display = 'block'
        setTimeout(() => toast.style.display = 'none', 2200)

        closeBadgeModal()
        setTimeout(() => window.location.reload(), 600)

    } catch (err) {
        alert('Failed to save badge: ' + err.message)
        console.error('Badge save error:', err)
    }
}
</script>

@endif
@endsection
