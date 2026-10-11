{{--
    A day's totals (DayClosingService::summary(), also saved on the closing): by who added the payments × method
    (payments - refunds = net, the cash handover), the methods totals, withholding apart (not cash).
    $summary: ['employees', 'methods', 'withholding', 'cash_total', 'count']
--}}
@php
    $employees = $summary['employees'] ?? [];
    $methods = $summary['methods'] ?? [];
    $methodName = fn ($id) => $methods[$id]['name'] ?? 'Method #' . $id;
@endphp

@if (empty($summary['count']))
    <div class="text-slate-500">No payments on this day.</div>
@else
    <div class="form-section">By Employee</div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Added By</th><th>Method</th><th>Payments</th><th>Refunds</th><th>Net</th></tr></thead>
            <tbody>
                @foreach ($employees as $employee)
                    @foreach ($employee['methods'] as $methodId => $line)
                        <tr>
                            <td>{{ $loop->first ? $employee['name'] : '' }}</td>
                            <td>{{ $methodName($methodId) }}</td>
                            <td>{{ number_format($line['payments'], 2) }}</td>
                            <td class="{{ $line['refunds'] ? 'text-danger' : '' }}">{{ number_format($line['refunds'], 2) }}</td>
                            <td>{{ number_format($line['net'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="font-medium">
                        <td colspan="4">{{ $employee['name'] }} — to hand over</td>
                        <td>{{ number_format($employee['net'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="form-section mt-8">Totals</div>
    <dl class="detail-list">
        @foreach ($methods as $method)
            <div><dt>{{ $method['name'] }}</dt><dd>{{ number_format($method['net'], 2) }}</dd></div>
        @endforeach
        <div><dt>Cash Total <span class="text-slate-400">(payments - refunds)</span></dt><dd class="font-medium text-success">{{ number_format($summary['cash_total'], 2) }}</dd></div>
        <div><dt>Withholding <span class="text-slate-400">(not cash)</span></dt><dd>{{ number_format($summary['withholding']['total'] ?? 0, 2) }} ({{ $summary['withholding']['count'] ?? 0 }})</dd></div>
    </dl>
@endif
