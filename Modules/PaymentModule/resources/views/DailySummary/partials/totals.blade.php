{{--
    A day's totals (DailySummaryService::summary(), the shift totals shape): per method payments - refunds - expenses = net
    (the shift totals table: Total payments, Refunds, Total collected, Expenses, Net to hand over, withholding apart), then the
    same per collector × method. $summary: ['collectors', 'methods', 'refunds', 'expenses', 'withholding', 'cash_total', 'net_total', 'count']
--}}
@php
    $methods = $summary['methods'] ?? [];
    $methodName = fn ($id) => $methods[$id]['name'] ?? 'Method #' . $id;
@endphp

@include('paymentmodule::Shift.partials.totals')

@if (!empty($summary['collectors']))
    <div class="form-section mt-8">By Collector</div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Collected By</th><th>Method</th><th>Payments</th><th>Refunds</th><th>Expenses</th><th>Net</th></tr></thead>
            <tbody>
                @foreach ($summary['collectors'] as $collector)
                    @foreach ($collector['methods'] as $methodId => $line)
                        <tr>
                            <td>{{ $loop->first ? $collector['name'] : '' }}</td>
                            <td>{{ $methodName($methodId) }}</td>
                            <td>{{ number_format($line['payments'], 2) }}</td>
                            <td class="{{ $line['refunds'] ? 'text-danger' : '' }}">{{ $line['refunds'] ? number_format(-$line['refunds'], 2) : '0.00' }}</td>
                            <td class="{{ $line['expenses'] ? 'text-danger' : '' }}">{{ $line['expenses'] ? number_format(-$line['expenses'], 2) : '0.00' }}</td>
                            <td>{{ number_format($line['net'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="font-medium">
                        <td colspan="5">{{ $collector['name'] }} — net</td>
                        <td>{{ number_format($collector['net'] ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
