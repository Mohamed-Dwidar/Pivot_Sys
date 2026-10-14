{{--
    A period section ($section, TodayService::periodSection()): running counts per space (count only, never "x / total"; "Other"
    = outside the type's spaces), then the renewals: Due today | Not renewed | Next N days (empty groups hidden). A renewed
    period shows its renewal (renewal_of) instead of Renew.
--}}
@php($type = $section['type'])
<section id="{{ $section['anchor'] }}" class="today-section">
    <div class="today-section__head">
        <div class="today-section__title">{{ $type->name }}</div>
        <div class="today-section__tools">
            <a href="{{ route($area . '.reservations.index', ['subscription_type_id' => $type->id]) }}" class="btn btn-secondary btn-sm">View all <i data-lucide="arrow-right"></i></a>
        </div>
    </div>

    @if ($section['counts']->isNotEmpty())
        <div class="today-counts">
            @foreach ($section['counts'] as $count)
                <span class="today-counts__item">{{ $count['name'] }} <b>{{ $count['count'] }}</b></span>
            @endforeach
        </div>
    @endif

    @php($groups = [
        ['title' => 'Due today', 'rows' => $section['dueToday'], 'kind' => 'due'],
        ['title' => 'Not renewed', 'rows' => $section['notRenewed'], 'kind' => 'ended'],
        ['title' => 'Next ' . $section['renewalDays'] . ' days', 'rows' => $section['next'], 'kind' => 'next'],
    ])
    @if (collect($groups)->every(fn ($group) => $group['rows']->isEmpty()))
        <div class="today-empty">No renewals due.</div>
    @else
        <div class="table-wrap">
            <table class="data-table today-renewals">
                @foreach ($groups as $group)
                    @continue($group['rows']->isEmpty())
                    <tbody>
                        <tr class="today-renewals__group"><td colspan="4">{{ $group['title'] }} ({{ $group['rows']->count() }})</td></tr>
                        @foreach ($group['rows'] as $reservation)
                            @include('reservationmodule::Today.partials.renewal-row', ['kind' => $group['kind']])
                        @endforeach
                    </tbody>
                @endforeach
            </table>
        </div>
    @endif
</section>
