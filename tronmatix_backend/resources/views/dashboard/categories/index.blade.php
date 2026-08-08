@extends('dashboard.layout')

@section('title', strtoupper(__('dashboard.categories.title')))

@section('content')
@include('dashboard._permission_check', ['feature' => 'products'])
@php
    $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false;
@endphp

@if(!$_permDenied)

{{-- Tree data --}}
<script>
  window.__CATEGORY_TREE__ = @json($tree ?? []);
</script>

<div style="padding: 24px;">

    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:var(--text-2xl); font-weight:800; letter-spacing:2px; color:var(--text); margin:0;">{{ strtoupper(__('dashboard.categories.title')) }}</h1>
            <p style="font-size:var(--text-sm); color:var(--text-muted); margin-top:4px;">{{ __('dashboard.categories.subtitle') }}</p>
        </div>
        <button onclick="openModal('category')" class="btn btn-orange">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            {{ strtoupper(__('dashboard.categories.addCategory')) }}
        </button>
    </div>

    {{-- Toast --}}
    <div id="cm-toast" style="display:none; position:fixed; top:20px; right:20px; z-index:9999; background:rgba(34,197,94,0.95); color:#fff; padding:12px 20px; border-radius:10px; font-weight:700; font-size:var(--text-sm); box-shadow:0 4px 20px rgba(0,0,0,0.2);">
        ✓ Saved
    </div>

    {{-- Tree --}}
    <div id="category-tree" style="background:var(--surface); border:1px solid var(--border); border-radius:14px; overflow:hidden;">
        @if(count($tree ?? []) === 0)
            <div style="text-align:center; padding:60px 20px; color:var(--text-xfaint);">
                <div style="font-size:48px; margin-bottom:12px;">📂</div>
                <p style="font-size:var(--text-md); font-weight:700;">{{ __('dashboard.categories.noCategories') }}</p>
                <p style="font-size:var(--text-sm); margin-top:4px;">{{ __('dashboard.categories.noCategoriesHint') }}</p>
            </div>
        @else
            @include('dashboard.category-management.partials.tree', ['items' => $tree ?? [], 'level' => 0])
        @endif
    </div>
</div>

{{-- ═══════════════════ MODALS ═══════════════════ --}}

{{-- Category Modal --}}
<div id="modal-category" class="cm-modal" style="display:none;">
    <div class="cm-modal-overlay" onclick="closeModal('category')"></div>
    <div class="cm-modal-box">
        <div class="cm-modal-header">
            <h3 id="modal-category-title" style="margin:0; font-size:var(--text-lg); font-weight:800; letter-spacing:1px;">{{ strtoupper(__('dashboard.categories.addCategory')) }}</h3>
            <button onclick="closeModal('category')" class="cm-modal-close">✕</button>
        </div>
        <form id="form-category" onsubmit="saveCategory(event)">
            <input type="hidden" name="id" id="cat-id">
            <div style="display:flex; flex-direction:column; gap:16px; padding:20px 0;">
                <div>
                    <label style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.name')) }} *</label>
                    <input type="text" name="name" id="cat-name" required class="s-input" style="margin-top:6px;" placeholder="{{ __('dashboard.categories.namePlaceholder') }}">
                    <div style="font-size:12px; color:var(--text-xfaint); margin-top:4px;">{{ __('dashboard.categories.slugAuto') }}</div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.order')) }}</label>
                        <input type="number" name="order" id="cat-order" value="0" min="0" class="s-input" style="margin-top:6px;">
                    </div>
                    <div>
                        <label style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.status')) }}</label>
                        <label style="display:flex; align-items:center; gap:8px; margin-top:10px; cursor:pointer;">
                            <input type="checkbox" name="is_active" id="cat-is_active" value="1" checked style="width:18px; height:18px; accent-color:#F97316;">
                            <span style="font-size:14px; color:var(--text-muted);">{{ __('dashboard.categories.active') }}</span>
                        </label>
                    </div>
                </div>
                <div style="display:flex; gap:12px; margin-top:8px;">
                    <button type="submit" class="btn btn-orange" style="flex:1; padding:14px;">💾 {{ strtoupper(__('dashboard.categories.save')) }}</button>
                    <button type="button" onclick="closeModal('category')" class="btn" style="flex:1; padding:14px; border:1.5px solid var(--border-input); background:transparent; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.cancel')) }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Main Category Modal --}}
