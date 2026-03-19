@extends('dashboard.layout')
@section('title', 'BANNERS')

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
{{-- ══════════════════ ACCESS DENIED ══════════════════════════════════════ --}}
<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;
     min-height:60vh;text-align:center;padding:40px 20px;font-family:Rajdhani,sans-serif;
     animation:fadeUp .45s ease both;">
    <div style="width:96px;height:96px;border-radius:28px;margin-bottom:28px;
         background:rgba(239,68,68,0.08);border:1.5px solid rgba(239,68,68,0.25);
         display:flex;align-items:center;justify-content:center;font-size:46px;
         box-shadow:0 0 60px rgba(239,68,68,0.12);animation:lockPulse 2.5s ease-in-out infinite;">🔒</div>
    <div style="font-size:30px;font-weight:900;letter-spacing:3px;color:#ef4444;margin-bottom:8px;">ACCESS DENIED</div>
    <div style="font-size:14px;color:rgba(255,255,255,0.35);margin-bottom:32px;max-width:380px;line-height:1.6;">
        Your role does not have permission to access this module.<br>
        Contact a <span style="color:#F97316;font-weight:700;">Super Admin</span> to request access.
    </div>
    <div style="display:inline-flex;align-items:center;gap:10px;padding:12px 24px;border-radius:16px;
         margin-bottom:32px;background:{{ $_pRM['color'] }}12;border:1.5px solid {{ $_pRM['color'] }}40;">
        <span style="font-size:22px;">{{ $_pRM['icon'] }}</span>
        <div style="text-align:left;">
            <div style="font-size:10px;color:rgba(255,255,255,0.4);letter-spacing:2px;font-weight:700;">YOUR ROLE</div>
            <div style="font-size:16px;font-weight:800;color:{{ $_pRM['color'] }};letter-spacing:1px;">{{ strtoupper($_pRM['label']) }}</div>
        </div>
        <div style="width:1px;height:32px;background:rgba(255,255,255,0.1);margin:0 4px;"></div>
        <div style="text-align:left;">
            <div style="font-size:10px;color:rgba(255,255,255,0.4);letter-spacing:2px;font-weight:700;">MODULE</div>
            <div style="font-size:16px;font-weight:800;color:rgba(255,255,255,0.6);letter-spacing:1px;">{{ strtoupper(str_replace('_',' ','products')) }}</div>
        </div>
    </div>
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);
         border-radius:16px;padding:20px 24px;margin-bottom:32px;max-width:480px;width:100%;">
        <div style="font-size:11px;color:rgba(255,255,255,0.3);letter-spacing:2px;font-weight:700;margin-bottom:16px;text-align:left;">YOUR ACCESS OVERVIEW</div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;">
            @foreach($_pAllFeats as $_fKey => $_fIcon)
            @php
                $_fPKey = "perm_{$_pRole}_{$_fKey}";
                $_fHas  = $_pRole === 'superadmin' || (AdminSetting::get($_fPKey, $_pDef["{$_pRole}_{$_fKey}"] ?? '0') === '1');
                $_fActive = ($_fKey === 'products');
            @endphp
            <div style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:10px 6px;border-radius:10px;
                 background:{{ $_fActive ? 'rgba(239,68,68,0.10)' : ($_fHas ? 'rgba(34,197,94,0.07)' : 'rgba(255,255,255,0.03)') }};
                 border:1px solid {{ $_fActive ? 'rgba(239,68,68,0.3)' : ($_fHas ? 'rgba(34,197,94,0.2)' : 'rgba(255,255,255,0.06)') }};">
                <span style="font-size:18px;{{ !$_fHas ? 'opacity:0.3;' : '' }}">{{ $_fIcon }}</span>
                <span style="font-size:9px;letter-spacing:1px;font-weight:700;
                    color:{{ $_fActive ? '#ef4444' : ($_fHas ? '#22c55e' : 'rgba(255,255,255,0.2)') }};">
                    {{ $_fHas ? '✓' : '✗' }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center;">
        <a href="{{ route('dashboard.index') }}" style="display:inline-flex;align-items:center;gap:8px;
           padding:12px 24px;border-radius:12px;text-decoration:none;background:#F97316;color:#fff;
           font-size:14px;font-weight:700;letter-spacing:1px;box-shadow:0 4px 16px rgba(249,115,22,0.3);"
           onmouseover="this.style.background='#fb923c'" onmouseout="this.style.background='#F97316'">
            🏠 GO TO DASHBOARD
        </a>
        <a href="javascript:history.back()" style="display:inline-flex;align-items:center;gap:8px;
           padding:12px 24px;border-radius:12px;text-decoration:none;
           background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);
           color:rgba(255,255,255,0.6);font-size:14px;font-weight:700;letter-spacing:1px;"
           onmouseover="this.style.background='rgba(255,255,255,0.10)'" onmouseout="this.style.background='rgba(255,255,255,0.06)'">
            ← GO BACK
        </a>
    </div>
