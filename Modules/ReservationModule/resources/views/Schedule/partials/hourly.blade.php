{{--
    An hourly section ($section, ScheduleService::hourlySection()): Timeline | List (custom.js todayPage, remembered per type).
    Timeline: rows = the type's active units (by space when it has more than one), columns = the hours of Settings › General
    (15 minute slots: custom.css .tl-from-N / .tl-span-N / .tl-lane-N); a block opens the reservation, an empty hour opens a new
    one for that unit and hour; Earlier / Later columns for what is outside the hours; the now line on today.
--}}
@php
    $type = $section['type'];
    $hourFrom = $hours[0];
    $timeFormat = \Modules\ReservationModule\app\Models\Reservation::TIME_FORMAT;
@endphp
<section id="{{ $section['anchor'] }}" class="today-section">
    <div class="today-section__head">
        <div class="today-section__title">{{ $type->name }} <span class="today-section__count">{{ $section['count'] }}</span></div>
        <div class="today-section__tools">
            <div class="today-toggle" data-today-toggle="{{ $type->id }}">
                <button type="button" data-view="timeline" class="active">Timeline</button>
                <button type="button" data-view="list">List</button>
            </div>
            <a href="{{ route($area . '.reservations.create', ['subscription_type_id' => $type->id, 'start' => $newTypes->firstWhere('type.id', $type->id)['start'] ?? $date->toDateString()]) }}" data-modal class="btn btn-primary btn-sm"><i data-lucide="plus"></i> {{ $type->name }}</a>
        </div>
    </div>

    <div data-today-view="timeline">
        @if ($section['groups']->isEmpty())
            <div class="today-empty">No active units for {{ $type->name }}.</div>
        @else
            <div class="tl-scroll">
                <div class="tl tl-hours-{{ count($section['hours']) }}" @if ($isToday) data-tl-start="{{ $windowStart->getTimestamp() }}" data-tl-slots="{{ $section['slots'] }}" @endif>
                    {{-- the hours --}}
                    <div class="tl-row tl-row--head">
                        <div class="tl-label"></div>
                        @if ($section['hasEarlier'])
                            <div class="tl-side">Earlier</div>
                        @endif
                        <div class="tl-grid tl-lanes-1">
                            @foreach ($section['hours'] as $index => $hour)
                                <span class="tl-hour tl-from-{{ $index * 4 + 1 }} tl-span-4">{{ sprintf('%02d', ($hourFrom + $index) % 24) }}</span>
                            @endforeach
                        </div>
                        @if ($section['hasLater'])
                            <div class="tl-side">Later</div>
                        @endif
                    </div>

                    @foreach ($section['groups'] as $group)
                        @if ($section['multiSpace'])
                            <div class="tl-space">{{ $group['space']->name }}</div>
                        @endif
                        @foreach ($group['rows'] as $row)
                            @php($unit = $row['unit'])
                            <div class="tl-row">
                                <div class="tl-label" title="{{ $unit->name }}">
                                    @if ($unit->color)
                                        @include('unitmodule::partials.color-swatch', ['value' => $unit->color->value])
                                    @endif
                                    <span>{{ $unit->name }}</span>
                                </div>
                                @if ($section['hasEarlier'])
                                    <div class="tl-side">
                                        @foreach ($row['earlier'] as $reservation)
                                            <a href="{{ route($area . '.reservations.show', $reservation->id) }}" data-modal class="tl-chip" title="{{ $reservation->period }}">← {{ $reservation->member?->name ?? '-' }} {{ $reservation->start_at->format('H:i') }}–{{ $reservation->realEnd()?->format('H:i') }}</a>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="tl-grid tl-lanes-{{ $row['lanes'] }}">
                                    {{-- an empty hour: a new reservation of this unit at that hour (under the blocks) --}}
                                    @foreach ($section['hours'] as $index => $hour)
                                        @if ($unit->is_active && !$unit->trashed())
                                            <a href="{{ route($area . '.reservations.create', ['subscription_type_id' => $type->id, 'unit_id' => $unit->id, 'start' => $windowStart->copy()->addHours($index)->format('Y-m-d H:i')]) }}"
                                                data-modal class="tl-slot tl-from-{{ $index * 4 + 1 }} tl-span-4" title="New at {{ $windowStart->copy()->addHours($index)->format($timeFormat) }}"></a>
                                        @endif
                                    @endforeach
                                    @foreach ($row['blocks'] as $block)
                                        @php($reservation = $block['reservation'])
                                        @php($pay = $reservation->package_id ? 'package' : (in_array($reservation->payment_status, ['unpaid', 'partial'], true) ? 'due' : 'paid'))
                                        <a href="{{ route($area . '.reservations.show', $reservation->id) }}" data-modal
                                            class="tl-block tl-block--{{ $pay }} tl-from-{{ $block['from'] }} tl-span-{{ $block['span'] }} tl-lane-{{ $block['lane'] }} {{ $block['clipStart'] ? 'tl-block--clip-start' : '' }} {{ $block['clipEnd'] ? 'tl-block--clip-end' : '' }}"
                                            title="{{ $reservation->member?->name ?? '-' }} · {{ $reservation->period }}">
                                            <span class="tl-block__name">{{ $block['clipStart'] ? '← ' : '' }}{{ $reservation->member?->name ?? '-' }}{{ $block['clipEnd'] ? ' →' : '' }}</span>
                                            <span class="tl-block__time">{{ $reservation->start_at->format('H:i') }}–{{ $reservation->realEnd()?->format('H:i') ?? '…' }} · {{ ['paid' => 'Paid', 'due' => 'Due', 'package' => 'In package'][$pay] }}</span>
                                        </a>
                                    @endforeach
                                    @if ($isToday)
                                        <span class="tl-now" data-tl-now hidden></span>
                                    @endif
                                </div>
                                @if ($section['hasLater'])
                                    <div class="tl-side">
                                        @foreach ($row['later'] as $reservation)
                                            <a href="{{ route($area . '.reservations.show', $reservation->id) }}" data-modal class="tl-chip" title="{{ $reservation->period }}">{{ $reservation->member?->name ?? '-' }} {{ $reservation->start_at->format('H:i') }} →</a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div data-today-view="list" hidden>
        @if ($section['list']->isEmpty())
            <div class="today-empty">No {{ $type->name }} {{ $isToday ? 'today' : 'on this day' }}.</div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Time</th><th>Name</th><th>Unit</th><th>Status</th><th>Remaining</th></tr></thead>
                    <tbody>
                        @foreach ($section['list'] as $reservation)
                            <tr>
                                <td class="whitespace-nowrap">
                                    {{ $reservation->start_at->format($timeFormat) }} – {{ $reservation->realEnd()?->format($timeFormat) ?? '…' }}
                                    @unless ($reservation->today_own)
                                        <span class="badge badge-status-slate badge-small">from yesterday</span>
                                    @endunless
                                </td>
                                <td><a href="{{ route($area . '.reservations.show', $reservation->id) }}" data-modal class="font-medium">{{ $reservation->member?->name ?? '-' }}</a></td>
                                <td>{{ $reservation->unit?->name ?? '-' }}</td>
                                <td>@include('reservationmodule::Schedule.partials.pay-badge')</td>
                                <td class="whitespace-nowrap">@include('paymentmodule::Payment.partials.remaining-amount', ['payable' => $reservation])</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>
