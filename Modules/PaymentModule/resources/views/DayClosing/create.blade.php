@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@php($dateText = $date->format(\Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT))
@extends(Auth::user()->userable->layout())

{{-- a full page: the date picker reloads it with ?date= --}}
@section('title')
    Close the Day {{ $dateText }}
@endsection

@section('actions')
    <form method="GET" action="{{ route($area . '.day-closings.create') }}" class="filter-bar">
        <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" max="{{ today()->format('Y-m-d') }}" class="{{ config('layoutmodule.form.input') }} field-short" aria-label="Day">
        <button type="submit" class="btn btn-secondary"><i data-lucide="calendar-search"></i> Show</button>
    </form>
    <a href="{{ route($area . '.day-closings.index') }}" class="btn btn-secondary"><i data-lucide="list"></i> Closed Days</a>
@endsection

@section('content')
    @if ($closing?->status === 'closed')
        <div class="mb-5">
            This day is <a href="{{ route($area . '.day-closings.show', $closing->id) }}" data-modal class="font-medium">closed</a>, its payments are locked.
        </div>
    @elseif ($closing)
        <div class="mb-5 text-warning">This day was reopened ({{ $closing->reopen_reason }}). Close it again when its payments are checked.</div>
    @endif

    {{-- the totals now (saved when the day is closed) --}}
    @include('paymentmodule::DayClosing.partials.totals')

    @unless ($closing?->status === 'closed')
        <form method="POST" action="{{ route($area . '.day-closings.store') }}" data-ajax
            data-confirm="The payments of {{ $dateText }} will be locked: no adding, changing or deleting them until the owner reopens the day."
            data-confirm-title="Close {{ $dateText }}?"
            data-confirm-button="Close the Day"
            data-confirm-variant="danger">
            @csrf
            <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i data-lucide="lock"></i> Close this Day</button>
            </div>
        </form>
    @endunless
@endsection
