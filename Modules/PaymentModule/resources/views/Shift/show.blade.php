@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

{{-- a closed shift as it was saved: totals, its payments, its history (shift_events) --}}
@section('title')
    Shift of {{ $closing->user?->userable?->displayName() ?? '-' }}
    <span class="ml-2">@include('paymentmodule::DayClosing.partials.status')</span>
@endsection

@section('modal-size', 'xl')

{{-- the account: reopen a closed shift (reason) / close a reopened one again (totals recalculated); ?date= = back to that day screen --}}
@section('actions')
    @if ($area === 'account' && $closing->status === 'closed')
        <a href="{{ route('account.shifts.reopen', [$closing->id] + request()->only('date', 'index')) }}" data-modal class="btn btn-secondary"><i data-lucide="unlock"></i> Reopen</a>
    @elseif ($area === 'account')
        <form method="POST" action="{{ route('account.shifts.reclose', $closing->id) }}" data-ajax
            data-confirm="Its totals are calculated again from its payments now, and they are locked again."
            data-confirm-title="Close this shift again?"
            data-confirm-button="Close Again"
            data-confirm-variant="success">
            @csrf
            @include('paymentmodule::Shift.partials.back')
            <button type="submit" class="btn btn-primary"><i data-lucide="lock"></i> Close Again</button>
        </form>
    @endif
@endsection

@section('content')
    <dl class="detail-list">
        <div><dt>Closed</dt><dd>{{ $closing->closed_at?->format('d-m-Y h:i A') }} · {{ $closing->closedBy?->userable?->displayName() ?? '-' }}</dd></div>
        <div><dt>Payments From → To</dt><dd>@include('paymentmodule::Shift.partials.period')</dd></div>
        @if ($closing->notes)
            <div><dt>Notes</dt><dd>{{ $closing->notes }}</dd></div>
        @endif
        @if ($closing->status === 'reopened')
            <div class="wide"><dt>Reopened</dt><dd class="text-warning">{{ $closing->reopened_at?->format('d-m-Y h:i A') }} · {{ $closing->reopenedBy?->userable?->displayName() ?? '-' }} — {{ $closing->reopen_reason }}</dd></div>
        @endif
    </dl>

    <div class="form-section mt-8">Totals</div>
    @include('paymentmodule::Shift.partials.totals', ['summary' => $closing->totals ?? []])

    <div class="form-section mt-8">Payments</div>
    @include('paymentmodule::Shift.partials.payments', ['payments' => $closing->payments])

    {{-- every close / reopen / re-close, newest first --}}
    <div class="form-section mt-8">History</div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>When</th><th>Action</th><th>By</th><th>Collected</th><th>Reason</th></tr></thead>
            <tbody>
                @foreach ($closing->events as $event)
                    <tr>
                        <td class="whitespace-nowrap">{{ $event->created_at?->format('d-m-Y h:i A') }}</td>
                        <td><span class="badge {{ $event->action === 'reopen' ? 'badge-status-warning' : 'badge-status-success' }}">{{ $event->action_label }}</span></td>
                        <td>{{ $event->byUser?->userable?->displayName() ?? '-' }}</td>
                        <td>{{ isset($event->totals['cash_total']) ? number_format($event->totals['cash_total'], 2) : '-' }}</td>
                        <td>{{ $event->reason ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
