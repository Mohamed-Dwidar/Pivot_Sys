{{--
    The totals of a shift (ShiftClosingService::summary(), also saved on the closing): by payment method
    (payments - refunds = net), withholding apart (not cash), the total collected = what is handed over.
    $summary: ['methods', 'withholding', 'cash_total', 'count']
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
                <tr class="font-medium">
                    <td colspan="3">Total collected</td>
                    <td class="text-success">{{ number_format($summary['cash_total'], 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3">Withholding <span class="text-slate-400">(not cash, {{ $summary['withholding']['count'] ?? 0 }})</span></td>
                    <td>{{ number_format($summary['withholding']['total'] ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endif
