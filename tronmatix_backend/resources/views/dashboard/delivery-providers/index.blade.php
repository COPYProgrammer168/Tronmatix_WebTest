@extends('dashboard.layout')

@section('title', 'DELIVERY PROVIDERS')

@section('content')
@php
    $user = Auth::guard('admin')->user() ?? Auth::guard('staff')->user();
@endphp

<div style="max-width:1200px; margin:0 auto; padding:24px;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:28px; font-weight:800; letter-spacing:2px; color:#fff; margin:0;">DELIVERY PROVIDERS</h1>
            <p style="font-size:14px; color:rgba(255,255,255,0.4); margin-top:4px;">Manage delivery zones, providers, fees and estimated times</p>
        </div>
        <a href="{{ route('dashboard.delivery-providers.create') }}" style="
            padding:12px 24px; border-radius:10px; border:none; cursor:pointer; text-decoration:none;
            background:linear-gradient(135deg,#F97316,#ea580c); color:#fff;
            font-family:Rajdhani,sans-serif; font-size:16px; font-weight:700; letter-spacing:1px;
            box-shadow:0 4px 16px rgba(249,115,22,0.3); transition:all .2s;
        " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(249,115,22,0.45)'"
           onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 16px rgba(249,115,22,0.3)'">
            + ADD PROVIDER
        </a>
    </div>

    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:16px; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse; font-family:Rajdhani,sans-serif;">
            <thead>
                <tr style="background:rgba(255,255,255,0.04); border-bottom:1px solid rgba(255,255,255,0.06);">
                    <th style="text-align:left; padding:14px 16px; font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.5);">NAME</th>
                    <th style="text-align:left; padding:14px 16px; font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.5);">ZONE</th>
                    <th style="text-align:left; padding:14px 16px; font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.5);">FEE</th>
                    <th style="text-align:left; padding:14px 16px; font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.5);">EST. TIME</th>
                    <th style="text-align:center; padding:14px 16px; font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.5);">STATUS</th>
                    <th style="text-align:center; padding:14px 16px; font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.5);">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($providers as $provider)
                <tr style="border-bottom:1px solid rgba(255,255,255,0.04); transition:background .2s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.02)'"
                    onmouseout="this.style.background='transparent'">
                    <td style="padding:14px 16px;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            @if($provider->logo)
                            <img src="{{ asset($provider->logo) }}" alt="{{ $provider->name }}" style="width:36px; height:36px; border-radius:8px; object-fit:contain; background:rgba(255,255,255,0.05);">
                            @else
                            <div style="width:36px; height:36px; border-radius:8px; background:rgba(249,115,22,0.1); border:1px solid rgba(249,115,22,0.2); display:flex; align-items:center; justify-content:center; font-size:18px;">🚚</div>
                            @endif
                            <div>
                                <div style="font-size:15px; font-weight:700; color:#fff;">{{ $provider->name }}</div>
                                <div style="font-size:12px; color:rgba(255,255,255,0.3); margin-top:2px;">Sort: {{ $provider->sort_order }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="padding:14px 16px; font-size:14px; color:rgba(255,255,255,0.7);">
                        {{ $provider->deliveryZone->name ?? '—' }}
                    </td>
                    <td style="padding:14px 16px;">
                        @if($provider->fee !== null)
                        <span style="font-size:15px; font-weight:800; color:#F97316;">${{ number_format($provider->fee, 2) }}</span>
                        @else
                        <span style="font-size:13px; color:rgba(255,255,255,0.4); font-style:italic;">Varies</span>
                        @endif
                    </td>
                    <td style="padding:14px 16px; font-size:14px; color:rgba(255,255,255,0.7);">
                        {{ $provider->estimated_time ?? '—' }}
                    </td>
                    <td style="padding:14px 16px; text-align:center;">
                        <form method="POST" action="{{ route('dashboard.delivery-providers.toggle', $provider) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <button type="submit" style="
                                padding:6px 14px; border-radius:20px; border:none; cursor:pointer; font-family:Rajdhani,sans-serif;
                                font-size:12px; font-weight:700; letter-spacing:1px; transition:all .2s;
                                background:{{ $provider->is_active ? 'rgba(34,197,94,0.12)' : 'rgba(239,68,68,0.12)' }};
                                color:{{ $provider->is_active ? '#22c55e' : '#ef4444' }};
                                border:1px solid {{ $provider->is_active ? 'rgba(34,197,94,0.25)' : 'rgba(239,68,68,0.25)' }};
                            " onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='none'">
                                {{ $provider->is_active ? '● ACTIVE' : '○ INACTIVE' }}
                            </button>
                        </form>
                    </td>
                    <td style="padding:14px 16px; text-align:center;">
                        <div style="display:flex; gap:8px; justify-content:center;">
                            <a href="{{ route('dashboard.delivery-providers.edit', $provider) }}" style="
                                padding:8px 16px; border-radius:8px; text-decoration:none; font-size:13px; font-weight:700;
                                background:rgba(59,130,246,0.1); color:#3b82f6; border:1px solid rgba(59,130,246,0.2);
                                transition:all .2s;
                            " onmouseover="this.style.background='rgba(59,130,246,0.18)'" onmouseout="this.style.background='rgba(59,130,246,0.1)'">Edit</a>
                            <form method="POST" action="{{ route('dashboard.delivery-providers.destroy', $provider) }}" style="display:inline;" onsubmit="return confirm('Delete {{ $provider->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="
                                    padding:8px 16px; border-radius:8px; border:none; cursor:pointer; font-size:13px; font-weight:700;
                                    background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.2);
                                    transition:all .2s;
                                " onmouseover="this.style.background='rgba(239,68,68,0.18)'" onmouseout="this.style.background='rgba(239,68,68,0.1)'">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:40px; text-align:center; color:rgba(255,255,255,0.3); font-size:15px;">
                        No delivery providers yet. <a href="{{ route('dashboard.delivery-providers.create') }}" style="color:#F97316; text-decoration:none; font-weight:700;">Add the first one</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
