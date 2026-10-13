@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(Auth::user()->userable->layout())

{{--
    Subscriptions (docs/reservations-list-plan.md): the auto renew subscription types' reservations, for follow-up / renewal.
    Tabs: Active Now (default, value '') / Ending in 7 Days / Ending in 30 Days (not renewed) / Ended, Not Renewed
    (the last 60 days, or the end date range). Renewed = the member has a later subscription in the same space.
--}}
@section('title')
    Subscriptions
@endsection

@section('actions')
    <a href="{{ route($area . '.reservations.index') }}" class="btn btn-secondary"><i data-lucide="calendar-check"></i> Reservations</a>
@endsection

@section('content')
    <form id="subscriptions-filters" class="filter-bar">
        <input type="hidden" name="tab" value="">
        <div class="status-tabs">
            @foreach (\Modules\ReservationModule\app\Models\Reservation::SUBSCRIPTION_TABS as $value => $label)
                <button type="button" data-filter-tab data-name="tab" data-value="{{ $value === 'active' ? '' : $value }}" class="{{ $value === 'active' ? 'active' : '' }}">
                    {{ $label }} (<span data-counter="subscriptions-{{ $value }}">{{ $counts['subscriptions-' . $value] }}</span>)
                </button>
            @endforeach
        </div>
        <input type="search" name="search" class="{{ config('layoutmodule.form.input') }}" placeholder="Member name or phone" autocomplete="off">
        <select name="space_id" class="{{ config('layoutmodule.form.select') }}" aria-label="Space">
            <option value="">All Spaces</option>
            @foreach ($spaces as $id => $name)
                <option value="{{ $id }}">{{ Str::humanize($name) }}</option>
            @endforeach
        </select>
        {{-- "Ended, Not Renewed" only: the end date range (default: the last {{ \Modules\ReservationModule\app\Models\Reservation::ENDED_DAYS }} days) --}}
        <input type="date" name="date_from" class="{{ config('layoutmodule.form.input') }} field-short" aria-label="Ended from" title="Ended from (Ended, Not Renewed)">
        <input type="date" name="date_to" class="{{ config('layoutmodule.form.input') }} field-short" aria-label="Ended to" title="Ended to (Ended, Not Renewed)">
        @include('layoutmodule::partials.datatable-clear')
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table" data-datatable data-url="{{ route($area . '.subscriptions.data') }}"
            data-filters="#subscriptions-filters" data-order='[]' data-empty="No subscriptions here.">
            <thead>
                <tr>
                    <th data-data="member_html">Member</th>
                    <th data-data="place">Space &rsaquo; Unit</th>
                    <th data-data="plan">Plan</th>
                    <th data-data="start" data-name="start_at" data-class="whitespace-nowrap">Start</th>
                    <th data-data="end" data-name="end_at" data-class="whitespace-nowrap">End</th>
                    <th data-data="days" data-class="whitespace-nowrap">Days</th>
                    <th data-data="net_amount" data-name="net_amount">Net Amount</th>
                    <th data-data="remaining_html" data-name="remaining">Remaining</th>
                    <th data-data="renewed_html">Renewed</th>
                    <th data-data="actions"></th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
