<div class="flex items-center gap-2">
    @if ($unit->color)
        @include('unitmodule::partials.color-swatch', ['value' => $unit->color->value])
    @endif
    <a href="{{ route('account.spaces.units.show', [$space->id, $unit->id]) }}" class="font-medium">{{ $unit->name }}</a>
</div>
