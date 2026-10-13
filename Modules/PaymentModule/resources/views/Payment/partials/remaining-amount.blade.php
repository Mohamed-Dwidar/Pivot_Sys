{{--
    A list Remaining cell, no badge (reservations / packages lists): red when owed, a member credit (negative) in blue
    as "x credit", 0 normal; not payable on its own (a reservation inside a package): "-". $payable: Payable + HasPayments
--}}
@if (!$payable->canTakePayments())
    <span class="text-slate-500">-</span>
@elseif ($payable->remaining > 0)
    <span class="text-danger font-medium">{{ number_format($payable->remaining, 2) }}</span>
@elseif ($payable->remaining < 0)
    <span class="text-info font-medium">{{ number_format(-$payable->remaining, 2) }}</span> <span class="text-xs text-info">credit</span>
@else
    {{ number_format($payable->remaining, 2) }}
@endif
