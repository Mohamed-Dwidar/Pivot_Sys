{{--
    The totals of a shift (ShiftClosingService::summary(), also saved on the closing): per payment method
    Cash In (payments) - Refunds - Cash Out (expenses) = Net; then the sum (UI names 2026-10-14): Cash In, Refunds (minus),
    Cash Out (minus), Net to hand over; withholding apart (not cash). Expenses with no method (method 0) are in Cash Out only.
    $summary: ['methods', 'refunds', 'expenses', 'withholding', 'cash_total', 'expenses_total', 'net_total', 'count']
    (closings saved before 2026-10-13 have no 'refunds', before 2026-10-14 no 'expenses': read as 0)
--}}
@php
    $refundsTotal = (float) ($summary['refunds']['total'] ?? collect($summary['methods'] ?? [])->sum('refunds'));
    $expensesTotal = (float) ($summary['expenses']['total'] ?? 0);
    $expensesCount = (int) ($summary['expenses']['count'] ?? 0);
    $netTotal = (float) ($summary['net_total'] ?? ($summary['cash_total'] ?? 0));
    $noMethod = (float) ($summary['no_method_expenses'] ?? 0);
@endphp
@if (empty($summary['count']) && !$expensesCount)
    <div class="text-slate-500">No cash in or cash out.</div>
@else
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Method</th><th>Cash In</th><th>Refunds</th><th>Cash Out</th><th>Net</th></tr></thead>
            <tbody>
                @foreach ($summary['methods'] ?? [] as $method)
                    <tr>
                        <td>{{ $method['name'] }}</td>
                        <td>{{ number_format($method['payments'], 2) }}</td>
                        <td class="{{ $method['refunds'] ? 'text-danger' : '' }}">{{ $method['refunds'] ? number_format(-$method['refunds'], 2) : '0.00' }}</td>
                        <td class="{{ !empty($method['expenses']) ? 'text-danger' : '' }}">{{ !empty($method['expenses']) ? number_format(-$method['expenses'], 2) : '0.00' }}</td>
                        <td class="font-medium">{{ number_format($method['net'], 2) }}</td>
                    </tr>
                @endforeach
                {{-- the handover as a sum --}}
                <tr class="row-total">
                    <td colspan="4">Cash In</td>
                    <td>{{ number_format(($summary['cash_total'] ?? 0) + $refundsTotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4">Refunds <span class="text-slate-400">(money given back{{ !empty($summary['refunds']['count']) ? ', ' . $summary['refunds']['count'] : '' }})</span></td>
                    <td class="{{ $refundsTotal ? 'text-danger' : '' }}">{{ number_format(-$refundsTotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4">Cash Out <span class="text-slate-400">(money paid out{{ $expensesCount ? ', ' . $expensesCount : '' }}{{ $noMethod ? ', ' . number_format($noMethod, 2) . ' with no method' : '' }})</span></td>
                    <td class="{{ $expensesTotal ? 'text-danger' : '' }}">{{ number_format(-$expensesTotal, 2) }}</td>
                </tr>
                <tr class="font-medium">
                    <td colspan="4">Net to hand over</td>
                    <td class="text-success">{{ number_format($netTotal, 2) }}</td>
                </tr>
                {{-- withholding apart (not cash); hidden when Withholding tax is off and there is none (Settings › General) --}}
                @if (!empty($summary['withholding']['count']) || \Modules\PaymentModule\app\Services\PaymentService::withholdingOn())
                    <tr>
                        <td colspan="4">Withholding <span class="text-slate-400">(not cash, {{ $summary['withholding']['count'] ?? 0 }})</span></td>
                        <td>{{ number_format($summary['withholding']['total'] ?? 0, 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
@endif
