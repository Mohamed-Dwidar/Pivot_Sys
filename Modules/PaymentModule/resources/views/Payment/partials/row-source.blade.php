{{-- what is paid: the reservation / package (opens its view in the popup) or the income category --}}
@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@if ($payment->payable instanceof \Modules\ReservationModule\app\Models\Reservation)
    <a href="{{ route($area . '.reservations.show', $payment->payable_id) }}" data-modal>Reservation #{{ $payment->payable_id }}</a>
@elseif ($payment->payable instanceof \Modules\PackageModule\app\Models\Package)
    <a href="{{ route($area . '.packages.show', $payment->payable_id) }}" data-modal><i data-lucide="package" class="inline h-3 w-3"></i> {{ $payment->payable->name }}</a>
@elseif ($payment->payable_type)
    {{-- deleted reservation / package --}}
    <span class="text-slate-500">#{{ $payment->payable_id }} (deleted)</span>
@else
    {{ $payment->incomeCategory?->name ?? 'Other income' }}
@endif
