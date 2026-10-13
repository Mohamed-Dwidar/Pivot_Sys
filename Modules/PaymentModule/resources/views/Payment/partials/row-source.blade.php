{{--
    what is paid (opens its view in the popup): a reservation = (unit color) "Space › Unit" + its start under it; a package = its name;
    other income = the income category
--}}
@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@if ($payment->payable instanceof \Modules\ReservationModule\app\Models\Reservation)
    <a href="{{ route($area . '.reservations.show', $payment->payable_id) }}" data-modal>@include('reservationmodule::Reservation.partials.row-place', ['reservation' => $payment->payable])</a>
    <div class="text-xs text-slate-500 whitespace-nowrap">{{ $payment->payable->formatAt($payment->payable->start_at) }}</div>
@elseif ($payment->payable instanceof \Modules\PackageModule\app\Models\Package)
    <a href="{{ route($area . '.packages.show', $payment->payable_id) }}" data-modal><i data-lucide="package" class="inline h-3 w-3"></i> {{ $payment->payable->name }}</a>
@elseif ($payment->payable_type)
    {{-- deleted reservation / package --}}
    <span class="text-slate-500">#{{ $payment->payable_id }} (deleted)</span>
@else
    {{ $payment->incomeCategory?->name ?? 'Other income' }}
@endif