</div>
<style>
@keyframes fadeUp   { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:none} }
@keyframes lockPulse { 0%,100%{box-shadow:0 0 30px rgba(239,68,68,0.08)} 50%{box-shadow:0 0 60px rgba(239,68,68,0.22)} }
</style>
@else



{{-- Header --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
        <p style="color:rgba(255,255,255,0.8); font-size:20px;">{{ $banners->count() }} banners total</p>
    </div>
    <button onclick="openModal()" class="btn btn-orange" style="font-size:18px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        ADD BANNER
    </button>
</div>

@if(session('success'))
<div style="background:rgba(34,197,94,0.12); border:1px solid rgba(34,197,94,0.35); color:#22c55e;
     border-radius:10px; padding:12px 16px; margin-bottom:16px; font-weight:700; font-size:14px;">
    ✓ {{ session('success') }}
</div>
@endif

{{-- Banner cards grid --}}
<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:16px;">
    @forelse($banners as $b)
    <div style="background:#1a1a1a; border:1px solid rgba(255,255,255,0.07); border-radius:14px; overflow:hidden;">

        {{-- Preview area --}}
        <div style="position:relative; height:150px; background:{{ $b->bg_color ?? '#111' }};
                    display:flex; align-items:center; justify-content:center; overflow:hidden;">

            {{-- Video preview --}}
            @if($b->video && $b->video_type === 'upload')
                <video src="{{ $b->video }}" muted loop autoplay playsinline
                    style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:.5;"></video>
            @elseif($b->video && in_array($b->video_type, ['youtube','vimeo','facebook']))
                {{-- Static thumbnail placeholder — iframes can't autoplay in card previews --}}
                <div style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center;
                            justify-content:center; background:rgba(0,0,0,0.55); gap:6px;">
                    @if($b->video_type === 'facebook')
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="#1877F2"><path d="M24 12.073C24 5.404 18.627 0 12 0S0 5.404 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.413c0-3.026 1.792-4.697 4.533-4.697 1.313 0 2.686.235 2.686.235v2.97h-1.513c-1.491 0-1.956.93-1.956 1.886v2.267h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>
                        <span style="color:#1877F2; font-size:11px; font-weight:800; letter-spacing:1px;">FACEBOOK VIDEO</span>
                    @else
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="#F97316"><path d="M8 5v14l11-7z"/></svg>
                        <span style="color:#F97316; font-size:11px; font-weight:800; letter-spacing:1px;">{{ strtoupper($b->video_type) }} VIDEO</span>
                    @endif
                </div>
            @endif

            {{-- Image overlay --}}
            @if($b->image)
                <img src="{{ $b->image }}" alt="{{ $b->title }}"
                    style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:.55;">
                {{-- GIF badge --}}
                @if(str_ends_with(strtolower($b->image), '.gif'))
                <div style="position:absolute; top:8px; left:{{ $b->has_video ? '70px' : '8px' }}; z-index:3;
                     background:rgba(168,85,247,0.9); color:#fff; border-radius:4px;
                     padding:2px 7px; font-size:10px; font-weight:800; letter-spacing:1px;">
                    GIF
                </div>
                @endif
            @endif

            {{-- Text overlay --}}
            <div style="position:relative; z-index:2; text-align:center; padding:12px;">
                @if($b->badge)
                    <div style="display:inline-block; background:#F97316; color:#fff; font-size:10px; font-weight:700;
                                letter-spacing:1px; border-radius:20px; padding:2px 10px; margin-bottom:6px;">
                        {{ $b->badge }}
                    </div>
                @endif
                <div style="font-size:16px; font-weight:900; color:{{ $b->text_color ?? '#fff' }}; line-height:1.2;">
                    {{ $b->title }}
                </div>
                @if($b->subtitle)
                    <div style="font-size:12px; color:rgba(255,255,255,0.7); margin-top:3px;">{{ $b->subtitle }}</div>
                @endif
            </div>

            {{-- Active toggle --}}
            <div style="position:absolute; top:8px; right:8px; z-index:3;">
                <form method="POST" action="{{ route('dashboard.banners.toggle', $b) }}" style="display:inline;">
                    @csrf @method('PATCH')
                    <button type="submit"
                        style="background:{{ $b->active ? 'rgba(34,197,94,0.85)' : 'rgba(107,114,128,0.85)' }};
                               color:#fff; border:none; border-radius:20px; padding:3px 10px;
                               font-size:11px; font-weight:700; cursor:pointer; letter-spacing:.5px;">
                        {{ $b->active ? '● ON' : '○ OFF' }}
                    </button>
                </form>
            </div>

            {{-- Media type badge --}}
            @if($b->has_video)
                <div style="position:absolute; top:8px; left:8px; z-index:3;
                     background:rgba(249,115,22,0.85); color:#fff; border-radius:20px;
                     padding:3px 8px; font-size:10px; font-weight:800; letter-spacing:1px;">
                    🎬 VIDEO
                </div>
            @endif

            {{-- Order badge --}}
            <div style="position:absolute; bottom:8px; left:8px; z-index:3;
                 background:rgba(0,0,0,0.6); color:rgba(255,255,255,0.6);
                 border-radius:6px; padding:2px 7px; font-size:11px; font-weight:700;">
                #{{ $b->order }}
            </div>
        </div>

        {{-- Card footer --}}
        <div style="padding:12px 14px; display:flex; align-items:center; justify-content:space-between; gap:8px;">
            <div style="min-width:0;">
                <div style="font-size:14px; font-weight:700; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    {{ $b->title }}
                </div>
                <div style="font-size:11px; color:rgba(255,255,255,0.3); margin-top:2px;">
                    @php $isGif = $b->image && str_ends_with(strtolower($b->image), '.gif'); @endphp
                    @if($b->image && $b->has_video) {{ $isGif ? 'GIF + Video' : 'Image + Video' }}
                    @elseif($b->has_video) Video only
                    @elseif($isGif) GIF animation
                    @elseif($b->image) Image only
                    @else Text only
                    @endif
                </div>
            </div>
            <div style="display:flex; gap:8px; flex-shrink:0;">
                <button onclick="openModal(
                    {{ $b->id }},
                    @js($b->title),
                    @js($b->subtitle ?? ''),
                    @js($b->badge ?? ''),
                    @js($b->bg_color ?? '#111111'),
                    @js($b->text_color ?? '#F97316'),
                    @js($b->image ?? ''),
                    {{ $b->order }},
                    {{ $b->active ? 'true' : 'false' }},
                    @js($b->video ?? ''),
                    @js($b->video_type ?? '')
                )" class="btn btn-outline btn-sm">EDIT</button>
                <form method="POST" action="{{ route('dashboard.banners.destroy', $b) }}"
                      onsubmit="return confirm('Delete this banner?')" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm"
                        style="border:1px solid #ef4444; color:#ef4444; background:transparent;">DEL</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div style="grid-column:1/-1; text-align:center; color:rgba(255,255,255,0.3); padding:60px 0; font-size:18px;">
        No banners yet — click ADD BANNER to get started.
    </div>
    @endforelse
</div>

{{-- ════════════════════════════════════════════════════════════════════════════
     MODAL
═══════════════════════════════════════════════════════════════════════════════ --}}
<div id="bannerModal" style="display:none; position:fixed; inset:0; z-index:1000;
     background:rgba(0,0,0,0.75); align-items:center; justify-content:center; padding:12px;">
    <div class="banner-modal-box" style="background:#1a1a1a; border:1px solid rgba(255,255,255,0.1); border-radius:16px;
                padding:24px; width:100%; max-width:540px; max-height:94vh; overflow-y:auto;">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 id="modalTitle" style="font-size:18px; font-weight:900; color:#fff; letter-spacing:2px;">ADD BANNER</h2>
            <button onclick="closeModal()" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); color:rgba(255,255,255,0.5); font-size:20px; cursor:pointer; border-radius:8px; width:34px; height:34px; display:flex; align-items:center; justify-content:center; line-height:1;">✕</button>
        </div>

        <form id="bannerForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            {{-- Validation errors --}}
            @if($errors->any())
            <div style="background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.4);
                        border-radius:10px; padding:12px 16px; margin-bottom:16px;">
                <div style="color:#ef4444; font-weight:800; font-size:13px; margin-bottom:6px;">
                    ✕ Please fix the following errors:
                </div>
                @foreach($errors->all() as $e)
                    <div style="color:#fca5a5; font-size:12px; margin-bottom:2px;">• {{ $e }}</div>
                @endforeach
            </div>
            @endif

            {{-- PHP upload limit warning (shows when file > PHP limit is detected) --}}
            @if(request()->isMethod('post') && empty($_FILES) && !empty($_SERVER['CONTENT_LENGTH']))
            <div style="background:rgba(249,115,22,0.12); border:1px solid rgba(249,115,22,0.4);
                        border-radius:10px; padding:12px 16px; margin-bottom:16px;">
                <div style="color:#F97316; font-weight:800; font-size:13px;">⚠ File too large for PHP</div>
                <div style="color:rgba(249,115,22,0.8); font-size:12px; margin-top:4px;">
                    The file exceeds PHP's <code>upload_max_filesize</code> limit.
                    Add the lines from <strong>htaccess_upload_fix.txt</strong> to your <code>public/.htaccess</code> to raise the limit.
                </div>
            </div>
            @endif

            <div class="banner-form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">

                {{-- Title --}}
                <div style="grid-column:1/-1;">
                    <label class="form-label">TITLE *</label>
                    <input type="text" name="title" id="fTitle" class="form-control" required placeholder="e.g. Tronmatix Build PC">
                </div>

                {{-- Subtitle --}}
                <div style="grid-column:1/-1;">
                    <label class="form-label">SUBTITLE</label>
                    <input type="text" name="subtitle" id="fSubtitle" class="form-control" placeholder="e.g. RTX 5090 NEW Stock">
                </div>

                {{-- Badge --}}
                <div>
                    <label class="form-label">BADGE LABEL</label>
                    <input type="text" name="badge" id="fBadge" class="form-control" placeholder="e.g. New Arrival">
                </div>

                {{-- Order --}}
                <div>
                    <label class="form-label">DISPLAY ORDER</label>
                    <input type="number" name="order" id="fOrder" class="form-control" min="0" value="0">
                </div>

                {{-- Colors --}}
                <div>
                    <label class="form-label">BACKGROUND COLOR</label>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <input type="color" name="bg_color" id="fBgColor" value="#111111"
                               style="width:42px; height:42px; border:none; border-radius:8px; cursor:pointer; background:none; padding:2px;">
                        <input type="text" id="fBgColorText"
                               style="flex:1; background:#111; border:1px solid rgba(255,255,255,0.15); color:#fff; border-radius:8px; padding:10px; font-size:14px;"
                               placeholder="#111111" oninput="document.getElementById('fBgColor').value=this.value">
                    </div>
                </div>
                <div>
                    <label class="form-label">TEXT COLOR</label>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <input type="color" name="text_color" id="fTextColor" value="#F97316"
                               style="width:42px; height:42px; border:none; border-radius:8px; cursor:pointer; background:none; padding:2px;">
                        <input type="text" id="fTextColorText"
                               style="flex:1; background:#111; border:1px solid rgba(255,255,255,0.15); color:#fff; border-radius:8px; padding:10px; font-size:14px;"
                               placeholder="#F97316" oninput="document.getElementById('fTextColor').value=this.value">
                    </div>
                </div>

                {{-- ─── IMAGE / GIF ──────────────────────────────────────── --}}
                <div style="grid-column:1/-1;">
                    <label class="form-label">
                        BANNER IMAGE / GIF
                        <span style="color:rgba(255,255,255,0.3); font-size:11px; font-weight:400;">(GIFs animate automatically)</span>
                    </label>
                    <div style="background:#111; border:1px dashed rgba(255,255,255,0.15); border-radius:10px; padding:16px;">
                        {{-- Current image/GIF preview --}}
                        <div id="currentImageWrap" style="display:none; margin-bottom:10px;">
                            <div style="position:relative; display:inline-block;">
                                <img id="currentImagePreview" src="" alt="Current"
                                     style="max-height:100px; border-radius:6px; object-fit:contain; display:block;">
                                <span id="currentGifBadge" style="display:none; position:absolute; top:4px; left:4px;
                                      background:rgba(168,85,247,0.9); color:#fff; font-size:10px; font-weight:800;
                                      letter-spacing:1px; border-radius:4px; padding:2px 6px;">GIF</span>
                            </div>
                            <label style="display:flex; align-items:center; gap:6px; margin-top:8px; cursor:pointer; font-size:13px; color:rgba(255,255,255,0.5);">
                                <input type="checkbox" name="remove_image" id="fRemoveImage" value="1"
                                       style="accent-color:#ef4444; width:15px; height:15px;">
                                Remove current image
                            </label>
                        </div>

                        {{-- New file picker --}}
                        <input type="file" name="image_file" id="fImageFile"
                               accept="image/jpeg,image/png,image/webp,image/gif"
                               class="form-control"
                               style="border:none; background:transparent; padding:0; font-size:13px;"
                               onchange="previewSelectedImage(this)">

                        {{-- Inline new-image preview --}}
                        <div id="newImagePreviewWrap" style="display:none; margin-top:10px;">
                            <div style="position:relative; display:inline-block;">
                                <img id="newImagePreview" src="" alt="Preview"
                                     style="max-height:100px; border-radius:6px; object-fit:contain; border:1px solid rgba(249,115,22,0.3);">
                                <span id="newGifBadge" style="display:none; position:absolute; top:4px; left:4px;
                                      background:rgba(168,85,247,0.9); color:#fff; font-size:10px; font-weight:800;
                                      letter-spacing:1px; border-radius:4px; padding:2px 6px;">GIF</span>
                            </div>
                            <p style="color:#22c55e; font-size:11px; margin-top:4px; font-weight:700;">✓ New image selected</p>
                        </div>

                        <p style="color:rgba(255,255,255,0.3); font-size:11px; margin-top:6px;">
                            JPG, PNG, WEBP, <strong style="color:rgba(168,85,247,0.8);">GIF</strong> — max 50 MB
                        </p>
                    </div>
                </div>

                {{-- ─── VIDEO ─────────────────────────────────────────────── --}}
                <div style="grid-column:1/-1;">
                    <label class="form-label">
                        BANNER VIDEO
                        <span style="color:rgba(255,255,255,0.3); font-size:11px; font-weight:400;">(optional — plays as background)</span>
                    </label>

                    {{-- Video type tabs --}}
                    <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px;">
                        @foreach(['none'=>'None','upload'=>'Upload File','youtube'=>'YouTube','vimeo'=>'Vimeo','facebook'=>'Facebook'] as $vt => $vl)
                        <label style="cursor:pointer;">
                            <input type="radio" name="video_source_tab" value="{{ $vt }}"
                                   {{ $vt === 'none' ? 'checked' : '' }}
                                   onchange="switchVideoTab('{{ $vt }}')"
                                   style="display:none;">
                            <span class="video-tab" data-tab="{{ $vt }}"
                                  style="display:inline-block; padding:6px 14px; border-radius:20px; font-size:13px; font-weight:700;
                                         background:{{ $vt === 'none' ? 'rgba(249,115,22,0.2)' : 'rgba(255,255,255,0.06)' }};
                                         color:{{ $vt === 'none' ? '#F97316' : 'rgba(255,255,255,0.5)' }};
                                         border:1px solid {{ $vt === 'none' ? '#F97316' : 'transparent' }};
                                         transition:all .15s; letter-spacing:.5px;">
                                {{ $vl }}
                            </span>
                        </label>
                        @endforeach
                    </div>

                    {{-- Upload panel --}}
                    <div id="videoPanel_upload" style="display:none; background:#111; border:1px dashed rgba(255,255,255,0.15); border-radius:10px; padding:16px;">
                        {{-- Current video preview --}}
                        <div id="currentVideoWrap" style="display:none; margin-bottom:10px;">
                            <video id="currentVideoPreview" src="" muted loop
                                   style="max-height:80px; border-radius:6px; max-width:100%;"></video>
                            <label style="display:flex; align-items:center; gap:6px; margin-top:8px; cursor:pointer; font-size:13px; color:rgba(255,255,255,0.5);">
                                <input type="checkbox" name="remove_video" id="fRemoveVideo" value="1"
                                       style="accent-color:#ef4444; width:15px; height:15px;">
                                Remove current video
                            </label>
                        </div>
                        <input type="file" name="video_file" id="fVideoFile" accept="video/mp4,video/webm,video/ogg"
                               class="form-control" style="border:none; background:transparent; padding:0; font-size:13px;">
                        <p style="color:rgba(255,255,255,0.3); font-size:11px; margin-top:6px;">MP4, WebM, OGG — max 50 MB. Will autoplay muted on loop.</p>
                    </div>

                    {{-- YouTube / Vimeo / Facebook embed panel --}}
                    <div id="videoPanel_embed" style="display:none;">
                        <input type="url" name="video_url" id="fVideoUrl" class="form-control"
                               placeholder="Paste YouTube, Vimeo, or Facebook video URL…">

                        {{-- Per-platform hints --}}
                        <div id="hintYoutube" style="display:none; margin-top:6px;">
                            <p style="color:rgba(255,255,255,0.35); font-size:11px;">
                                YouTube embed:
                                <code style="color:#F97316;">https://www.youtube.com/embed/VIDEO_ID?autoplay=1&mute=1&loop=1&playlist=VIDEO_ID</code>
                            </p>
                        </div>
                        <div id="hintVimeo" style="display:none; margin-top:6px;">
                            <p style="color:rgba(255,255,255,0.35); font-size:11px;">
                                Vimeo embed:
                                <code style="color:#F97316;">https://player.vimeo.com/video/VIDEO_ID?autoplay=1&muted=1&loop=1</code>
                            </p>
                        </div>
                        <div id="hintFacebook" style="display:none; margin-top:8px;">
                            {{-- ⚠ Important requirements notice --}}
                            <div style="background:rgba(24,119,242,0.1); border:1px solid rgba(24,119,242,0.3);
                                        border-radius:8px; padding:10px 14px; margin-bottom:8px;">
                                <div style="color:#60a5fa; font-weight:800; font-size:12px; margin-bottom:4px;">
                                    📋 Facebook Video Requirements
                                </div>
                                <ul style="color:rgba(255,255,255,0.55); font-size:11px; line-height:1.8; margin:0; padding-left:16px;">
                                    <li>Video privacy must be set to <strong style="color:#fff;">Public</strong></li>
                                    <li>Paste the normal Facebook video page URL — e.g.<br>
                                        <code style="color:#F97316;">https://www.facebook.com/watch/?v=VIDEO_ID</code><br>
                                        <code style="color:#F97316;">https://www.facebook.com/PageName/videos/VIDEO_ID</code>
                                    </li>
                                    <li><strong style="color:#fff;">Do not</strong> paste the <code>plugins/video.php</code> URL — it will not work</li>
                                </ul>
                            </div>
                        </div>

                        {{-- Preview container: iframe for YT/Vimeo, FB SDK div for Facebook --}}

                        {{-- YouTube / Vimeo iframe preview --}}
                        <div id="embedPreviewWrap" style="display:none; margin-top:10px; border-radius:10px; overflow:hidden; position:relative; padding-top:33%;">
                            <iframe id="embedPreview" src="" frameborder="0"
                                    allow="autoplay; encrypted-media" allowfullscreen
                                    style="position:absolute; inset:0; width:100%; height:100%;"></iframe>
                        </div>

                        {{-- Facebook SDK preview --}}
                        <div id="fbPreviewWrap" style="display:none; margin-top:10px; border-radius:10px; overflow:hidden; text-align:center; background:#111; padding:10px;">
                            <div id="fb-root"></div>
                            <div id="fbVideoEmbed" class="fb-video"
                                 data-href=""
                                 data-width="460"
                                 data-autoplay="true"
                                 data-allowfullscreen="false"
                                 data-show-text="false">
                            </div>
                            <p style="color:rgba(255,255,255,0.35); font-size:11px; margin-top:8px;">
                                If the preview shows "Video Unavailable", the video is private or embedding is disabled on Facebook.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Active toggle --}}
                <div style="grid-column:1/-1; display:flex; align-items:center; gap:10px;">
                    <input type="checkbox" name="active" id="fActive" value="1" checked
                           style="width:18px; height:18px; accent-color:#F97316;">
                    <label for="fActive" class="form-label" style="margin:0; cursor:pointer;">Active (visible on storefront)</label>
                </div>
            </div>

            <div style="margin-top:24px; display:flex; gap:12px;">
                <button type="button" onclick="closeModal()" class="btn btn-outline" style="flex:1;">CANCEL</button>
                <button type="submit" class="btn btn-orange" style="flex:1; font-size:16px;">SAVE BANNER</button>
            </div>
        </form>
    </div>
