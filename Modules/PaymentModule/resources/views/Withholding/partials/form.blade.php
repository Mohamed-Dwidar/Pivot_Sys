{{--
    Withholding certificate create / edit fields. $certificate is null on create.
    The payer: a company or a member (optional, for the reports) + the name / tax id as written on the certificate.
    payments[]: the withholding payments it covers (the free ones + its own); the difference is shown, not blocking.
    The attachment (PDF / image) is a private file.
--}}
@php($selectedPayments = array_map('intval', old('payments', $certificate ? $certificate->payments->pluck('id')->all() : [])))

<div class="form-section">Certificate</div>
<div class="grid grid-cols-1 gap-5 md:grid-cols-3">
    @include('layoutmodule::partials.field', ['name' => 'certificate_no', 'label' => 'Certificate No.', 'required' => true, 'value' => $certificate?->certificate_no, 'attrs' => 'maxlength="100"'])
    @include('layoutmodule::partials.field', ['name' => 'issued_at', 'label' => 'Issue Date', 'type' => 'date', 'required' => true,
        'value' => $certificate?->issued_at?->format('Y-m-d') ?? today()->format('Y-m-d')])
    @include('layoutmodule::partials.field', ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true, 'value' => $certificate?->amount,
        'attrs' => 'min="0.01" step="0.01" max="99999999"', 'hint' => 'The tax withheld, as written on the certificate.'])
    @include('layoutmodule::partials.field', ['name' => 'period_from', 'label' => 'Period From', 'type' => 'date', 'value' => $certificate?->period_from?->format('Y-m-d')])
    @include('layoutmodule::partials.field', ['name' => 'period_to', 'label' => 'Period To', 'type' => 'date', 'value' => $certificate?->period_to?->format('Y-m-d')])
</div>

<div class="form-section mt-8">Payer</div>
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    @include('layoutmodule::partials.field', ['name' => 'payer_name', 'label' => 'Payer Name', 'required' => true, 'value' => $certificate?->payer_name, 'attrs' => 'maxlength="255"'])
    @include('layoutmodule::partials.field', ['name' => 'payer_tax_id', 'label' => 'Payer Tax ID', 'value' => $certificate?->payer_tax_id, 'attrs' => 'maxlength="30"'])
    @include('layoutmodule::partials.field', ['name' => 'company_id', 'label' => 'Company', 'type' => 'select', 'searchable' => true,
        'options' => ['' => '- None -'] + $companies, 'value' => $certificate?->company_id ?: null])
    {{-- server search + quick add (membermodule::partials.member-select) --}}
    @include('membermodule::partials.member-select', ['member' => $certificate?->member_id ? $certificate->member : null, 'placeholder' => '- None -',
        'width' => 'medium', 'companies' => $companies])
</div>

<div class="form-section mt-8">Withholding Entries</div>
<div data-field="payments">
    @if ($paymentOptions)
        <div class="checkbox-list">
            @foreach ($paymentOptions as $paymentId => $paymentName)
                <div class="flex items-center">
                    <input type="checkbox" name="payments[]" id="payment-{{ $paymentId }}" value="{{ $paymentId }}" class="{{ config('layoutmodule.form.checkbox') }}"
                        @checked(in_array($paymentId, $selectedPayments))>
                    <label for="payment-{{ $paymentId }}" class="{{ config('layoutmodule.form.checkbox_label') }}">{{ $paymentName }}</label>
                </div>
            @endforeach
        </div>
        <div class="{{ config('layoutmodule.form.hint') }}">The withholding entries this certificate covers (not on another certificate). Their total should equal the amount.</div>
    @else
        <div class="text-slate-500">No withholding entries without a certificate. Add them from Payments › Cash In (type Withholding).</div>
    @endif
    @foreach (['payments', 'payments.*'] as $errorKey)
        @foreach ($errors->get($errorKey) as $messages)
            @foreach ((array) $messages as $message)
                <div class="{{ config('layoutmodule.form.error') }}">{{ $message }}</div>
            @endforeach
        @endforeach
    @endforeach
</div>

<div class="form-section mt-8">Attachment</div>
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div data-field="attachment">
        <label for="field-attachment" class="{{ config('layoutmodule.form.label') }}">Certificate File</label>
        <input type="file" name="attachment" id="field-attachment" accept=".pdf,.jpg,.jpeg,.png,.webp" class="{{ config('layoutmodule.form.input') }} field-long">
        <div class="{{ config('layoutmodule.form.hint') }}">PDF or image, max 5 MB.{{ $certificate?->attachment ? ' A new file replaces the saved one.' : '' }}</div>
        @error('attachment')
            <div class="{{ config('layoutmodule.form.error') }}">{{ $message }}</div>
        @enderror
    </div>
    @if ($certificate?->attachment)
        <div class="md:pt-8">
            @include('layoutmodule::partials.checkbox', ['name' => 'delete_attachment', 'label' => 'Remove the saved file', 'checked' => false])
        </div>
    @endif
</div>

<div class="mt-5">
    @include('layoutmodule::partials.field', ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'value' => $certificate?->notes])
</div>
