{{--
    The Cash In tab summary line for the current filters (PaymentService::listSummary()):
    "Cash In: <total> — <method> <amount> · … · Withholding <amount>"; withholding is not part of the total; no refunds on this tab
    (they are Cash Out, 2026-10-14; the Refunds part stays for a list that has them).
--}}
<span class="font-medium">Cash In: {{ number_format($summary['total'], 2) }}</span>
@php($parts = [])
@foreach ($summary['methods'] as $name => $amount)
    @php($parts[] = e(Str::humanize($name)) . ' ' . number_format($amount, 2))
@endforeach
@if ($summary['refunds'])
    @php($parts[] = 'Refunds <span class="text-danger">−' . number_format($summary['refunds'], 2) . '</span>')
@endif
@if ($summary['withholding'])
    @php($parts[] = 'Withholding ' . number_format($summary['withholding'], 2))
@endif
@if ($parts)
    <span class="text-slate-500"> — {!! implode(' · ', $parts) !!}</span>
@endif
