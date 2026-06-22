@extends('dashboard.layout')
@section('title', strtoupper(__('dashboard.nav.videos')))

@section('content')

@include('dashboard._permission_check', ['feature' => 'products'])
@php $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false; @endphp
@if(!$_permDenied)
@php
function vid_img_url(?string $path): string {
    if (!$path) return '';
    $p = trim((string) $path);
    if (!$p) return '';
    if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) return $p;
    $cleanPath = preg_replace('/^\/?storage\//', '', $p);
    return '/storage/' . ltrim($cleanPath, '/');
}

// Same public, no-key-needed thumbnail pattern used on the storefront —
// lets the admin card preview show a real frame even when no thumbnail
// was uploaded in the CMS for a YouTube video.
function vid_youtube_thumb(?string $url): ?string {
    if (!$url) return null;
    if (!preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{6,})/', $url, $m)) {
        return null;
    }
    return "https://img.youtube.com/vi/{$m[1]}/hqdefault.jpg";
}
@endphp

{{-- Header --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
        <p style="color:rgba(255,255,255,0.8); font-size: var(--title-size);">{{ $videos->count() }} videos total</p>
    </div>
    <button onclick="openVideoModal()" class="btn btn-orange" style="font-size: var(--title-size);">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        ADD VIDEO
    </button>
</div>

@if(session('success'))
<div style="background:rgba(34,197,94,0.12); border:1px solid rgba(34,197,94,0.35); color:#22c55e;
     border-radius:10px; padding:12px 16px; margin-bottom:16px; font-weight:700; font-size: var(--title-size);">
    ✓ {{ session('success') }}
</div>
@endif

{{-- Video cards grid --}}
<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px;">
    @forelse($videos as $v)
    <div style="background:#1a1a1a; border:1px solid rgba(255,255,255,0.07); border-radius:14px; overflow:hidden;">

        <div style="position:relative; height:160px; background:#000; display:flex; align-items:center; justify-content:center; overflow:hidden;">
            @if($v->thumbnail)
                <img src="{{ vid_img_url($v->thumbnail) }}" alt="{{ $v->title }}"
                     style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:.6;">
            @elseif($v->video_type === 'youtube' && vid_youtube_thumb($v->video))
                <img src="{{ vid_youtube_thumb($v->video) }}" alt="{{ $v->title }}"
                     style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:.6;">
            @elseif($v->video_type === 'upload')
                <video src="{{ vid_img_url($v->video) }}" muted
                       style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:.5;"></video>
            @endif

            <div style="position:relative; z-index:2; display:flex; flex-direction:column; align-items:center; gap:6px;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="rgba(255,255,255,0.85)"><path d="M8 5v14l11-7z"/></svg>
                <span style="color:#fff; font-size: var(--title-size); font-weight:800; letter-spacing:1px; text-transform:uppercase;">
                    {{ $v->video_type }}
                </span>
            </div>

            {{-- Active toggle --}}
            <div style="position:absolute; top:8px; right:8px; z-index:3;">
                <form method="POST" action="{{ route('dashboard.videos.toggle', $v) }}" style="display:inline;">
                    @csrf @method('PATCH')
                    <button type="submit"
                        style="background:{{ $v->active ? 'rgba(34,197,94,0.85)' : 'rgba(107,114,128,0.85)' }};
                               color:#fff; border:none; border-radius:20px; padding:3px 10px;
                               font-size: var(--title-size); font-weight:700; cursor:pointer; letter-spacing:.5px;">
                        {{ $v->active ? '● ON' : '○ OFF' }}
                    </button>
                </form>
            </div>

            <div style="position:absolute; bottom:8px; left:8px; z-index:3;
                 background:rgba(0,0,0,0.6); color:rgba(255,255,255,0.6);
                 border-radius:6px; padding:2px 7px; font-size: var(--title-size); font-weight:700;">
                #{{ $v->order }}
            </div>
        </div>

        <div style="padding:12px 14px; display:flex; align-items:center; justify-content:space-between; gap:8px;">
            <div style="min-width:0;">
                <div style="font-size: var(--title-size); font-weight:700; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    {{ $v->title }}
                </div>
                @if($v->product)
                <div style="font-size: var(--title-size); color:rgba(249,115,22,0.8); margin-top:2px;">
                    🔗 {{ $v->product->name }}
                </div>
                @endif
            </div>
            <div style="display:flex; gap:8px; flex-shrink:0;">
                <button onclick="openVideoModal(
                {{ $v->id }},
                @js($v->title),
                @js($v->description ?? ''),
                @js($v->video_type),
                @js($v->video),
                @js($v->thumbnail ? vid_img_url($v->thumbnail) : ''),
                {{ $v->order }},
                {{ $v->active ? 'true' : 'false' }},
                {{ $v->product_id ?? 'null' }},
                @js($v->product->name ?? '')
                )" class="btn btn-outline btn-sm">EDIT</button>

                <form method="POST" action="{{ route('dashboard.videos.destroy', $v) }}"
                      onsubmit="return confirm('Delete this video?')" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline btn-sm" style="color:#ef4444; border-color:rgba(239,68,68,0.3);">DELETE</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div style="grid-column:1/-1; text-align:center; color:rgba(255,255,255,0.3); padding:60px 0; font-size: var(--title-size);">
        No videos yet — click ADD VIDEO to get started.
    </div>
    @endforelse
</div>

{{-- ════════════════════════════════════════════════════════════════════════
     MODAL
═══════════════════════════════════════════════════════════════════════════ --}}
<div id="videoModal" style="display:none; position:fixed; inset:0; z-index:1000;
     background:rgba(0,0,0,0.75); align-items:center; justify-content:center; padding:12px;">
    <div class="video-modal-box" style="background:#1a1a1a; border:1px solid rgba(255,255,255,0.1); border-radius:16px;
                padding:28px; width:100%; max-width:680px; max-height:94vh; overflow-y:auto; overflow-x:hidden;">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 id="videoModalTitle" style="font-size: var(--title-size); font-weight:900; color:#fff; letter-spacing:2px;">ADD VIDEO</h2>
            <button onclick="closeVideoModal()" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); color:rgba(255,255,255,0.5); font-size: var(--title-size); cursor:pointer; border-radius:8px; width:34px; height:34px; display:flex; align-items:center; justify-content:center; line-height:1;">✕</button>
        </div>

        <form id="videoForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="vFormMethod" value="POST">
            <input type="hidden" name="video_source_tab" id="vSourceTab" value="upload">

            @if($errors->any())
            <div style="background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.4);
                        border-radius:10px; padding:12px 16px; margin-bottom:16px;">
                <div style="color:#ef4444; font-weight:800; font-size: var(--title-size); margin-bottom:6px;">
                    ✕ Please fix the following errors:
                </div>
                @foreach($errors->all() as $e)
                    <div style="color:#fca5a5; font-size: var(--title-size); margin-bottom:2px;">• {{ $e }}</div>
                @endforeach
            </div>
            @endif

            <div class="video-form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

                {{-- Title --}}
                <div style="grid-column:1/-1;">
                    <label class="form-label">TITLE *</label>
                    <input type="text" name="title" id="vTitle" class="form-control" required placeholder="e.g. RTX 5090 Unboxing">
                </div>

                {{-- Description --}}
                <div style="grid-column:1/-1;">
                    <label class="form-label">DESCRIPTION</label>
                    <input type="text" name="description" id="vDescription" class="form-control" placeholder="Short caption shown under the video">
                </div>

                {{-- Order + Product --}}
                <div>
                    <label class="form-label">DISPLAY ORDER</label>
                    <input type="number" name="order" id="vOrder" class="form-control" min="0" value="0">
                </div>
                <div></div>

                <div style="grid-column:1/-1;">
                    <label class="form-label">APPLY TO PRODUCT (Optional)</label>
                    <div style="position:relative;">
                        <input type="text" id="vProductSearch" placeholder="🔍 Search product…"
                            autocomplete="off" oninput="vFilterProducts(this.value)"
                            onfocus="vShowProductDropdown()"
                            style="width:100%; background:#111; border:1px solid rgba(255,255,255,0.15); color:#fff; border-radius:8px; padding:10px 14px; box-sizing:border-box; outline:none;"
                            onfocusin="this.style.borderColor='#F97316'"
                            onblur="setTimeout(vHideProductDropdown,200); this.style.borderColor='rgba(255,255,255,0.15)'">
                        <select name="product_id" id="vProductId" style="display:none;">
                            <option value="">-- None --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                        <div id="vProductDropdown"
                            style="display:none; position:absolute; top:calc(100% + 4px); left:0; right:0; z-index:99999;
                            background:#1e1e1e; border:1px solid rgba(249,115,22,0.4); border-radius:10px;
                            max-height:220px; overflow-y:auto; box-shadow:0 8px 32px rgba(0,0,0,0.85);">
                            <div id="vProductList"></div>
                        </div>
                    </div>
                </div>

                {{-- ─── VIDEO SOURCE ─────────────────────────────────────── --}}
                <div style="grid-column:1/-1;">
                    <label class="form-label">VIDEO SOURCE *</label>

                    {{-- Tab pills --}}
                    <div style="display:flex; gap:8px; margin-bottom:12px; flex-wrap:wrap;">
                        <button type="button" class="vid-tab" data-tab="upload" onclick="switchVidTab('upload')"
                                style="padding:7px 14px; border-radius:20px; border:1px solid #F97316; background:rgba(249,115,22,0.2); color:#F97316; font-size: var(--title-size); font-weight:700; cursor:pointer;">
                            📁 Upload File
                        </button>
                        <button type="button" class="vid-tab" data-tab="youtube" onclick="switchVidTab('youtube')"
                                style="padding:7px 14px; border-radius:20px; border:1px solid transparent; background:rgba(255,255,255,0.06); color:rgba(255,255,255,0.5); font-size: var(--title-size); font-weight:700; cursor:pointer;">
                            ▶ YouTube
                        </button>
                        <button type="button" class="vid-tab" data-tab="facebook" onclick="switchVidTab('facebook')"
                                style="padding:7px 14px; border-radius:20px; border:1px solid transparent; background:rgba(255,255,255,0.06); color:rgba(255,255,255,0.5); font-size: var(--title-size); font-weight:700; cursor:pointer;">
                            f Facebook
                        </button>
                        <button type="button" class="vid-tab" data-tab="tiktok" onclick="switchVidTab('tiktok')"
                                style="padding:7px 14px; border-radius:20px; border:1px solid transparent; background:rgba(255,255,255,0.06); color:rgba(255,255,255,0.5); font-size: var(--title-size); font-weight:700; cursor:pointer;">
                            ♪ TikTok
                        </button>
                    </div>

                    {{-- Upload panel --}}
                    <div id="vPanel_upload" style="background:#111; border:1px dashed rgba(255,255,255,0.15); border-radius:10px; padding:16px;">
                        <div id="vCurrentVideoWrap" style="display:none; margin-bottom:10px;">
                            <video id="vCurrentVideoPreview" src="" muted controls
                                   style="max-height:120px; border-radius:6px; max-width:100%;"></video>
                        </div>
                        <input type="file" name="video_file" id="vVideoFile" accept="video/mp4,video/webm,video/ogg"
                               class="form-control" style="border:none; background:transparent; padding:0; font-size: var(--title-size);">
                        <p style="color:rgba(255,255,255,0.3); font-size: var(--title-size); margin-top:6px;">MP4, WebM, OGG — max 100 MB.</p>
                    </div>

                    {{-- Embed panel (shared input, label changes per tab) --}}
                    <div id="vPanel_embed" style="display:none; background:#111; border:1px dashed rgba(255,255,255,0.15); border-radius:10px; padding:16px;">
                        <input type="text" name="video_url" id="vVideoUrl" class="form-control" placeholder="Paste video URL"
                               oninput="vPreviewEmbed()">
                        <p id="vEmbedHint" style="color:rgba(255,255,255,0.3); font-size: var(--title-size); margin-top:6px;"></p>
                        <div id="vEmbedPreviewWrap" style="display:none; margin-top:10px;">
                            <div id="vEmbedPreviewBox" style="max-width:100%; border-radius:8px; overflow:hidden;"></div>
                        </div>
                    </div>
                </div>

                {{-- Thumbnail (optional) --}}
                <div style="grid-column:1/-1;">
                    <label class="form-label">
                        THUMBNAIL <span style="color:rgba(255,255,255,0.3); font-size: var(--title-size); font-weight:400;">(optional — shown before play)</span>
                    </label>
                    <div style="background:#111; border:1px dashed rgba(255,255,255,0.15); border-radius:10px; padding:16px;">
                        <div id="vCurrentThumbWrap" style="display:none; margin-bottom:10px;">
                            <img id="vCurrentThumbPreview" src="" alt="" style="max-height:90px; border-radius:6px; object-fit:contain;">
                            <label style="display:flex; align-items:center; gap:6px; margin-top:8px; cursor:pointer; font-size: var(--title-size); color:rgba(255,255,255,0.5);">
                                <input type="checkbox" name="remove_thumbnail" id="vRemoveThumb" value="1" style="accent-color:#ef4444; width:15px; height:15px;">
                                Remove current thumbnail
                            </label>
                        </div>
                        <input type="file" name="thumbnail_file" id="vThumbFile" accept="image/jpeg,image/png,image/webp"
                               class="form-control" style="border:none; background:transparent; padding:0; font-size: var(--title-size);">
                    </div>
                </div>

                {{-- Active toggle --}}
                <div style="grid-column:1/-1; display:flex; align-items:center; gap:10px;">
                    <input type="checkbox" name="active" id="vActive" value="1" checked
                           style="width:18px; height:18px; accent-color:#F97316;">
                    <label for="vActive" class="form-label" style="margin:0; cursor:pointer;">Active (visible on storefront)</label>
                </div>
            </div>

            <div style="margin-top:24px; display:flex; gap:12px;">
                <button type="button" onclick="closeVideoModal()" class="btn btn-outline" style="flex:1;">CANCEL</button>
                <button type="submit" class="btn btn-orange" style="flex:1; font-size: var(--title-size);">SAVE VIDEO</button>
            </div>
        </form>
    </div>
