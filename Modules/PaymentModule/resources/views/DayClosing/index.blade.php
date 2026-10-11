@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(Auth::user()->userable->layout())

@section('title')
    Day Closing
@endsection

@section('actions')
    <a href="{{ route($area . '.payments.index') }}" class="btn btn-secondary"><i data-lucide="wallet"></i> Payments</a>
    <a href="{{ route($area . '.day-closings.create') }}" class="btn btn-primary"><i data-lucide="lock"></i> Close a Day</a>
@endsection

@section('content')
    {{-- flags (docs/payments-plan.md §2b): shifts open longer than the reminder setting, with the "still working" answers --}}
    @if ($longOpen->isNotEmpty())
        <div class="form-section">Long Open Shifts</div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Employee</th><th>Open Since</th><th>Payments</th><th>Collected</th><th>"Still Working"</th><th></th></tr></thead>
                <tbody>
                    @foreach ($longOpen as $userId => $period)
                        <tr>
                            <td>{{ $period['user']?->userable?->displayName() ?? 'User #' . $userId }}</td>
                            <td class="whitespace-nowrap">{{ $period['since']->format('d-m-Y h:i A') }} <span class="text-xs text-slate-500">({{ $period['since']->diffForHumans(null, true) }})</span></td>
                            <td>{{ $period['count'] }}</td>
                            <td>{{ number_format($period['cash_total'], 2) }}</td>
                            <td>
                                {{ $period['answers']->count() }}
                                @foreach ($period['answers'] as $answer)
                                    <div class="text-xs text-slate-500">{{ $answer->created_at->format('d-m-Y h:i A') }}: {{ $answer->reason ?: \Modules\PaymentModule\app\Models\ShiftEvent::ACTIONS[$answer->action] }}</div>
                                @endforeach
                            </td>
                            <td><a href="{{ route('account.shifts.confirm', ['user_id' => $userId, 'index' => 1]) }}" data-modal class="btn btn-secondary btn-sm"><i data-lucide="lock"></i> Close on Behalf</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($reopenedShifts->isNotEmpty())
        <div class="form-section {{ $longOpen->isNotEmpty() ? 'mt-8' : '' }}">Reopened Shifts</div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Employee</th><th>Reopened</th><th>Reason</th><th>Payments From → To</th><th></th></tr></thead>
                <tbody>
                    @foreach ($reopenedShifts as $shift)
                        <tr>
                            <td>{{ $shift->user?->userable?->displayName() ?? 'User #' . $shift->user_id }}</td>
                            <td class="whitespace-nowrap">{{ $shift->reopened_at?->format('d-m-Y h:i A') }}</td>
                            <td>{{ $shift->reopen_reason }}</td>
                            <td>@include('paymentmodule::Shift.partials.period', ['closing' => $shift])</td>
                            <td><a href="{{ route('account.shifts.show', [$shift->id, 'index' => 1]) }}" data-modal class="btn btn-secondary btn-sm"><i data-lucide="eye"></i> View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($longOpen->isNotEmpty() || $reopenedShifts->isNotEmpty())
        <div class="form-section mt-8">Closed Days</div>
    @endif

    <form id="day-closings-filters" class="filter-bar">
        <select name="status" class="{{ config('layoutmodule.form.select') }}" aria-label="Status">
            <option value="">All Statuses</option>
            <option value="closed">Closed</option>
            <option value="reopened">Reopened</option>
        </select>
        @include('layoutmodule::partials.datatable-clear')
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table" data-datatable data-url="{{ route($area . '.day-closings.data') }}"
            data-filters="#day-closings-filters" data-order='[[0, "desc"]]' data-empty="No closed days yet.">
            <thead>
                <tr>
                    <th data-data="date_html" data-name="date">Day</th>
                    <th data-data="status_html" data-name="status">Status</th>
                    <th data-data="cash_total" data-name="cash_total">Cash Total</th>
                    <th data-data="withholding_total" data-name="withholding_total">Withholding</th>
                    <th data-data="closed" data-name="closed_at">Closed By</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