</div>

<script>
// ── Facebook JavaScript SDK ───────────────────────────────────────────────────
// Loaded once; enables <div class="fb-video"> previews without iframe blocks.
window.fbAsyncInit = function() {
    FB.init({ xfbml: true, version: 'v21.0' });
};
(function(d, s, id) {
    if (d.getElementById(id)) return;
    var js = d.createElement(s); js.id = id; js.async = true; js.defer = true;
    js.crossOrigin = 'anonymous';
    js.src = 'https://connect.facebook.net/en_US/sdk.js';
    d.head.appendChild(js);
}(document, 'script', 'facebook-jssdk'));

// ── Auto-reopen modal if validation errors exist ──────────────────────────────
@if($errors->any())
window.addEventListener('DOMContentLoaded', () => openModal())
@endif

// ── Client-side file size check ───────────────────────────────────────────────
document.getElementById('bannerForm').addEventListener('submit', function(e) {
    const imgFile  = document.getElementById('fImageFile')?.files?.[0]
    const vidFile  = document.getElementById('fVideoFile')?.files?.[0]
    const MB       = 1024 * 1024
    if (imgFile && imgFile.size > 50 * MB) {
        e.preventDefault()
        alert(`Image too large (${(imgFile.size/MB).toFixed(1)} MB). Maximum is 50 MB.`)
        return
    }
    if (vidFile && vidFile.size > 100 * MB) {
        e.preventDefault()
        alert(`Video too large (${(vidFile.size/MB).toFixed(1)} MB). Maximum is 100 MB.\n\nTip: use YouTube/Vimeo instead for large videos.`)
        return
    }
})