<div id="modal-main-category" class="cm-modal" style="display:none;">
    <div class="cm-modal-overlay" onclick="closeModal('main-category')"></div>
    <div class="cm-modal-box">
        <div class="cm-modal-header">
            <h3 id="modal-main-category-title" style="margin:0; font-size:var(--text-lg); font-weight:800; letter-spacing:1px;">{{ strtoupper(__('dashboard.categories.addMainCategory')) }}</h3>
            <button onclick="closeModal('main-category')" class="cm-modal-close">✕</button>
        </div>
        <form id="form-main-category" onsubmit="saveMainCategory(event)">
            <input type="hidden" name="id" id="mc-id">
            <input type="hidden" name="category_id" id="mc-category_id">
            <div style="display:flex; flex-direction:column; gap:16px; padding:20px 0;">
                <div>
                    <label style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.parentCategory')) }}</label>
                    <div id="mc-parent-display" style="margin-top:6px; padding:8px 12px; background:var(--surface-2); border-radius:8px; font-size:14px; font-weight:700; color:var(--orange);"></div>
                </div>
                <div>
                    <label style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.name')) }} *</label>
                    <input type="text" name="name" id="mc-name" required class="s-input" style="margin-top:6px;" placeholder="{{ __('dashboard.categories.namePlaceholder') }}">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.order')) }}</label>
                        <input type="number" name="order" id="mc-order" value="0" min="0" class="s-input" style="margin-top:6px;">
                    </div>
                    <div>
                        <label style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.status')) }}</label>
                        <label style="display:flex; align-items:center; gap:8px; margin-top:10px; cursor:pointer;">
                            <input type="checkbox" name="is_active" id="mc-is_active" value="1" checked style="width:18px; height:18px; accent-color:#F97316;">
                            <span style="font-size:14px; color:var(--text-muted);">{{ __('dashboard.categories.active') }}</span>
                        </label>
                    </div>
                </div>
                <div style="display:flex; gap:12px; margin-top:8px;">
                    <button type="submit" class="btn btn-orange" style="flex:1; padding:14px;">💾 {{ strtoupper(__('dashboard.categories.save')) }}</button>
                    <button type="button" onclick="closeModal('main-category')" class="btn" style="flex:1; padding:14px; border:1.5px solid var(--border-input); background:transparent; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.cancel')) }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Sub Category Modal --}}
<div id="modal-sub-category" class="cm-modal" style="display:none;">
    <div class="cm-modal-overlay" onclick="closeModal('sub-category')"></div>
    <div class="cm-modal-box">
        <div class="cm-modal-header">
            <h3 id="modal-sub-category-title" style="margin:0; font-size:var(--text-lg); font-weight:800; letter-spacing:1px;">{{ strtoupper(__('dashboard.categories.addSubCategory')) }}</h3>
            <button onclick="closeModal('sub-category')" class="cm-modal-close">✕</button>
        </div>
        <form id="form-sub-category" onsubmit="saveSubCategory(event)">
            <input type="hidden" name="id" id="sc-id">
            <input type="hidden" name="main_category_id" id="sc-main_category_id">
            <div style="display:flex; flex-direction:column; gap:16px; padding:20px 0;">
                <div>
                    <label style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.parentMainCategory')) }}</label>
                    <div id="sc-parent-display" style="margin-top:6px; padding:8px 12px; background:var(--surface-2); border-radius:8px; font-size:14px; font-weight:700; color:var(--orange);"></div>
                </div>
                <div>
                    <label style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.name')) }} *</label>
                    <input type="text" name="name" id="sc-name" required class="s-input" style="margin-top:6px;" placeholder="e.g. UNDER 1K">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.order')) }}</label>
                        <input type="number" name="order" id="sc-order" value="0" min="0" class="s-input" style="margin-top:6px;">
                    </div>
                    <div>
                        <label style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.status')) }}</label>
                        <label style="display:flex; align-items:center; gap:8px; margin-top:10px; cursor:pointer;">
                            <input type="checkbox" name="is_active" id="sc-is_active" value="1" checked style="width:18px; height:18px; accent-color:#F97316;">
                            <span style="font-size:14px; color:var(--text-muted);">{{ __('dashboard.categories.active') }}</span>
                        </label>
                    </div>
                </div>
                <div style="display:flex; gap:12px; margin-top:8px;">
                    <button type="submit" class="btn btn-orange" style="flex:1; padding:14px;">💾 {{ strtoupper(__('dashboard.categories.save')) }}</button>
                    <button type="button" onclick="closeModal('sub-category')" class="btn" style="flex:1; padding:14px; border:1.5px solid var(--border-input); background:transparent; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.cancel')) }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Brand Modal --}}