</div>

<script>
// ── Facebook SDK (for the admin-side live preview only) ────────────────────
window.fbAsyncInit = function() { FB.init({ xfbml: true, version: 'v21.0' }); };
(function(d, s, id) {
    if (d.getElementById(id)) return;
    var js = d.createElement(s); js.id = id; js.async = true; js.defer = true;
    js.crossOrigin = 'anonymous';
    js.src = 'https://connect.facebook.net/en_US/sdk.js';
    d.head.appendChild(js);
}(document, 'script', 'facebook-jssdk'));

// ── TikTok embed script (for the admin-side live preview) ──────────────────
(function(d, s, id) {
    if (d.getElementById(id)) return;
    var js = d.createElement(s); js.id = id; js.async = true;
    js.src = 'https://www.tiktok.com/embed.js';
    d.body.appendChild(js);
}(document, 'script', 'tiktok-embed-js'));

@if($errors->any())
window.addEventListener('DOMContentLoaded', () => openVideoModal())
@endif

// ── Tab switching ────────────────────────────────────────────────────────
const EMBED_HINTS = {
    youtube:  'For best results on Desktop: Copy the URL from the browser address bar while watching the video (e.g., youtube.com/watch?v=...).',
    facebook: 'Important: Open the video on a desktop browser. Copy the URL from the address bar. DO NOT use the "Share" button URL or a "Reel" link, as these often fail to embed.',
    tiktok:   'Open the video on a desktop browser. Copy the URL from the address bar (e.g., tiktok.com/@user/video/...).',
};

