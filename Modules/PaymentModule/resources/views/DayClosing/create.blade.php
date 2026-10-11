@php
    $dateText = $date->format(\Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT);
    $dateValue = $date->format('Y-m-d');
    $reopenedShifts = $shifts->where('status', 'reopened');
    $blocked = $openPeriods->isNotEmpty() || $reopenedShifts->isNotEmpty();
@endphp
@extends('layoutmodule::account.main')

{{--
    The account's day screen (docs/payments-plan.md §2b), a full page: the date picker reloads it with ?date=.
    The employees' shifts holding payments of that day (open → "Close on behalf", closed / reopened → view, reopen, close again),
    the day's totals, "Close this Day" (the confirm popup) once every one of those shifts is closed.
--}}
@section('title')
    Close the Day {{ $dateText }}
@endsection

@section('actions')
    <form method="GET" action="{{ route('account.day-closings.create') }}" class="filter-bar">
        <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" max="{{ today()->format('Y-m-d') }}" class="{{ config('layoutmodule.form.input') }} field-short" aria-label="Day">
        <button type="submit" class="btn btn-secondary"><i data-lucide="calendar-search"></i> Show</button>
    </form>
    <a href="{{ route('account.day-closings.index') }}" class="btn btn-secondary"><i data-lucide="list"></i> Closed Days</a>
@endsection

@section('content')
    @if ($closing?->status === 'closed')
        <div class="mb-5">
            This day is <a href="{{ route('account.day-closings.show', $closing->id) }}" data-modal class="font-medium">closed</a>, its payments are locked.
        </div>
    @elseif ($closing)
        <div class="mb-5 text-warning">This day was reopened ({{ $closing->reopen_reason }}). Close it again when its payments are checked.</div>
    @endif

    {{-- the employees' shifts holding payments of this day --}}
    <div class="form-section">Employee Shifts</div>
    @if ($openPeriods->isEmpty() && $shifts->isEmpty())
        <div class="text-slate-500">No employee payments on this day.</div>
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Employee</th><th>Status</th><th>Payments From → To</th><th>Payments</th><th>Collected</th><th>Withholding</th><th></th></tr></thead>
                <tbody>
                    @foreach ($openPeriods as $userId => $period)
                        <tr>
                            <td>{{ $period['user']?->userable?->displayName() ?? 'User #' . $userId }}</td>
                            <td><span class="badge badge-status-warning">Open</span></td>
                            <td class="whitespace-nowrap">{{ $period['since']->format('d-m-Y h:i A') }} → now</td>
                            <td>{{ $period['count'] }} <span class="text-xs text-slate-500">({{ $period['dated_count'] }} on this day)</span></td>
                            <td>{{ number_format($period['cash_total'], 2) }}</td>
                            <td>{{ number_format($period['withholding_total'], 2) }}</td>
                            <td><a href="{{ route('account.shifts.confirm', ['user_id' => $userId, 'date' => $dateValue]) }}" data-modal class="btn btn-secondary btn-sm"><i data-lucide="lock"></i> Close on Behalf</a></td>
                        </tr>
                    @endforeach
                    @foreach ($shifts as $shift)
                        <tr>
                            <td>{{ $shift->user?->userable?->displayName() ?? 'User #' . $shift->user_id }}</td>
                            <td>@include('paymentmodule::DayClosing.partials.status', ['closing' => $shift])</td>
                            <td>@include('paymentmodule::Shift.partials.period', ['closing' => $shift])</td>
                            <td>{{ $shift->count }}</td>
                            <td>{{ number_format($shift->cash_total, 2) }}</td>
                            <td>{{ number_format($shift->withholding_total, 2) }}</td>
                            <td><a href="{{ route('account.shifts.show', [$shift->id, 'date' => $dateValue]) }}" data-modal class="btn btn-secondary btn-sm"><i data-lucide="eye"></i> View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-2 text-xs text-slate-500">A shift's totals are for the whole shift (it can hold payments of other days); the day totals below are for this day only.</div>
    @endif

    {{-- the totals now (saved when the day is closed) --}}
    <div class="form-section mt-8">Day Totals</div>
    @include('paymentmodule::DayClosing.partials.totals')

    @unless ($closing?->status === 'closed')
        <div class="form-actions">
            @if ($blocked)
                <span class="text-warning">Close the open / reopened shifts above before closing the day.</span>
            @else
                <a href="{{ route('account.day-closings.confirm', ['date' => $dateValue]) }}" data-modal class="btn btn-primary"><i data-lucide="lock"></i> Close this Day</a>
            @endif
        </div>
    @endunless
@endsection
