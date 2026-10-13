{{-- the list Remaining column: red when owed, a member credit (negative) in blue as "credit", 0 normal; in a package: "-" --}}
@if (!$reservation->canTakePayments())
    <span class="text-slate-500">-</span>
@elseif ($reservation->remaining > 0)
    <span class="text-danger font-medium">{{ number_format($reservation->remaining, 2) }}</span>
@elseif ($reservation->remaining < 0)
    <span class="text-info font-medium">{{ number_format(-$reservation->remaining, 2) }}</span> <span class="text-xs text-info">credit</span>
@else
    {{ number_format($reservation->remaining, 2) }}
@endif
