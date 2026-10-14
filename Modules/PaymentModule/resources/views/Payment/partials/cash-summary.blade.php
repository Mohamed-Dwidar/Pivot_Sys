{{--
    The All / Cash Out summary line (CashListService::summary(), $direction all | out):
    All: "In <in> · Out <out> · Net <net> — <method> <net> · … · Withholding <amount>" (withholding apart, not cash);
    Cash Out: "Cash Out <out> (Refunds <r> · Paid out <e> = the expenses) — <method> <out> · …".
--}}
@if ($direction === 'out')
    <span class="font-medium">Cash Out: <span class="text-danger">{{ number_format($summary['out'], 2) }}</span></span>
    @if ($summary['refunds'])
        <span class="text-slate-500">(Refunds {{ number_format($summary['refunds'], 2) }} · Paid out {{ number_format($summary['expenses'], 2) }})</span>
    @endif
    @php($parts = collect($summary['methods'])->filter(fn ($amounts) => $amounts['out'])->map(fn ($amounts, $name) => e(Str::humanize($name)) . ' ' . number_format($amounts['out'], 2)))
@else
    <span class="font-medium">In <span class="text-success">{{ number_format($summary['in'], 2) }}</span></span>
    · <span class="font-medium">Out <span class="text-danger">{{ number_format($summary['out'], 2) }}</span></span>
    · <span class="font-medium">Net {{ number_format($summary['net'], 2) }}</span>
    @php($parts = collect($summary['methods'])->map(fn ($amounts, $name) => e(Str::humanize($name)) . ' ' . number_format($amounts['net'], 2)))
    @if ($summary['withholding'])
        @php($parts->push('Withholding ' . number_format($summary['withholding'], 2)))
    @endif
@endif
@if ($parts->isNotEmpty())
    <span class="text-slate-500"> — {!! $parts->implode(' · ') !!}</span>
@endif