function switchVidTab(tab) {
    document.getElementById('vSourceTab').value = tab
    document.querySelectorAll('.vid-tab').forEach(el => {
        const active = el.dataset.tab === tab
        el.style.background  = active ? 'rgba(249,115,22,0.2)' : 'rgba(255,255,255,0.06)'
        el.style.color       = active ? '#F97316' : 'rgba(255,255,255,0.5)'
        el.style.borderColor = active ? '#F97316' : 'transparent'
    })
    const isEmbed = tab !== 'upload'
    document.getElementById('vPanel_upload').style.display = isEmbed ? 'none' : 'block'
    document.getElementById('vPanel_embed').style.display  = isEmbed ? 'block' : 'none'
    if (isEmbed) {
        document.getElementById('vEmbedHint').textContent = EMBED_HINTS[tab] || ''
        document.getElementById('vVideoFile').value = ''
        vPreviewEmbed()
    } else {
        document.getElementById('vVideoUrl').value = ''
        document.getElementById('vEmbedPreviewWrap').style.display = 'none'
    }
}

function vPreviewEmbed() {
    const tab = document.getElementById('vSourceTab').value
    const url = document.getElementById('vVideoUrl').value.trim()
    const box = document.getElementById('vEmbedPreviewBox')
    const wrap = document.getElementById('vEmbedPreviewWrap')

    if (!url) { wrap.style.display = 'none'; box.innerHTML = ''; return }

    if (tab === 'youtube') {
        const id = vExtractYouTubeId(url)
        box.innerHTML = id
            ? `<iframe width="100%" height="220" src="https://www.youtube.com/embed/${id}" frameborder="0" allowfullscreen></iframe>`
            : '<p style="color:#ef4444;font-size:13px;padding:8px;">Could not parse YouTube URL</p>'
        wrap.style.display = 'block'
    } else if (tab === 'facebook') {
        if (url.includes('/reel/')) {
            box.innerHTML = '<p style="color:#F97316;font-size:13px;padding:8px;line-height:1.5;">⚠ Facebook Reel links can\'t be embedded with the standard video player. Open the reel on Facebook, use the "Share → Embed" option (if available) or copy a regular post/video URL instead.</p>'
            wrap.style.display = 'block'
            return
        }
        box.innerHTML = `<div class="fb-video" data-href="${url}" data-width="auto" data-show-text="false"></div>`
        wrap.style.display = 'block'
        if (window.FB) FB.XFBML.parse(box)
    } else if (tab === 'tiktok') {
        const id = vExtractTikTokId(url)
        box.innerHTML = id
            ? `<blockquote class="tiktok-embed" cite="${url}" data-video-id="${id}" style="max-width:325px;min-width:200px;margin:0;"><section></section></blockquote>`
            : '<p style="color:#ef4444;font-size:13px;padding:8px;">Could not parse TikTok URL</p>'
        wrap.style.display = 'block'
        if (window.tiktokEmbed?.lib?.render) window.tiktokEmbed.lib.render(box)
    }
}

