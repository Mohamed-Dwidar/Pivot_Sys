{{-- the list Period column: "From 14-10-2024" / "Ends 03-12-2024" (from its counted reservations; no end = continues; none = -) --}}
@php($date = \Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT)
@if ($package->date_from)
    <div>From {{ $package->date_from->format($date) }}</div>
    <div class="text-slate-500">{{ $package->date_to ? 'Ends ' . $package->date_to->format($date) : 'Continues' }}</div>
@else
    <span class="text-slate-500">-</span>
@endif
