@php
    $area = request()->routeIs('employee.*') ? 'employee' : 'account';
    $employee ??= null;
@endphp
@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

{{--
    The confirmation of "Close My Shift" (the app popup, not a browser confirm): the totals to hand over.
    The account: the same for an employee ($employee) = closed on their behalf (logged), ?date= = back to that day screen.
    up_to_id = the highest payment shown: a payment added meanwhile → refused (ShiftClosingService::close()).
    $logout: opened from the logout popup → logged out after closing (custom.js, json "logout").
--}}
@section('title')
    {{ $employee ? 'Close the Shift of ' . $employee->userable->displayName() . '?' : 'Close My Shift?' }}
@endsection

@section('content')
    @if ($period['payments']->isEmpty())
        <div class="text-slate-500">No payments since the last closing, nothing to close.</div>
    @else
        <div class="mb-4">
            {{ $period['summary']['count'] }} payments since {{ $period['since']?->format('d-m-Y h:i A') }}.
            After closing they are locked; only the account can reopen the shift.
            @if ($employee)
                It is logged as closed by you on their behalf.
            @endif
        </div>

        @include('paymentmodule::Shift.partials.totals', ['summary' => $period['summary']])

        <form method="POST" action="{{ route($area === 'account' ? 'account.shifts.close' : 'employee.shift.close') }}" data-ajax>
            @csrf
            <input type="hidden" name="up_to_id" value="{{ $period['summary']['max_id'] }}">
            @if ($employee)
                <input type="hidden" name="user_id" value="{{ $employee->id }}">
            @endif
            @include('paymentmodule::Shift.partials.back')
            @if ($logout)
                <input type="hidden" name="logout" value="1">
            @endif
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i data-lucide="lock"></i> Confirm Closing</button>
                @if (request()->ajax())
                    <button type="button" data-tw-dismiss="modal" class="btn btn-secondary">Cancel</button>
                @else
                    <a href="{{ $area === 'account' ? (request()->boolean('index') ? route('account.day-closings.index') : route('account.day-closings.create', ['date' => request('date')])) : route('employee.shift.index') }}" class="btn btn-secondary">Cancel</a>
                @endif
            </div>
        </form>
    @endif
@endsection
