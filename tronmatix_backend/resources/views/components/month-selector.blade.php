@props(['month', 'label' => 'Period:', 'margin' => true])

@php
    $prev = \Carbon\Carbon::parse($month->format('Y-m').'-01')->subMonth();
    $next = \Carbon\Carbon::parse($month->format('Y-m').'-01')->addMonth();
    $isFuture = $next->isFuture();
@endphp

<div style="display:flex; align-items:center; gap:10px; {{ $margin ? 'margin-bottom:20px;' : '' }}">
    @if($label)
        <div style="font-size: var(--title-size); font-weight:700; color:var(--text-muted); letter-spacing:1px; text-transform:uppercase;">
            {{ $label }}
        </div>
    @endif
    <a href="{{ request()->url() }}?month={{ $prev->format('Y-m') }}"
       style="padding:5px 10px; border-radius:6px; border:1px solid var(--border-input);
              background:var(--surface-2); color:var(--text-muted); text-decoration:none;
              font-size: var(--title-size); cursor:pointer; display:inline-flex; align-items:center;
              transition:all .15s;"
       onmouseover="this.style.borderColor='#F97316';this.style.color='#F97316'"
       onmouseout="this.style.borderColor='';this.style.color=''">
        ◀
    </a>
    <input type="month" value="{{ $month->format('Y-m') }}"
           onchange="window.location.href=this.dataset.base+'&month='+this.value"
           data-base="{{ request()->url() }}?month="
           style="padding:5px 10px; border-radius:6px; border:1px solid var(--border-input);
                  background:var(--surface-2); color:var(--text-primary); font-size: var(--title-size);
                  font-family: 'Rajdhani', sans-serif; font-weight:500; outline:none; cursor:pointer;" />
    @if(!$isFuture)
        <a href="{{ request()->url() }}?month={{ $next->format('Y-m') }}"
           style="padding:5px 10px; border-radius:6px; border:1px solid var(--border-input);
                  background:var(--surface-2); color:var(--text-muted); text-decoration:none;
                  font-size: var(--title-size); cursor:pointer; display:inline-flex; align-items:center;
                  transition:all .15s;"
           onmouseover="this.style.borderColor='#F97316';this.style.color='#F97316'"
           onmouseout="this.style.borderColor='';this.style.color=''">
            ▶
        </a>
    @endif
    <span style="font-size: var(--title-size); color:var(--text-xfaint); margin-left:4px;">
        {{ $month->format('F Y') }}
    </span>
</div>
