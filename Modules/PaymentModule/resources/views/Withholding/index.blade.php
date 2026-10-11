@extends('layoutmodule::account.main')

@section('title')
    Withholding Certificates
@endsection

@section('actions')
    <a href="{{ route('account.payments.index') }}" class="btn btn-secondary"><i data-lucide="wallet"></i> Payments</a>
    <a href="{{ route('account.withholding-certificates.create') }}" data-modal class="btn btn-primary"><i data-lucide="plus"></i> Add Certificate</a>
@endsection

@section('content')
    <form id="withholding-filters" class="filter-bar">
        <input type="search" name="search" class="{{ config('layoutmodule.form.input') }}" placeholder="Number, payer or tax id" autocomplete="off">
        <select name="match" class="{{ config('layoutmodule.form.select') }}" aria-label="Payments">
            <option value="">All</option>
            <option value="1">Matches its payments</option>
            <option value="0">Difference</option>
        </select>
        <input type="date" name="date_from" class="{{ config('layoutmodule.form.input') }} field-short" aria-label="Issued from" title="Issued from">
        <input type="date" name="date_to" class="{{ config('layoutmodule.form.input') }} field-short" aria-label="Issued to" title="Issued to">
        @include('layoutmodule::partials.datatable-clear')
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table" data-datatable data-url="{{ route('account.withholding-certificates.data') }}"
            data-filters="#withholding-filters" data-order='[[1, "desc"]]' data-empty="No withholding certificates yet.">
            <thead>
                <tr>
                    <th data-data="number_html" data-name="certificate_no">Certificate No.</th>
                    <th data-data="issued_at" data-name="issued_at" data-class="whitespace-nowrap">Issued</th>
                    <th data-data="payer" data-name="payer_name">Payer</th>
                    <th data-data="amount" data-name="amount">Amount</th>
                    <th data-data="payments_count" data-name="payments_count">Payments</th>
                    <th data-data="difference_html">Difference</th>
                    <th data-data="actions"></th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
