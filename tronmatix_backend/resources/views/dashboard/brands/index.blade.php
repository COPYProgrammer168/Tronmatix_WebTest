@extends('dashboard.layout')

@section('title', strtoupper('Brand Logos'))

@section('content')
@include('dashboard._permission_check', ['feature' => 'products'])
@php
    $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false;
@endphp

@if(!$_permDenied)

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
    <div>
        <h1 style="font-size:var(--text-2xl); font-weight:800; letter-spacing:2px; color:var(--text); margin:0;">BRAND LOGOS</h1>
        <p style="font-size:var(--text-sm); color:var(--text-muted); margin-top:4px;">
            {{ $brands->count() }} brand{{ $brands->count() !== 1 ? 's' : '' }} · click a logo to edit
        </p>
    </div>
    <a href="{{ route('dashboard.brands.create') }}" class="btn btn-orange">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        ADD BRAND
    </a>
</div>

@if(session('success'))
<div style="background:rgba(34,197,94,0.12); border:1px solid rgba(34,197,94,0.35); color:#22c55e;
     border-radius:10px; padding:12px 16px; margin-bottom:16px; font-weight:700; font-size: var(--text-sm);">
    ✓ {{ session('success') }}
</div>
@endif

@if(session('error'))
<div style="background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.35); color:#ef4444;
     border-radius:10px; padding:12px 16px; margin-bottom:16px; font-weight:700; font-size: var(--text-sm);">
    ⚠ {{ session('error') }}
</div>
@endif

{{-- Logo grid --}}
<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:16px;">
    @forelse($brands as $brand)
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; overflow:hidden;
                transition:transform .15s, box-shadow .15s; cursor:pointer;"
         onmouseenter="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 24px rgba(0,0,0,0.18)'"
         onmouseleave="this.style.transform=''; this.style.boxShadow=''"
         onclick="window.location='{{ route('dashboard.brands.edit', $brand) }}'">

        {{-- Logo area --}}
        <div style="height:120px; background:var(--surface-2); display:flex; align-items:center; justify-content:center;
                    position:relative; overflow:hidden; padding:16px;">
            @if($brand->image)
                <img src="{{ storage_url($brand->image) }}" alt="{{ $brand->name }}"
                     style="max-width:100%; max-height:100%; object-fit:contain; filter:drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
            @else
                <div style="width:56px; height:56px; border-radius:14px; background:rgba(249,115,22,0.10);
                            border:1.5px solid rgba(249,115,22,0.25); display:flex; align-items:center;
                            justify-content:center; font-size:24px;">🏷️</div>
            @endif

            {{-- ON/OFF badge --}}
            <form method="POST" action="{{ route('dashboard.brands.toggle', $brand) }}"
                  onclick="event.stopPropagation()" style="position:absolute; top:8px; right:8px;">
                @csrf @method('PATCH')
                <button type="submit" title="{{ $brand->is_active ? 'Active' : 'Hidden' }}"
                    style="width:10px; height:10px; border-radius:50%; border:none; cursor:pointer;
                           background:{{ $brand->is_active ? '#22c55e' : '#6b7280' }};">
                </button>
            </form>
        </div>

        {{-- Name + actions --}}
        <div style="padding:10px 14px 14px; display:flex; align-items:center; justify-content:space-between; gap:8px;">
            <div style="min-width:0; flex:1;">
                <div style="font-size:var(--text-sm); font-weight:800; color:var(--text); letter-spacing:0.5px;
                            white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    {{ $brand->name }}
                </div>
                <div style="font-size:11px; color:var(--text-xfaint); margin-top:1px;">
                    · {{ $brand->order }}
                </div>
            </div>
            <div style="display:flex; gap:4px; flex-shrink:0;">
                <a href="{{ route('dashboard.brands.edit', $brand) }}"
                   style="width:30px; height:30px; border-radius:8px; border:1px solid var(--border-input);
                          display:flex; align-items:center; justify-content:center; text-decoration:none;
                          color:var(--text-muted); font-size:13px;"
                   title="Edit">✏️</a>
                <form method="POST" action="{{ route('dashboard.brands.destroy', $brand) }}"
                      onclick="event.stopPropagation()"
                      onsubmit="event.stopPropagation(); return confirm('Delete &quot;{{ $brand->name }}&quot;?')"
                      style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" title="Delete"
                        style="width:30px; height:30px; border-radius:8px; border:1px solid rgba(239,68,68,0.3);
                               background:transparent; color:#ef4444; cursor:pointer; font-size:13px;
                               display:flex; align-items:center; justify-content:center;">🗑</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div style="grid-column:1/-1; text-align:center; padding:80px 20px;">
        <div style="font-size:48px; margin-bottom:12px;">🏷️</div>
        <p style="color:var(--text-xfaint); font-size:var(--text-md); margin-bottom:16px;">No brands yet.</p>
        <a href="{{ route('dashboard.brands.create') }}" class="btn btn-orange">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            ADD FIRST BRAND
        </a>
    </div>
    @endforelse
</div>

@endif
@endsection
