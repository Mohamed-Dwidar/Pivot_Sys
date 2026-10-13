{{--
    A day's totals (DailySummaryService::summary()): per collector × method (payments - refunds = net, the cash handed over),
    per method, refunds as their own minus line, withholding apart (not cash).
    $summary: ['collectors', 'methods', 'refunds', 'withholding', 'cash_total', 'count']
--}}
@php
    $methods = $summary['methods'] ?? [];
    $methodName = fn ($id) => $methods[$id]['name'] ?? 'Method #' . $id;
@endphp

@if (empty($summary['count']))
    <div class="text-slate-500">No payments on this day.</div>
@else
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Method</th><th>Payments</th><th>Refunds</th><th>Net</th></tr></thead>
            <tbody>
                @foreach ($methods as $method)
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
                        <td colspan="3">Withholding <span class="text-slate-400">(not cash, {{ $summary['withholding']['count'] }})</span></td>
                        <td>{{ number_format($summary['withholding']['total'], 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="form-section mt-8">By Collector</div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Collected By</th><th>Method</th><th>Payments</th><th>Refunds</th><th>Net</th></tr></thead>
            <tbody>
                @foreach ($summary['collectors'] as $collector)
                    @foreach ($collector['methods'] as $methodId => $line)
                        <tr>
                            <td>{{ $loop->first ? $collector['name'] : '' }}</td>
                            <td>{{ $methodName($methodId) }}</td>
                            <td>{{ number_format($line['payments'], 2) }}</td>
                            <td class="{{ $line['refunds'] ? 'text-danger' : '' }}">{{ $line['refunds'] ? number_format(-$line['refunds'], 2) : '0.00' }}</td>
                            <td>{{ number_format($line['net'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="font-medium">
                        <td colspan="4">{{ $collector['name'] }} — net</td>
                        <td>{{ number_format($collector['net'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
