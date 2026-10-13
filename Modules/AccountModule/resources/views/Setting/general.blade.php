@extends('layoutmodule::account.main')

{{--
    Settings › General (the account only, docs/payments-plan.md): two switches stored on the account.
    Withholding tax OFF: no new withholding anywhere (payment type, Method filter, certificates page + menu); existing withholding
    records stay and are still shown. VAT: for Phase 2 (OFF = prices tax free); nothing else changes now.
--}}
@section('title')
    General Settings
@endsection

@section('content')
    <form method="POST" action="{{ route('account.settings.general.update') }}" data-ajax
        data-confirm="The settings apply to you and all your employees. No payment or certificate is changed."
        data-confirm-title="Save the settings?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')

        <div class="form-section">Taxes</div>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            @include('layoutmodule::partials.checkbox', ['name' => 'withholding_enabled', 'label' => 'Withholding tax', 'checked' => $account->withholdingEnabled(),
                'hint' => 'Off: "Withholding" is no longer offered when adding payments or in the filters, and the Withholding Certificates page is hidden. Existing withholding records stay and are still shown. Turning it on again restores everything.'])
            @include('layoutmodule::partials.checkbox', ['name' => 'vat_enabled', 'label' => 'VAT', 'checked' => $account->vatEnabled(),
                'hint' => 'For invoices (coming next): off = all prices are tax free, no tax rates or tax lines. Nothing changes today.'])
        </div>

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.dashboard')])
    </form>
@endsection
