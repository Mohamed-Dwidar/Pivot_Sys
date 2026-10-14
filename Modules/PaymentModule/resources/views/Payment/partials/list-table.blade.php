{{--
    The payments list table (DataTables, PaymentActions::table()), shared by the Payments page and My Shift:
    Member | For | Date | Method | Amount | Created By (account only, $showBy) | row menu.
    $filters: the filters form id ("#payments-filters"; My Shift: a hidden form with shift=open), $empty: the empty text,
    $dataParams: query of the data URL (the Payments page Cash In tab: direction=in = no refunds).
--}}
@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
<div class="table-wrap mt-5">
    <table class="data-table" data-datatable data-url="{{ route($area . '.payments.data', $dataParams ?? []) }}"
        data-filters="{{ $filters }}" data-order='[[2, "desc"]]' data-empty="{{ $empty ?? 'No cash in here.' }}">
        <thead>
            <tr>
                <th data-data="member_html">Member</th>
                <th data-data="source">For</th>
                <th data-data="paid_at" data-name="paid_at" data-class="whitespace-nowrap">Date</th>
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
