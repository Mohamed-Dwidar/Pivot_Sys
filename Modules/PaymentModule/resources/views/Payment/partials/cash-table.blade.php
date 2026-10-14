{{--
    The All / Cash Out list table (DataTables, PaymentActions::cashData()): Date | Type | Member or Category | For / Item | Method |
    Amount | By (account only, $showBy) | row menu. $filters: the filters form id, $direction: all | out (in the data URL).
--}}
@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
<div class="table-wrap mt-5">
    <table class="data-table" data-datatable data-url="{{ route($area . '.payments.cash-data', ['direction' => $direction]) }}"
        data-filters="{{ $filters }}" data-order='[[0, "desc"]]' data-empty="{{ $empty ?? 'Nothing here.' }}">
        <thead>
            <tr>
                <th data-data="at_date" data-name="at_date" data-class="whitespace-nowrap">Date</th>
                <th data-data="type">Type</th>
                <th data-data="party">Member / Category</th>
                <th data-data="what">For / Item</th>
                <th data-data="method">Method</th>
                <th data-data="amount" data-name="amount" data-class="whitespace-nowrap">Amount</th>
                @if (!empty($showBy))
                    <th data-data="by">By</th>
                @endif
                <th data-data="actions"></th>
            </tr>
        </thead>
    </table>
</div>
