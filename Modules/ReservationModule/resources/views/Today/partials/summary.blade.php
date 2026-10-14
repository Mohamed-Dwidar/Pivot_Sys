{{-- The summary row: the day's count of each hourly / full day section, renewals needing action, the day's remaining (Due) --}}
@php($firstPeriod = $sections->firstWhere('mode', 'period'))
@if ($sections->isNotEmpty())
    <div class="today-summary">
        @foreach ($sections->whereIn('mode', ['hourly', 'full_day']) as $section)
            <a href="#{{ $section['anchor'] }}" class="today-summary__item"><span>{{ $section['type']->name }}</span> <b>{{ $section['count'] }}</b></a>
        @endforeach
        @if ($firstPeriod)
            <a href="#{{ $firstPeriod['anchor'] }}" class="today-summary__item"><span>Renewals</span> <b class="{{ $renewals ? 'text-danger' : '' }}">{{ $renewals }}</b></a>
        @endif
        <span class="today-summary__item"><span>Due</span> <b class="{{ $due > 0 ? 'text-danger' : '' }}">{{ number_format($due, 2) }}</b></span>
    </div>
@endif