<div id="modal-brand" class="cm-modal" style="display:none;">
    <div class="cm-modal-overlay" onclick="closeModal('brand')"></div>
    <div class="cm-modal-box">
        <div class="cm-modal-header">
            <h3 id="modal-brand-title" style="margin:0; font-size:var(--text-lg); font-weight:800; letter-spacing:1px;">{{ strtoupper(__('dashboard.categories.addBrand')) }}</h3>
            <button onclick="closeModal('brand')" class="cm-modal-close">✕</button>
        </div>
        <form id="form-brand" onsubmit="saveBrand(event)" enctype="multipart/form-data">
            <input type="hidden" name="id" id="brand-id">
            <input type="hidden" name="sub_category_id" id="brand-sub_category_id">
            <div style="display:flex; flex-direction:column; gap:16px; padding:20px 0;">
                <div>
                    <label style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.parentSubCategory')) }}</label>
                    <div id="brand-parent-display" style="margin-top:6px; padding:8px 12px; background:var(--surface-2); border-radius:8px; font-size:14px; font-weight:700; color:var(--orange);"></div>
                </div>
                <div>
                    <label style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.name')) }} *</label>
                    <input type="text" name="name" id="brand-name" required class="s-input" style="margin-top:6px;" placeholder="e.g. INTEL 12TH">
                </div>
                <div>
                    <label style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.brandLogoImage')) }}</label>
                    <input type="file" name="image_file" id="brand-image_file" accept="image/*" class="s-input" style="margin-top:6px; padding:8px;">
                    <div style="font-size:12px; color:var(--text-xfaint); margin-top:4px;">JPG, PNG, WebP or GIF (max 50MB)</div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.order')) }}</label>
                        <input type="number" name="order" id="brand-order" value="0" min="0" class="s-input" style="margin-top:6px;">
                    </div>
                    <div>
                        <label style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.status')) }}</label>
                        <label style="display:flex; align-items:center; gap:8px; margin-top:10px; cursor:pointer;">
                            <input type="checkbox" name="is_active" id="brand-is_active" value="1" checked style="width:18px; height:18px; accent-color:#F97316;">
                            <span style="font-size:14px; color:var(--text-muted);">{{ __('dashboard.categories.active') }}</span>
                        </label>
                    </div>
                </div>
                <div style="display:flex; gap:12px; margin-top:8px;">
                    <button type="submit" class="btn btn-orange" style="flex:1; padding:14px;">💾 {{ strtoupper(__('dashboard.categories.save')) }}</button>
                    <button type="button" onclick="closeModal('brand')" class="btn" style="flex:1; padding:14px; border:1.5px solid var(--border-input); background:transparent; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.cancel')) }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Delete Confirm Modal --}}
