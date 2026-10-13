{{-- the list Status column: the package status (Active / Inactive), then the payment status (same badges as reservations) --}}
<div><span class="badge {{ $package->is_active ? 'badge-status-success' : 'badge-status-slate' }}">{{ $package->is_active ? 'Active' : 'Inactive' }}</span></div>
<div class="mt-1">@include('paymentmodule::Payment.partials.payment-badge', ['payable' => $package])</div>