function vExtractYouTubeId(url) {
    const m = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{6,})/)
    return m ? m[1] : null
}
function vExtractTikTokId(url) {
    const m = url.match(/video\/(\d+)/)
    return m ? m[1] : null
}

// ── Product search dropdown ─────────────────────────────────────────────
const _allProductsV = [{ id: '', name: '-- None --' },
    @foreach ($products as $product)
        { id: '{{ $product->id }}', name: '{{ addslashes($product->name) }}' },
    @endforeach
]

function vBuildProductList(query) {
    const list = document.getElementById('vProductList')
    if (!list) return
    const q = (query || '').toLowerCase().trim()
    const filtered = q ? _allProductsV.filter(p => p.name.toLowerCase().includes(q)) : _allProductsV
    if (filtered.length === 0) {
        list.innerHTML = '<div style="padding:12px 14px;color:rgba(255,255,255,0.3);font-size: var(--title-size);">No products found</div>'
        return
    }
    list.innerHTML = filtered.map(p => `
        <div onclick="vSelectProduct('${p.id}', '${p.name.replace(/'/g,"&#39;")}')"
             style="padding:9px 14px; cursor:pointer; font-size: var(--title-size); color:rgba(255,255,255,0.85);"
             onmouseover="this.style.background='rgba(249,115,22,0.12)'"
             onmouseout="this.style.background='transparent'">
            ${p.name}
        </div>
    `).join('')
}
function vSelectProduct(id, name) {
    document.getElementById('vProductId').value = id
    document.getElementById('vProductSearch').value = id ? name : ''
    vHideProductDropdown()
}
function vShowProductDropdown() {
    vBuildProductList(document.getElementById('vProductSearch').value)
    document.getElementById('vProductDropdown').style.display = 'block'
}
function vHideProductDropdown() {
    const dd = document.getElementById('vProductDropdown')
    if (dd) dd.style.display = 'none'
}
function vFilterProducts(q) { vBuildProductList(q); vShowProductDropdown() }

