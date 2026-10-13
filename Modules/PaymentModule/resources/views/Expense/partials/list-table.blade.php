{{--
    The expenses list table (DataTables, ExpenseActions::table()), shared by the Expenses page and My Shift:
    Expense | Category | Date | Method | Amount | Created By (account only, $showBy) | row menu.
    $filters: the filters form id (My Shift: a hidden form with shift=open), $empty: the empty text.
--}}
@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
<div class="table-wrap mt-5">
    <table class="data-table" data-datatable data-url="{{ route($area . '.expenses.data') }}"
        data-filters="{{ $filters }}" data-order='[[2, "desc"]]' data-empty="{{ $empty ?? 'No expenses here.' }}">
        <thead>
            <tr>
                <th data-data="item_html">Expense</th>
                <th data-data="category">Category</th>
                <th data-data="spent_at" data-name="spent_at" data-class="whitespace-nowrap">Date</th>
                <th data-data="method">Method</th>
                <th data-data="amount" data-name="amount" data-class="whitespace-nowrap">Amount</th>
                @if (!empty($showBy))
                    <th data-data="by">Created By</th>
                @endif
                <th data-data="actions"></th>
            </tr>
        </thead>
    </table>
</div>
