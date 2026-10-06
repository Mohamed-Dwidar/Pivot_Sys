{{-- Color dot ($value = hex). SVG fill, so no inline CSS is needed. --}}
<svg class="color-swatch {{ $class ?? '' }}" viewBox="0 0 16 16" aria-hidden="true"><rect width="16" height="16" rx="4" fill="{{ $value }}"/></svg>