// ── Modal open / close ──────────────────────────────────────────────────
function openVideoModal(id, title, description, videoType, video, thumbnail, order, active, productId, productName) {
    document.getElementById('videoModal').style.display = 'flex'

    if (id) {
        document.getElementById('videoModalTitle').textContent = 'EDIT VIDEO'
        document.getElementById('videoForm').action = `/dashboard/videos/${id}`
        document.getElementById('vFormMethod').value = 'PUT'
        document.getElementById('vTitle').value = title || ''
        document.getElementById('vDescription').value = description || ''
        document.getElementById('vOrder').value = order ?? 0
        document.getElementById('vActive').checked = active ?? true
        document.getElementById('vProductId').value = productId || ''
        document.getElementById('vProductSearch').value = productName || ''

        switchVidTab(videoType || 'upload')

        if (videoType === 'upload' && video) {
            document.getElementById('vCurrentVideoPreview').src = video
            document.getElementById('vCurrentVideoWrap').style.display = 'block'
        } else {
            document.getElementById('vCurrentVideoWrap').style.display = 'none'
        }

        if (videoType !== 'upload' && video) {
            document.getElementById('vVideoUrl').value = video
            vPreviewEmbed()
        }

        if (thumbnail) {
            document.getElementById('vCurrentThumbPreview').src = thumbnail
            document.getElementById('vCurrentThumbWrap').style.display = 'block'
        } else {
            document.getElementById('vCurrentThumbWrap').style.display = 'none'
        }
    } else {
        document.getElementById('videoModalTitle').textContent = 'ADD VIDEO'
        document.getElementById('videoForm').action = '{{ route("dashboard.videos.store") }}'
        document.getElementById('vFormMethod').value = 'POST'
        document.getElementById('videoForm').reset()
        document.getElementById('vProductId').value = ''
        document.getElementById('vProductSearch').value = ''
        document.getElementById('vActive').checked = true
        document.getElementById('vCurrentVideoWrap').style.display = 'none'
        document.getElementById('vCurrentThumbWrap').style.display = 'none'
        document.getElementById('vEmbedPreviewWrap').style.display = 'none'
        switchVidTab('upload')
    }
}

function closeVideoModal() {
    document.getElementById('videoModal').style.display = 'none'
}

document.getElementById('videoModal').addEventListener('click', function(e) {
    if (e.target === this) closeVideoModal()
})

// ── Client-side file size check ─────────────────────────────────────────
document.getElementById('videoForm').addEventListener('submit', function(e) {
    const vidFile = document.getElementById('vVideoFile')?.files?.[0]
    const MB = 1024 * 1024
    if (vidFile && vidFile.size > 100 * MB) {
        e.preventDefault()
        alert(`Video too large (${(vidFile.size/MB).toFixed(1)} MB). Maximum is 100 MB.`)
    }
})
</script>

<style>
#videoModal * { box-sizing: border-box; }
@media (max-width: 540px) {
    .video-modal-box { padding: 16px !important; border-radius: 14px !important; max-height: 96vh !important; }
    .video-form-grid { grid-template-columns: 1fr !important; }
    .video-form-grid > div { grid-column: 1 / -1 !important; }
}
</style>

@endif
@endsection
