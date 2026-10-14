{{--
    Add payment fields (no edit here: the owner's edit is step 6). Every drop menu is searchable (Tom Select).
    For: a reservation / a package of the member (loaded by ajax from the member + "for", custom.js [data-options-url]
    and [data-payment-form]), or other income (an income category, the member is optional).
    [data-show-if="field=value ..."]: shown only while the select has one of the values (custom.js applyConditions).
    PaymentService::create() checks the rules again (amount > 0, a closed day, a reservation inside a package, the ownership).
--}}
@php
    $area = request()->routeIs('employee.*') ? 'employee' : 'account';
    $kind = old('payable', $payableKind);
@endphp

{{-- opened from a reservation / package view: the popup goes back to it after saving --}}
@if (request()->filled('payable_id'))
    <input type="hidden" name="back_to_payable" value="1">
@endif
{{-- opened from the employee's "My Shift" page (a missed payment): back to it after saving --}}
@if (request()->boolean('back_to_shift'))
    <input type="hidden" name="back_to_shift" value="1">
@endif

<div class="form-section">Cash In For</div>
<div class="grid grid-cols-1 gap-5 md:grid-cols-3">
    @include('layoutmodule::partials.field', ['name' => 'payable', 'label' => 'For', 'type' => 'select', 'required' => true,
        'options' => ['reservation' => 'Reservation', 'package' => 'Package', '' => 'Other Income'], 'value' => $kind])
</div>
<div class="grid grid-cols-1 gap-5 mt-5 md:grid-cols-1">
    @include('layoutmodule::partials.field', ['name' => 'member_id', 'label' => 'Member', 'type' => 'select', 'searchable' => true, 'width' => 'long',
        'options' => ['' => '- Select -'] + $members, 'value' => $payment->member_id ?: null,
        'hint' => 'Search by name or mobile. Required for a reservation / package, optional for other income.'])
</div>
<div class="grid grid-cols-1 gap-5 mt-5 md:grid-cols-1" data-show-if="payable=reservation package">
    @include('layoutmodule::partials.field', ['name' => 'payable_id', 'label' => 'Reservation / Package', 'type' => 'select', 'required' => true, 'searchable' => true, 'width' => 'xlong',
        'options' => ['' => '- Select -'] + $payableOptions, 'value' => $payment->payable_id ?: null,
        'attrs' => 'data-options-url="' . route($area . '.payments.options.payables') . '" data-parent="member_id" data-also="payable" data-none="Nothing to pay for this member"' . (empty($payableOptions) ? ' disabled' : ''),
        'hint' => 'The member\'s reservations (not inside a package: they are paid through the package) or packages, the ones still owed first.'])
</div>
<div class="grid grid-cols-1 gap-5 mt-5 md:grid-cols-3" data-show-if="payable=">
    @include('layoutmodule::partials.field', ['name' => 'income_category_id', 'label' => 'Income Category', 'type' => 'select', 'required' => true, 'searchable' => true,
        'options' => ['' => '- Select -'] + $categories, 'value' => $payment->income_category_id ?: null,
        'hint' => empty($categories) ? 'No income categories yet, add them from Settings > Income Categories.' : null])
</div>

<div class="form-section mt-8">Cash In</div>
<div class="grid grid-cols-1 gap-5 md:grid-cols-3">
    {{-- Settings › General "Withholding tax" off: payments only --}}
    @if (\Modules\PaymentModule\app\Services\PaymentService::withholdingOn())
        @include('layoutmodule::partials.field', ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'required' => true,
            'options' => ['payment' => 'Cash In', 'withholding' => 'Withholding'], 'value' => $payment->type,
            'hint' => 'Withholding: tax withheld by the customer (a certificate instead of money).'])
    @else
        <input type="hidden" name="type" value="payment">
    @endif
    @include('layoutmodule::partials.field', ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true, 'value' => $payment->amount,
        'attrs' => 'min="0.01" step="0.01" max="99999999"'])
    @include('layoutmodule::partials.field', ['name' => 'paid_at', 'label' => 'Date', 'type' => 'date', 'required' => true,
        'value' => $payment->paid_at?->format('Y-m-d')])
</div>
<div class="grid grid-cols-1 gap-5 mt-5 md:grid-cols-3">
    <div data-show-if="type=payment">
        @include('layoutmodule::partials.field', ['name' => 'payment_method_id', 'label' => 'Payment Method', 'type' => 'select', 'required' => true, 'searchable' => true,
            'options' => ['' => '- Select -'] + $methods, 'value' => $payment->payment_method_id ?: null,
            'hint' => empty($methods) ? 'No payment methods yet, add them from Settings > Payment Methods.' : null])
    </div>
    @include('layoutmodule::partials.field', ['name' => 'reference', 'label' => 'Reference', 'value' => $payment->reference,
        'hint' => 'Transfer / card reference (optional).', 'attrs' => 'maxlength="255"'])
</div>

<div class="mt-5">
    @include('layoutmodule::partials.field', ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'value' => $payment->notes])
</div>
