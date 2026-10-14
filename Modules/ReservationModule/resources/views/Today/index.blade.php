@extends(Auth::user()->userable->layout())

{{--
    The Today page (TodayActions / TodayService, the same for the account and its employees): one section per subscription type
    with Show on Home (home_order), laid out by its booking mode — nothing named in code. ?date=Y-m-d, default = the business
    date (Settings › General: at night, before the cutoff, still the day before). ‹ › = the day before / after.
    The sections are reloaded by custom.js todayPage after every save in a popup (route {area}.today.sections).
--}}
@section('title')
    Today · {{ $date->format('D d-m-Y') }}
@endsection

@section('actions')
    <div class="today-nav">
        <a href="{{ route($area . '.today.index', ['date' => $date->copy()->subDay()->toDateString()]) }}" class="btn btn-secondary" title="The day before"><i data-lucide="chevron-left"></i></a>
        <a href="{{ route($area . '.today.index') }}" class="btn {{ $isToday ? 'btn-primary' : 'btn-secondary' }}">Today</a>
        <a href="{{ route($area . '.today.index', ['date' => $date->copy()->addDay()->toDateString()]) }}" class="btn btn-secondary" title="The day after"><i data-lucide="chevron-right"></i></a>
    </div>
    {{-- + New: pick the type first (the form opens with it, for the selected day) --}}
    @if ($newTypes->isNotEmpty())
        <div data-tw-merge="" data-tw-placement="bottom-end" class="dropdown relative">
            <button type="button" data-tw-toggle="dropdown" aria-expanded="false" class="btn btn-primary"><i data-lucide="plus"></i> New <i data-lucide="chevron-down"></i></button>
            <div class="dropdown-menu js-menu absolute z-[9999]">
                <div data-tw-merge="" class="dropdown-content rounded-md border-transparent bg-white p-2 shadow-[0px_3px_10px_#00000017] dark:border-transparent dark:bg-darkmode-600 w-52">
                    @foreach ($newTypes as $new)
                        <a href="{{ route($area . '.reservations.create', ['subscription_type_id' => $new['type']->id, 'start' => $new['start']]) }}" data-modal data-tw-dismiss="dropdown" class="{{ config('layoutmodule.menu.item') }}">
                            <i data-lucide="calendar-plus" class="{{ config('layoutmodule.menu.icon') }}"></i> {{ $new['type']->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endsection

@section('content')
    <div class="today" data-today data-url="{{ route($area . '.today.sections', ['date' => $date->toDateString()]) }}">
        @include('reservationmodule::Today.partials.sections')
    </div>
@endsection
