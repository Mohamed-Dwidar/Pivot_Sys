{{-- the list "Space › Unit" column: the unit color first --}}
<div class="flex items-center gap-2">
    @if ($reservation->unit?->color)
        @include('unitmodule::partials.color-swatch', ['value' => $reservation->unit->color->value])
    @endif
    <span>{{ $reservation->space?->name ?? '-' }} &rsaquo; {{ $reservation->unit?->name ?? '-' }}</span>
</div>
