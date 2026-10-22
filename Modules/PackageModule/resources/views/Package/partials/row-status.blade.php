{{-- the list Status column: the payment status (same badges as reservations; no Active / Inactive since 2026-10-21) --}}
<div>@include('paymentmodule::Payment.partials.payment-badge', ['payable' => $package])</div>
