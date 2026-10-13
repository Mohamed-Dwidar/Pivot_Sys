{{-- the list Remaining column (the shared payments cell: red / blue credit / 0 / "-" in a package) --}}
@include('paymentmodule::Payment.partials.remaining-amount', ['payable' => $reservation])
