@props([
    'label' => '',
    'value' => '—',
    'trend' => null, // ['pct' => int, 'trend' => 'up'|'down'|'flat']
    'color' => 'orange', // orange | green | blue | purple | red | yellow
    'icon' => '',
])

@php
    $colorMap = [
        'orange' => '#F97316',
        'green'  => '#22C55E',
        'blue'   => '#3B82F6',
        'purple' => '#A855F7',
        'red'    => '#EF4444',
        'yellow' => '#EAB308',
    ];
    $c = $colorMap[$color] ?? '#F97316';

    $trendUp = ($trend['trend'] ?? 'flat') === 'up';
    $trendDown = ($trend['trend'] ?? 'flat') === 'down';
    $pct = $trend['pct'] ?? 0;
    $showTrend = $trend && ($trendUp || $trendDown);
@endphp

<div class="stat-card" style="
    position:relative; overflow:hidden; cursor:default;
    transition:border-color 0.2s, transform 0.2s;
" onmouseenter="this.style.borderColor='{{ $c }}';this.style.transform='translateY(-2px)'"
   onmouseleave="this.style.borderColor='';this.style.transform=''">
    <div style="position:absolute; top:0; left:0; width:3px; height:100%;
                background:{{ $c }}; border-radius:3px 0 0 3px; opacity:0.5;"></div>
    <div class="stat-icon" style="border-color: {{ $c }}44; background:{{ $c }}18;">
        @if($icon)
            {{ $icon }}
        @else
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="{{ $c }}" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
            </svg>
        @endif
    </div>
    <div>
        <div class="stat-value">{{ $value }}</div>
        <div class="stat-label">{{ $label }}</div>
        @if($showTrend)
            <div style="display:inline-flex; align-items:center; gap:3px; margin-top:4px;
                        padding:1px 7px; border-radius:4px; font-size:10px; font-weight:700;
                        {{ $trendUp ? 'background:rgba(34,197,94,0.12); color:#22C55E;' : 'background:rgba(239,68,68,0.12); color:#EF4444;' }}">
                {{ $trendUp ? '▲' : '▼' }} {{ $pct }}% {{ $trendUp ? 'more' : 'less' }}
            </div>
        @endif
    </div>
</div>