// ── New image preview with GIF detection ─────────────────────────────────────
function previewSelectedImage(input) {
    const wrap    = document.getElementById('newImagePreviewWrap')
    const preview = document.getElementById('newImagePreview')
    const badge   = document.getElementById('newGifBadge')
    if (input.files && input.files[0]) {
        const file = input.files[0]
        preview.src = URL.createObjectURL(file)
        badge.style.display = file.type === 'image/gif' ? 'block' : 'none'
        wrap.style.display  = 'block'
    } else {
        wrap.style.display = 'none'
        preview.src = ''
    }
}

// ── Platform detection ────────────────────────────────────────────────────────
function detectPlatform(url) {
    if (!url) return null
    if (url.includes('youtube.com') || url.includes('youtu.be'))  return 'youtube'
    if (url.includes('vimeo.com'))                                 return 'vimeo'
    if (url.includes('facebook.com') || url.includes('fb.watch')) return 'facebook'
    return null
}

// ── Video tab switching ───────────────────────────────────────────────────────
function switchVideoTab(tab) {
    document.querySelectorAll('.video-tab').forEach(el => {
        const active = el.dataset.tab === tab
        el.style.background  = active ? 'rgba(249,115,22,0.2)' : 'rgba(255,255,255,0.06)'
        el.style.color       = active ? '#F97316'               : 'rgba(255,255,255,0.5)'
        el.style.borderColor = active ? '#F97316'               : 'transparent'
    })
    const isEmbed = (tab === 'youtube' || tab === 'vimeo' || tab === 'facebook')
    document.getElementById('videoPanel_upload').style.display = tab === 'upload' ? 'block' : 'none'
    document.getElementById('videoPanel_embed').style.display  = isEmbed            ? 'block' : 'none'

    ;['youtube','vimeo','facebook'].forEach(p => {
        const el = document.getElementById('hint' + p.charAt(0).toUpperCase() + p.slice(1))
        if (el) el.style.display = (tab === p) ? 'block' : 'none'
    })

    if (tab !== 'upload') document.getElementById('fVideoFile').value = ''
    if (tab === 'upload' || tab === 'none') {
        document.getElementById('fVideoUrl').value = ''
        hideAllPreviews()
    }
}

