{{-- the "payment status" select of a reservations / packages list filter bar ($statuses = HasPayments::PAYMENT_STATUSES) --}}
<select name="payment_status" class="{{ config('layoutmodule.form.select') }}" aria-label="Payment status">
    <option value="">All Payments</option>
    @foreach ($statuses as $value => $label)
        <option value="{{ $value }}">{{ $label }}</option>
    @endforeach
</select>
