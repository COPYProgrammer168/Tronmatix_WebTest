@props(['url'])
<tr>
<td class="header" style="padding: 25px 0; text-align: center;">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="{{ asset('logo.png') }}" class="logo" alt="Tronmatix Computer" style="height: 48px; margin-top: 0; margin-bottom: 10px; max-height: 48px; width: auto;">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