<div id="modal-delete" class="cm-modal" style="display:none;">
    <div class="cm-modal-overlay" onclick="closeModal('delete')"></div>
    <div class="cm-modal-box" style="max-width:420px; text-align:center;">
        <div style="padding:32px 24px 24px;">
            <div style="font-size:48px; margin-bottom:12px;">⚠️</div>
            <h3 style="margin:0 0 8px; font-size:var(--text-lg); font-weight:800;">{{ strtoupper(__('dashboard.categories.deleteTitle')) }}</h3>
            <p id="delete-message" style="color:var(--text-muted); font-size:var(--text-sm); margin:0 0 20px;"></p>
            <input type="hidden" id="delete-type">
            <input type="hidden" id="delete-id">
            <div style="display:flex; gap:12px;">
                <button onclick="closeModal('delete')" class="btn" style="flex:1; padding:12px; border:1.5px solid var(--border-input); background:transparent; color:var(--text-muted);">{{ strtoupper(__('dashboard.categories.cancel')) }}</button>
                <button onclick="confirmDelete()" class="btn" style="flex:1; padding:12px; background:#ef4444; color:#fff; border:none;">{{ strtoupper(__('dashboard.categories.delete')) }}</button>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Tree rows ──────────────────────────────────── */
.cm-tree-row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    border-bottom: 1px solid var(--border);
    transition: background 0.15s;
    min-height: 48px;
}
.cm-tree-row:last-child { border-bottom: none; }
.cm-tree-row:hover { background: var(--surface-2); }
.cm-tree-row.drag-over {
    background: rgba(249,115,22,0.08);
    border-top: 2px solid #F97316;
}
.cm-tree-row.dragging { opacity: 0.4; }

.cm-drag-handle {
    cursor: grab;
    color: var(--text-xfaint);
    padding: 4px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    flex-shrink: 0;
}
.cm-drag-handle:active { cursor: grabbing; }

.cm-chevron {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: var(--text-muted);
    transition: transform 0.2s;
    flex-shrink: 0;
}
.cm-chevron.expanded { transform: rotate(90deg); }
.cm-chevron.hidden-chevron { visibility: hidden; }

.cm-node-name {
    flex: 1;
    font-weight: 700;
    font-size: var(--text-sm);
    color: var(--text);
    letter-spacing: 0.5px;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.cm-node-name a {
    color: inherit;
    text-decoration: none;
}
.cm-node-name a:hover { color: #F97316; }

.cm-node-order {
    width: 56px;
    text-align: center;
    font-size: var(--text-xs);
    font-weight: 700;
    color: var(--text-muted);
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 4px;
    flex-shrink: 0;
}

.cm-toggle {
    position: relative;
    width: 36px;
    height: 20px;
    flex-shrink: 0;
}
.cm-toggle input { opacity: 0; width: 0; height: 0; }
.cm-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background: #ccc;
    border-radius: 20px;
    transition: 0.2s;
}
.cm-toggle-slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 2px;
    bottom: 2px;
    background: white;
    border-radius: 50%;
    transition: 0.2s;
}
.cm-toggle input:checked + .cm-toggle-slider { background: #F97316; }
.cm-toggle input:checked + .cm-toggle-slider:before { transform: translateX(16px); }

.cm-btn-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    cursor: pointer;
    border: none;
    background: transparent;
    color: var(--text-muted);
    transition: all 0.15s;
    flex-shrink: 0;
    font-size: 14px;
}
.cm-btn-icon:hover {
    background: var(--surface-2);
    color: var(--text);
}
.cm-btn-icon.cm-btn-delete:hover {
    background: rgba(239,68,68,0.1);
    color: #ef4444;
}
.cm-btn-icon.cm-btn-add {
    color: #F97316;
    font-weight: 700;
    font-size: 16px;
}

.cm-children {
    padding-left: 32px;
    background: var(--surface-2);
    border-left: 2px solid var(--border);
    margin-left: 24px;
}
.cm-children.collapsed { display: none; }

