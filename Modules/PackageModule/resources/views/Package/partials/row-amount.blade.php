{{-- the list Amount column: the net (green, bold); with a discount a small line "<price> · −<discount>%" --}}
<span class="font-bold text-success">{{ number_format($package->after_discount, 2) }}</span>
@if ((float) $package->discount_percentage > 0)
    <div class="text-xs text-slate-500">{{ number_format($package->amount, 2) }} · −{{ rtrim(rtrim(number_format($package->discount_percentage, 2), '0'), '.') }}%</div>
@endif
