@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

{{--
    A closed shift (popup): its totals (recalculated when the account edits / deletes one of its payments), its payments in the
    Payments list columns (row menu included). No history here (user 2026-10-13; the changes stay logged in shift_events).
    No reopen (docs/payments-plan.md §2b): the account changes a payment directly from its row menu, with a reason.
--}}
@section('title')
    Shift of {{ $closing->user?->userable?->displayName() ?? '-' }}
    <span class="ml-2">@include('paymentmodule::Shift.partials.status')</span>
@endsection

@section('modal-size', 'xl')

@section('content')
    <dl class="detail-list">
        <div><dt>Closed</dt><dd>{{ $closing->closed_at?->format('d-m-Y h:i A') }} · {{ $closing->closedBy?->userable?->displayName() ?? '-' }}</dd></div>
        <div><dt>Payments From → To</dt><dd>@include('paymentmodule::Shift.partials.period')</dd></div>
        @if ($closing->notes)
            <div><dt>Notes</dt><dd>{{ $closing->notes }}</dd></div>
        @endif
        @if ($closing->isEdited())
            <div><dt>Edited After Closing</dt><dd class="text-warning">{{ $closing->edit_count }} change(s), last {{ $closing->edited_at?->format('d-m-Y h:i A') }}</dd></div>
        @endif
    </dl>

    <div class="form-section mt-8">Totals</div>
    @include('paymentmodule::Shift.partials.totals', ['summary' => $closing->totals ?? []])

    <div class="form-section mt-8">Payments</div>
    @include('paymentmodule::Shift.partials.payments', ['payments' => $closing->payments])
@endsection