/* ── Modals ─────────────────────────────────────── */
.cm-modal {
    position: fixed;
    inset: 0;
    z-index: 9000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.cm-modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
}
.cm-modal-box {
    position: relative;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    width: 90%;
    max-width: 520px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    animation: cmFadeIn 0.2s ease;
}
@keyframes cmFadeIn {
    from { opacity: 0; transform: translateY(20px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.cm-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
}
.cm-modal-close {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    background: var(--surface-2);
    color: var(--text-muted);
    cursor: pointer;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.cm-modal-close:hover {
    background: var(--border);
    color: var(--text);
}

/* ── Buttons ─────────────────────────────────────── */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 700;
    font-size: var(--text-sm);
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: all 0.15s;
    font-family: 'Rajdhani', sans-serif;
    letter-spacing: 0.5px;
}
.btn-orange {
    background: #F97316;
    color: #fff;
}
.btn-orange:hover { background: #ea580c; }
.btn-sm {
    padding: 4px 10px;
    font-size: 11px;
    border-radius: 6px;
}
.btn-outline {
    background: transparent;
    border: 1.5px solid var(--border-input);
    color: var(--text-muted);
}
.btn-outline:hover {
    border-color: #F97316;
    color: #F97316;
}
.s-input {
    width: 100%;
    padding: 8px 12px;
    border-radius: 8px;
    border: 1.5px solid var(--border-input);
    background: var(--surface);
    color: var(--text);
    font-size: var(--text-sm);
    font-family: 'Rajdhani', sans-serif;
    transition: border-color 0.15s;
}
.s-input:focus {
    outline: none;
    border-color: #F97316;
}
</style>

<script>
// ══════════════════════════════════════════════════════
// Category Management — vanilla JS accordion + AJAX
// ══════════════════════════════════════════════════════
(function() {
    'use strict';

    // ── Translations exposed from Blade (respects current locale) ──
    @php
        $_catTrans = [
            'editCategory' => __('dashboard.categories.editCategory'),
            'addCategory' => __('dashboard.categories.addCategory'),
            'editMainCategory' => __('dashboard.categories.editMainCategory'),
            'addMainCategory' => __('dashboard.categories.addMainCategory'),
            'editSubCategory' => __('dashboard.categories.editSubCategory'),
            'addSubCategory' => __('dashboard.categories.addSubCategory'),
            'editBrand' => __('dashboard.categories.editBrand'),
            'addBrand' => __('dashboard.categories.addBrand'),
            'deleteMessage' => __('dashboard.categories.deleteMessage'),
            'deleted' => __('dashboard.categories.deleted'),
            'saved' => __('dashboard.categories.saved'),
            'saveFailed' => __('dashboard.categories.saveFailed'),
            'toggleFailed' => __('dashboard.categories.toggleFailed'),
            'enabled' => __('dashboard.categories.enabled'),
            'disabled' => __('dashboard.categories.disabled'),
        ];
    @endphp
    const CAT_TRANSLATIONS = @json($_catTrans);
    const t = (key) => CAT_TRANSLATIONS[key] || key;

    // ── Accordion ────────────────────────────────────
    window.toggleNode = function(btn) {
        const node = btn.closest('.cm-tree-node');
        const children = node.querySelector('.cm-children');
        if (!children) return;
        const isCollapsed = children.classList.contains('collapsed');
        if (isCollapsed) {
            children.classList.remove('collapsed');
            btn.classList.add('expanded');
        } else {
            children.classList.add('collapsed');
            btn.classList.remove('expanded');
        }
    };

    // ── Modals ───────────────────────────────────────
    window.openModal = function(type, data) {
        const modal = document.getElementById('modal-' + type);
        if (!modal) return;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        const form = modal.querySelector('form');
        if (form) form.reset();

        if (type === 'main-category' && data) {
            document.getElementById('mc-category_id').value = data.parentId || '';
            document.getElementById('mc-parent-display').textContent = data.parentName || '';
            if (data.id) {
                document.getElementById('modal-main-category-title').textContent = t('editMainCategory');
                document.getElementById('mc-id').value = data.id;
                document.getElementById('mc-name').value = data.name || '';
                document.getElementById('mc-order').value = data.order || 0;
                document.getElementById('mc-is_active').checked = data.is_active !== false;
            } else {
                document.getElementById('modal-main-category-title').textContent = t('addMainCategory');
                document.getElementById('mc-id').value = '';
            }
        } else if (type === 'sub-category' && data) {
            document.getElementById('sc-main_category_id').value = data.parentId || '';
            document.getElementById('sc-parent-display').textContent = data.parentName || '';
            if (data.id) {
                document.getElementById('modal-sub-category-title').textContent = t('editSubCategory');
                document.getElementById('sc-id').value = data.id;
                document.getElementById('sc-name').value = data.name || '';
                document.getElementById('sc-order').value = data.order || 0;
                document.getElementById('sc-is_active').checked = data.is_active !== false;
            } else {
                document.getElementById('modal-sub-category-title').textContent = t('addSubCategory');
                document.getElementById('sc-id').value = '';
            }
        } else if (type === 'brand' && data) {
            document.getElementById('brand-sub_category_id').value = data.parentId || '';
            document.getElementById('brand-parent-display').textContent = data.parentName || '';
            if (data.id) {
                document.getElementById('modal-brand-title').textContent = t('editBrand');
                document.getElementById('brand-id').value = data.id;
                document.getElementById('brand-name').value = data.name || '';
                document.getElementById('brand-order').value = data.order || 0;
                document.getElementById('brand-is_active').checked = data.is_active !== false;
            } else {
                document.getElementById('modal-brand-title').textContent = t('addBrand');
                document.getElementById('brand-id').value = '';
            }
        } else if (type === 'category') {
            if (data && data.id) {
                document.getElementById('modal-category-title').textContent = t('editCategory');
                document.getElementById('cat-id').value = data.id;
                document.getElementById('cat-name').value = data.name || '';
                document.getElementById('cat-order').value = data.order || 0;
                document.getElementById('cat-is_active').checked = data.is_active !== false;
            } else {
                document.getElementById('modal-category-title').textContent = t('addCategory');
                document.getElementById('cat-id').value = '';
            }
        }
    };

    window.closeModal = function(type) {
        const modal = document.getElementById('modal-' + type);
        if (modal) modal.style.display = 'none';
        document.body.style.overflow = '';
    };

    // ── AJAX save ────────────────────────────────────
    function csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    window.saveCategory = function(e) {
        e.preventDefault();
        const id = document.getElementById('cat-id').value;
        const form = document.getElementById('form-category');
        const data = new FormData(form);
        const url = id ? '/dashboard/categories/' + id : '/dashboard/categories';
        submitAjax(url, data, id, 'category', 'cat-');
    };

    window.saveMainCategory = function(e) {
        e.preventDefault();
        const id = document.getElementById('mc-id').value;
        const form = document.getElementById('form-main-category');
        const data = new FormData(form);
        const url = id ? '/dashboard/main-categories/' + id : '/dashboard/main-categories';
        submitAjax(url, data, id, 'main-category', 'mc-');
    };

    window.saveSubCategory = function(e) {
        e.preventDefault();
        const id = document.getElementById('sc-id').value;
        const form = document.getElementById('form-sub-category');
        const data = new FormData(form);
        const url = id ? '/dashboard/sub-categories/' + id : '/dashboard/sub-categories';
        submitAjax(url, data, id, 'sub-category', 'sc-');
    };

    window.saveBrand = function(e) {
        e.preventDefault();
        const id = document.getElementById('brand-id').value;
        const form = document.getElementById('form-brand');
        const data = new FormData(form);
        const url = id ? '/dashboard/brands/' + id : '/dashboard/brands';
        submitAjax(url, data, id, 'brand', 'brand-');
    };

    function submitAjax(url, formData, id, type, prefix) {
        if (id) formData.append('_method', 'PUT');
        formData.append('_token', csrf());

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' },
        })
        .then(r => r.ok ? r.json() : Promise.reject(r))
        .then(json => {
            showToast(t('saved'));
            closeModal(type);
            // TODO: update tree row or append new row
            location.reload();
        })
        .catch(err => {
            console.error('Save failed:', err);
            showToast(t('saveFailed'), true);
        });
    }

    // ── URL builder ──────────────────────────────────
    function entityUrl(type) {
        const paths = {
            'category': '/dashboard/categories',
            'main-category': '/dashboard/main-categories',
            'sub-category': '/dashboard/sub-categories',
            'brand': '/dashboard/brands',
        };
        return paths[type] || ('/dashboard/' + type + 's');
    }

    // ── Toggle active ────────────────────────────────
    window.toggleActive = function(type, id, btn) {
        const formData = new FormData();
        formData.append('_token', csrf());
        formData.append('_method', 'PATCH');

        fetch(entityUrl(type) + '/' + id + '/toggle', {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' },
        })
        .then(r => r.ok ? r.json() : Promise.reject(r))
        .then(json => {
            btn.checked = !btn.checked;
            showToast(btn.checked ? t('enabled') : t('disabled'));
        })
        .catch(err => {
            console.error('Toggle failed:', err);
            btn.checked = !btn.checked;
            showToast(t('toggleFailed'), true);
        });
    };

    // ── Delete ───────────────────────────────────────
    window.promptDelete = function(type, id, name) {
        document.getElementById('delete-type').value = type;
        document.getElementById('delete-id').value = id;
        document.getElementById('delete-message').textContent =
            t('deleteMessage').replace(':name', name);
        openModal('delete');
    };

    window.confirmDelete = function() {
        const type = document.getElementById('delete-type').value;
        const id = document.getElementById('delete-id').value;
        const formData = new FormData();
        formData.append('_token', csrf());
        formData.append('_method', 'DELETE');

        fetch(entityUrl(type) + '/' + id, {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' },
        })
        .then(r => r.ok ? r.json() : Promise.reject(r))
        .then(json => {
            closeModal('delete');
            // data-id / data-type live on the .cm-tree-node, so query that directly.
            const node = document.querySelector('.cm-tree-node[data-id="' + id + '"][data-type="' + type + '"]');
            if (node) node.remove();
            showToast(t('deleted'));
        })
        .catch(err => {
            console.error('Delete failed:', err);
            showToast('Delete failed', true);
        });
    };

    // ── Toast ────────────────────────────────────────
    function showToast(msg, isError) {
        const toast = document.getElementById('cm-toast');
        toast.textContent = msg;
        toast.style.background = isError ? 'rgba(239,68,68,0.95)' : 'rgba(34,197,94,0.95)';
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 2500);
    }

    // ── Drag-reorder ─────────────────────────────────
    let dragSrcNode = null;
    let dragType = null;

    window.initDrag = function(row, type) {
        row.setAttribute('draggable', 'true');

        row.addEventListener('dragstart', function(e) {
            dragSrcNode = row.closest('.cm-tree-node');
            dragType = type;
            row.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', row.dataset.id || '');
        });

        row.addEventListener('dragend', function() {
            row.classList.remove('dragging');
            document.querySelectorAll('.cm-tree-row').forEach(r => r.classList.remove('drag-over'));
            dragSrcNode = null;
            dragType = null;
        });

        row.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            if (row === dragSrcNode?.querySelector?.('.cm-tree-row')) return;
            row.classList.add('drag-over');
        });

        row.addEventListener('dragleave', function() {
            row.classList.remove('drag-over');
        });

        row.addEventListener('drop', function(e) {
            e.preventDefault();
            row.classList.remove('drag-over');
            if (!dragSrcNode) return;
            const targetNode = row.closest('.cm-tree-node');
            if (targetNode === dragSrcNode) return;

            // Only allow reordering among direct siblings.
            const container = targetNode.parentNode;
            if (dragSrcNode.parentNode !== container) return;

            const nodes = [...container.querySelectorAll(':scope > .cm-tree-node')];
            const fromIdx = nodes.indexOf(dragSrcNode);
            const toIdx = nodes.indexOf(targetNode);

            if (fromIdx < 0 || toIdx < 0) return;

            if (fromIdx < toIdx) {
                container.insertBefore(dragSrcNode, targetNode.nextSibling);
            } else {
                container.insertBefore(dragSrcNode, targetNode);
            }

            const newOrder = nodes.map((n, i) => ({
                id: n.dataset.id,
                order: i + 1,
                type: dragType
            }));

            submitReorder(newOrder);
        });
    };

    function submitReorder(orders) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/dashboard/category-management/reorder';
        form.style.display = 'none';

        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = csrf();
        form.appendChild(token);

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'orders';
        input.value = JSON.stringify(orders);
        form.appendChild(input);

        document.body.appendChild(form);
        form.submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.cm-tree-row').forEach(row => {
            initDrag(row, row.dataset.type);
        });
    });

})();
</script>

@endif
@endsection
