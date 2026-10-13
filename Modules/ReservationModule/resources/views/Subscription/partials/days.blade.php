{{-- days left (running) / days since it ended (by the end date) --}}
@php($days = $reservation->days_left)
@if ($days === null)
    -
@elseif ($days >= 0)
    <span class="{{ $days <= 7 ? 'text-danger font-medium' : '' }}">{{ $days }} {{ Str::plural('day', $days) }} left</span>
@else
    <span class="text-slate-500">ended {{ -$days }} {{ Str::plural('day', -$days) }} ago</span>
@endif
