@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(Auth::user()->userable->layout())

@section('title')
    Expenses
@endsection

@section('actions')
    <a href="{{ route($area . '.shift.index') }}" class="btn btn-secondary"><i data-lucide="lock"></i> My Shift</a>
    <a href="{{ route($area . '.expenses.create') }}" data-modal class="btn btn-primary"><i data-lucide="plus"></i> Add Expense</a>
@endsection

@section('content')
    {{--
        Expenses (docs/payments-plan.md "Expenses"), the payments list rules: the account = tabs Today (default) | This Month | All +
        ONE date field synced with them; employees = their own expenses, no tabs, ONE day (today by default, × / Clear = today).
        Filters: search | date | Category | Method (+ "No method" = imported old method 5) | Clear. The summary line comes with the rows.
    --}}
    <form id="expenses-filters" class="filter-bar">
        @if ($area === 'account')
            @include('layoutmodule::partials.filter-tabs', ['tabs' => \Modules\PaymentModule\app\Models\Expense::TABS, 'default' => 'today', 'dates' => \Modules\PaymentModule\app\Models\Expense::tabDates()])
        @endif
        <input type="search" name="search" class="{{ config('layoutmodule.form.input') }}" placeholder="No., item or notes" autocomplete="off">
        @include('layoutmodule::partials.date-range-filter', $area === 'employee' ? ['single' => true, 'default' => today()->toDateString()] : [])
        <select name="expense_category_id" class="{{ config('layoutmodule.form.select') }}" aria-label="Category">
            <option value="">All Categories</option>
            @foreach ($categories as $id => $name)
                <option value="{{ $id }}">{{ Str::humanize($name) }}</option>
            @endforeach
        </select>
        <select name="method" class="{{ config('layoutmodule.form.select') }}" aria-label="Method">
            <option value="">All Methods</option>
            @foreach ($methods as $id => $name)
                <option value="{{ $id }}">{{ Str::humanize($name) }}</option>
            @endforeach
            <option value="none">No method</option>
        </select>
        @include('layoutmodule::partials.datatable-clear')
    </form>

    <div class="list-summary" data-html="expenses-summary"></div>

    @include('paymentmodule::Expense.partials.list-table', ['filters' => '#expenses-filters', 'showBy' => $area === 'account'])
@endsection

@include('layoutmodule::partials.datatable-assets')