function hideAllPreviews() {
    document.getElementById('embedPreviewWrap').style.display = 'none'
    document.getElementById('embedPreview').src = ''
    document.getElementById('fbPreviewWrap').style.display = 'none'
    document.getElementById('fbVideoEmbed').dataset.href = ''
}

// ── Live embed preview ─────────────────────────────────────────────────────────
document.getElementById('fVideoUrl')?.addEventListener('input', function() {
    const url      = this.value.trim()
    const platform = detectPlatform(url)

    // Auto-select the right tab when user pastes a URL
    if (platform) {
        switchVideoTab(platform)
        const radio = document.querySelector(`input[name="video_source_tab"][value="${platform}"]`)
        if (radio) radio.checked = true
    }

    if (!url) { hideAllPreviews(); return }

    if (platform === 'facebook') {
        // ── Use FB SDK instead of iframe ──────────────────────────────────────
        document.getElementById('embedPreviewWrap').style.display = 'none'
        document.getElementById('embedPreview').src = ''

        const embed = document.getElementById('fbVideoEmbed')
        embed.dataset.href = url           // store the original Facebook URL
        document.getElementById('fbPreviewWrap').style.display = 'block'

        // Re-parse the FB SDK so it renders the new video
        if (window.FB) {
            FB.XFBML.parse(document.getElementById('fbPreviewWrap'))
        }
        // If SDK hasn't loaded yet, fbAsyncInit will auto-parse on load

    } else if (platform === 'youtube' || platform === 'vimeo') {
        document.getElementById('fbPreviewWrap').style.display = 'none'
        document.getElementById('fbVideoEmbed').dataset.href = ''

        document.getElementById('embedPreview').src = url
        document.getElementById('embedPreviewWrap').style.display = 'block'
    } else {
        hideAllPreviews()
    }
})

