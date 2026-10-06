<div class="flex items-center gap-2">
    @include('unitmodule::partials.color-swatch', ['value' => $color->value])
    <span class="font-medium">{{ $color->name }}</span>
</div>
