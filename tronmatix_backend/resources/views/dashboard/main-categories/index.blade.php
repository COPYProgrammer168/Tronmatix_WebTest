@extends('dashboard.layout')

@section('title', strtoupper('Main Categories'))

@section('content')
@include('dashboard._permission_check', ['feature' => 'products'])
@php
    $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false;
    $user = Auth::guard('admin')->user() ?? Auth::guard('staff')->user();
@endphp

@if(!$_permDenied)

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
        <h1 style="font-size:var(--text-2xl); font-weight:800; letter-spacing:2px; color:var(--text); margin:0;">MAIN CATEGORIES</h1>
        <p style="font-size:var(--text-sm); color:var(--text-muted); margin-top:4px;">
            Level 2 — {{ $mainCategories->count() }} total · each belongs to a Category (Level 1)
        </p>
    </div>
    <a href="{{ route('dashboard.main-categories.create') }}" class="btn btn-orange">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        ADD MAIN CATEGORY
    </a>
</div>

@if(session('success'))
<div style="background:rgba(34,197,94,0.12); border:1px solid rgba(34,197,94,0.35); color:#22c55e;
     border-radius:10px; padding:12px 16px; margin-bottom:16px; font-weight:700; font-size: var(--text-sm);">
    ✓ {{ session('success') }}
</div>
@endif

<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:16px;">
    @forelse($mainCategories as $mc)
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:14px; overflow:hidden;">
        <div style="height:90px; background:var(--surface-2); display:flex; align-items:center; justify-content:center; position:relative; overflow:hidden;">
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(249,115,22,0.12); border:1px solid rgba(249,115,22,0.25);
                        display:flex; align-items:center; justify-content:center; font-size:20px;">📂</div>
            <div style="position:absolute; bottom:8px; left:8px; z-index:3;
                 background:rgba(0,0,0,0.6); color:rgba(255,255,255,0.7);
                 border-radius:6px; padding:2px 7px; font-size:var(--text-xs); font-weight:700;">
                #{{ $mc->order }}
            </div>
            <form method="POST" action="{{ route('dashboard.main-categories.toggle', $mc) }}" style="position:absolute; top:8px; right:8px; z-index:3;">
                @csrf @method('PATCH')
                <button type="submit"
                    style="background:{{ $mc->is_active ? 'rgba(34,197,94,0.85)' : 'rgba(107,114,128,0.85)' }};
                           color:#fff; border:none; border-radius:20px; padding:3px 10px;
                           font-size:var(--text-xs); font-weight:700; cursor:pointer; letter-spacing:.5px;">
                    {{ $mc->is_active ? '● ON' : '○ OFF' }}
                </button>
            </form>
        </div>

        <div style="padding:14px 16px;">
            <div style="font-size:var(--text-md); font-weight:800; color:var(--text); letter-spacing:1px;">{{ $mc->name }}</div>
            <div style="font-size:var(--text-xs); color:var(--text-xfaint); margin-top:2px;">/{{ $mc->slug }}</div>
            <div style="font-size:var(--text-xs); color:var(--text-muted); margin-top:6px;">
                Parent: <span style="color:var(--orange); font-weight:700;">{{ $mc->category->name ?? '—' }}</span>
            </div>
            <div style="display:flex; align-items:center; gap:6px; margin-top:10px;">
                <span style="background:rgba(249,115,22,0.1); border:1px solid rgba(249,115,22,0.25); color:var(--orange);
                     border-radius:20px; padding:2px 10px; font-size:var(--text-xs); font-weight:700;">
                    {{ $mc->sub_categories_count }} sub categories
                </span>
            </div>
        </div>

        <div style="padding:0 16px 14px; display:flex; gap:8px;">
            <a href="{{ route('dashboard.main-categories.edit', $mc) }}" class="btn btn-outline btn-sm" style="flex:1;">EDIT</a>
            <form method="POST" action="{{ route('dashboard.main-categories.destroy', $mc) }}"
                  onsubmit="return confirm('Delete &quot;{{ $mc->name }}&quot;? Its sub categories and brands will cascade.')" style="display:inline;">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm"
                    style="border:1px solid #ef4444; color:#ef4444; background:transparent;">DELETE</button>
            </form>
        </div>
    </div>
    @empty
    <div style="grid-column:1/-1; text-align:center; color:var(--text-xfaint); padding:60px 0; font-size:var(--text-md);">
        No main categories yet.
        <a href="{{ route('dashboard.main-categories.create') }}" style="color:var(--orange); text-decoration:none; font-weight:700;">Add the first one</a>
    </div>
    @endforelse
</div>

@endif
@endsection
