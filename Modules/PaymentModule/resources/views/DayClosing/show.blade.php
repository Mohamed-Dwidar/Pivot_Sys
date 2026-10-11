@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

@section('title')
    Day {{ $closing->date->format(\Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT) }}
    <span class="ml-2">@include('paymentmodule::DayClosing.partials.status')</span>
@endsection

@section('modal-size', 'lg')

@section('actions')
    @if ($closing->status === 'closed' && $area === 'account')
        <a href="{{ route('account.day-closings.reopen', $closing->id) }}" data-modal class="btn btn-secondary"><i data-lucide="lock-open"></i> Reopen</a>
    @elseif ($closing->status !== 'closed')
        <a href="{{ route($area . '.day-closings.create', ['date' => $closing->date->format('Y-m-d')]) }}" class="btn btn-primary"><i data-lucide="lock"></i> Close Again</a>
    @endif
@endsection

@section('content')
    <dl class="detail-list">
        <div><dt>Last Closed By</dt><dd>{{ $closing->closedBy?->userable?->displayName() ?? '-' }} · {{ $closing->closed_at?->format('d-m-Y h:i A') }}</dd></div>
    </dl>

    {{-- the totals saved when it was last closed --}}
    <div class="mt-6">
        @include('paymentmodule::DayClosing.partials.totals')
    </div>

    {{-- every close / reopen (day_closing_logs), newest first --}}
    <div class="form-section mt-8">History</div>
    @if ($closing->logs->isNotEmpty())
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>When</th><th>Action</th><th>By</th><th>Cash Total</th><th>Withholding</th><th>Reason</th></tr></thead>
                <tbody>
                    @foreach ($closing->logs as $log)
                        <tr>
                            <td class="whitespace-nowrap">{{ $log->created_at?->format('d-m-Y h:i A') }}</td>
                            <td><span class="badge {{ $log->action === 'close' ? 'badge-status-success' : 'badge-status-warning' }}">{{ \Modules\PaymentModule\app\Models\DayClosingLog::ACTIONS[$log->action] }}</span></td>
                            <td>{{ $log->user?->userable?->displayName() ?? '-' }}</td>
                            <td>{{ number_format($log->cash_total, 2) }}</td>
                            <td>{{ number_format($log->withholding_total, 2) }}</td>
                            <td>{{ $log->reason ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-slate-500">No history (closed before the history was kept).</div>
    @endif
@endsection
