{{--
    The totals of a shift (ShiftClosingService::summary(), also saved on the closing): by payment method
    (payments - refunds = net), refunds as their own minus line, withholding apart (not cash), the total collected = what is handed over.
    $summary: ['methods', 'refunds', 'withholding', 'cash_total', 'count'] (closings saved before 2026-10-13 have no 'refunds')
--}}
@if (empty($summary['count']))
    <div class="text-slate-500">No payments.</div>
@else
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Method</th><th>Payments</th><th>Refunds</th><th>Net</th></tr></thead>
            <tbody>
                @foreach ($summary['methods'] ?? [] as $method)
                    <tr>
                        <td>{{ $method['name'] }}</td>
                        <td>{{ number_format($method['payments'], 2) }}</td>
                        <td class="{{ $method['refunds'] ? 'text-danger' : '' }}">{{ $method['refunds'] ? number_format(-$method['refunds'], 2) : '0.00' }}</td>
                        <td>{{ number_format($method['net'], 2) }}</td>
                    </tr>
                @endforeach
                {{-- the handover as a sum: all payments, the refunds as their own minus line, = total collected --}}
                @php($refundsTotal = (float) ($summary['refunds']['total'] ?? collect($summary['methods'] ?? [])->sum('refunds')))
                <tr class="row-total">
                    <td colspan="3">Total payments</td>
                    <td>{{ number_format($summary['cash_total'] + $refundsTotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3">Refunds <span class="text-slate-400">(money given back{{ !empty($summary['refunds']['count']) ? ', ' . $summary['refunds']['count'] : '' }})</span></td>
                    <td class="{{ $refundsTotal ? 'text-danger' : '' }}">{{ number_format(-$refundsTotal, 2) }}</td>
                </tr>
                <tr class="font-medium">
                    <td colspan="3">Total collected</td>
                    <td class="text-success">{{ number_format($summary['cash_total'], 2) }}</td>
                </tr>
                {{-- withholding apart (not cash); hidden when Withholding tax is off and there is none (Settings › General) --}}
                @if (!empty($summary['withholding']['count']) || \Modules\PaymentModule\app\Services\PaymentService::withholdingOn())
                    <tr>
                        <td colspan="3">Withholding <span class="text-slate-400">(not cash, {{ $summary['withholding']['count'] ?? 0 }})</span></td>
                        <td>{{ number_format($summary['withholding']['total'] ?? 0, 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
@endif
