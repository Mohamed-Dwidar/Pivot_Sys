{{--
    The list Reservations column, two lines: its counted reservations (not cancelled like), then the booked hours of the timed
    ones in brackets "91" / "(124.5 h)" (full day / contract types count, no hours); no timed ones = the count only (Package::scopeWithReservationStats)
--}}
@php($hours = round(((int) $package->booked_minutes) / 60, 1))
<div>{{ (int) $package->counted_reservations }}</div>
@if ($hours > 0)
    <div class="text-slate-500">({{ rtrim(rtrim(number_format($hours, 1), '0'), '.') }} h)</div>
@endif
