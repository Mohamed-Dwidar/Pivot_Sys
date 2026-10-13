{{--
    The "Space › Unit" cell: the unit color first. $stacked (the reservations list): two lines — the space, then the unit (no "›").
--}}
<div class="flex items-center gap-2">
    @if ($reservation->unit?->color)
        @include('unitmodule::partials.color-swatch', ['value' => $reservation->unit->color->value])
    @endif
    @if (!empty($stacked))
        <div>
            <div>{{ $reservation->space?->name ?? '-' }}</div>
            <div class="text-slate-500">{{ $reservation->unit?->name ?? '-' }}</div>
        </div>
    @else
        <span>{{ $reservation->space?->name ?? '-' }} &rsaquo; {{ $reservation->unit?->name ?? '-' }}</span>
    @endif
</div>
