@php
    $area = request()->routeIs('employee.*') ? 'employee' : 'account';
    // Settings › General "Withholding tax" off: no certificates button, no Withholding filter option
    $withholdingOn = \Modules\PaymentModule\app\Services\PaymentService::withholdingOn();
@endphp
@extends(Auth::user()->userable->layout())

@section('title')
    Payments
@endsection

@section('actions')
    @if ($area === 'employee')
        <a href="{{ route('employee.shift.index') }}" class="btn btn-secondary"><i data-lucide="lock"></i> My Shift</a>
    @else
        <a href="{{ route('account.shift.index') }}" class="btn btn-secondary"><i data-lucide="lock"></i> My Shift</a>
        <a href="{{ route('account.daily-summary.index') }}" class="btn btn-secondary"><i data-lucide="calendar-days"></i> Daily Summary</a>
    @endif
    @if ($area === 'account' && $withholdingOn)
        <a href="{{ route('account.withholding-certificates.index') }}" class="btn btn-secondary"><i data-lucide="file-badge"></i> Withholding Certificates</a>
    @endif
    <a href="{{ route($area . '.payments.create') }}" data-modal class="btn btn-primary"><i data-lucide="plus"></i> Add Payment</a>
@endsection

@section('content')
    {{--
        Toolbar (docs/payments-plan.md "Payments list", 2026-10-13): tabs Today (default, value '') | This Month | All;
        ONE row of filters: search | date (one field, synced with the tabs both ways: Today = today, This Month = the month, All = empty;
        other days = no tab highlighted) | For | Method | Clear. Employees: no tabs, the date only (one day, today by default; × / Clear = today, never all).
        For = Reservations, Packages, the "Other income" group (all, then each category); Method = each method, then Refunds / Withholding.
        The summary line (total by method, refunds, withholding) comes with the rows ([data-html]).
    --}}
    <form id="payments-filters" class="filter-bar">
        {{-- employees: no tabs, the date field only (one day, today by default, × / Clear = today) --}}
        @if ($area === 'account')
            @include('layoutmodule::partials.filter-tabs', ['tabs' => \Modules\PaymentModule\app\Models\Payment::TABS, 'default' => 'today', 'dates' => \Modules\PaymentModule\app\Models\Payment::tabDates()])
        @endif
        <input type="search" name="search" class="{{ config('layoutmodule.form.input') }}" placeholder="No., reference, member name or phone" autocomplete="off">
        @include('layoutmodule::partials.date-range-filter', $area === 'employee' ? ['single' => true, 'default' => today()->toDateString()] : [])
        <select name="for" class="{{ config('layoutmodule.form.select') }}" aria-label="For">
            <option value="">All (For)</option>
            <option value="reservation">Reservations</option>
            <option value="package">Packages</option>
            <optgroup label="Other income">
                <option value="income">All other income</option>
                @include('layoutmodule::partials.filter-options', ['options' => $categories, 'prefix' => 'category-'])
            </optgroup>
        </select>
        <select name="method" class="{{ config('layoutmodule.form.select') }}" aria-label="Method">
            <option value="">All Methods</option>
            @include('layoutmodule::partials.filter-options', ['options' => $methods])
            <option value="refund">Refunds</option>
            @if ($withholdingOn)
                <option value="withholding">Withholding</option>
            @endif
        </select>
        @include('layoutmodule::partials.datatable-clear')
    </form>

    <div class="list-summary" data-html="payments-summary"></div>

    {{-- employees see only their own payments: no Created By --}}
    @include('paymentmodule::Payment.partials.list-table', ['filters' => '#payments-filters', 'showBy' => $area === 'account'])
@endsection

@include('layoutmodule::partials.datatable-assets')