// Sync color pickers ↔ text inputs
document.getElementById('fBgColor')?.addEventListener('input', function() {
    document.getElementById('fBgColorText').value = this.value
})
document.getElementById('fTextColor')?.addEventListener('input', function() {
    document.getElementById('fTextColorText').value = this.value
})

// ── Modal open / close ────────────────────────────────────────────────────────
function openModal(id, title, subtitle, badge, bgColor, textColor, image, order, active, video, videoType) {
    document.getElementById('bannerModal').style.display = 'flex'

    if (id) {
        document.getElementById('modalTitle').textContent  = 'EDIT BANNER'
        document.getElementById('bannerForm').action       = `/dashboard/banners/${id}`
        document.getElementById('formMethod').value        = 'PUT'
        document.getElementById('fTitle').value            = title    || ''
        document.getElementById('fSubtitle').value         = subtitle || ''
        document.getElementById('fBadge').value            = badge    || ''
        document.getElementById('fOrder').value            = order    ?? 0
        document.getElementById('fActive').checked         = active   ?? true

        const bg = bgColor   || '#111111'
        const tc = textColor || '#F97316'
        document.getElementById('fBgColor').value       = bg
        document.getElementById('fBgColorText').value   = bg
        document.getElementById('fTextColor').value     = tc
        document.getElementById('fTextColorText').value = tc

        // Image
        if (image) {
            document.getElementById('currentImagePreview').src = image
            document.getElementById('currentImageWrap').style.display = 'block'
            const gifBadge = document.getElementById('currentGifBadge')
            if (gifBadge) gifBadge.style.display = image.toLowerCase().endsWith('.gif') ? 'block' : 'none'
        } else {
            document.getElementById('currentImageWrap').style.display = 'none'
        }
        document.getElementById('newImagePreviewWrap').style.display = 'none'
        document.getElementById('newImagePreview').src = ''

        // Video
        if (video && videoType === 'upload') {
            document.getElementById('currentVideoPreview').src = video
            document.getElementById('currentVideoWrap').style.display = 'block'
            switchVideoTab('upload')
            const r = document.querySelector('input[name="video_source_tab"][value="upload"]')
            if (r) r.checked = true
        } else if (video && (videoType === 'youtube' || videoType === 'vimeo' || videoType === 'facebook')) {
            document.getElementById('fVideoUrl').value = video
            switchVideoTab(videoType)
            const r = document.querySelector(`input[name="video_source_tab"][value="${videoType}"]`)
            if (r) r.checked = true
            document.getElementById('fVideoUrl').dispatchEvent(new Event('input'))
        } else {
            switchVideoTab('none')
            const r = document.querySelector('input[name="video_source_tab"][value="none"]')
            if (r) r.checked = true
        }
    } else {
        document.getElementById('modalTitle').textContent  = 'ADD BANNER'
        document.getElementById('bannerForm').action       = '{{ route("dashboard.banners.store") }}'
        document.getElementById('formMethod').value        = 'POST'
        document.getElementById('bannerForm').reset()
        document.getElementById('fActive').checked         = true
        document.getElementById('fBgColor').value          = '#111111'
        document.getElementById('fBgColorText').value      = '#111111'
        document.getElementById('fTextColor').value        = '#F97316'
        document.getElementById('fTextColorText').value    = '#F97316'
        document.getElementById('currentImageWrap').style.display   = 'none'
        document.getElementById('newImagePreviewWrap').style.display = 'none'
        document.getElementById('newImagePreview').src               = ''
        document.getElementById('currentVideoWrap').style.display    = 'none'
        hideAllPreviews()
        switchVideoTab('none')
        const r = document.querySelector('input[name="video_source_tab"][value="none"]')
        if (r) r.checked = true
    }
}

function closeModal() {
    document.getElementById('bannerModal').style.display = 'none'
}

document.getElementById('bannerModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal()
})
</script>

<style>
/* ── Banner modal responsive ───────────────────────────────────────────── */
@media (max-width: 540px) {
    .banner-modal-box {
        padding: 16px !important;
        border-radius: 14px !important;
        max-height: 96vh !important;
    }
    .banner-form-grid {
        grid-template-columns: 1fr !important;
    }
    /* On mobile, make badge/order and bg/text color fields full-width */
    .banner-form-grid > div {
        grid-column: 1 / -1 !important;
    }
    /* Keep video tab pills wrapping nicely */
    .video-tab {
        padding: 5px 10px !important;
        font-size: 12px !important;
    }
}
/* Restore 2-col for badge+order and bg+text color on ≥ 541px */
@media (min-width: 541px) {
    .banner-form-grid > div:not([style*="grid-column:1/-1"]):not([style*="grid-column: 1 / -1"]) {
        grid-column: auto !important;
    }
}
</style>

@endif
@endsection
