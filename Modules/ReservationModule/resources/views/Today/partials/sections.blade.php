{{-- The Today page content (also answered alone by {area}.today.sections): night hint, shift strip, summary, the sections --}}
@if ($nightNow)
    <div class="today-night"><i data-lucide="moon"></i> Night shift · {{ $nightNow->format('d-m-Y h:i A') }}</div>
@endif

@include('reservationmodule::Today.partials.shift-strip')
@include('reservationmodule::Today.partials.summary')

@forelse ($sections as $section)
    @include('reservationmodule::Today.partials.' . ['hourly' => 'hourly', 'full_day' => 'full-day', 'period' => 'period'][$section['mode']])
@empty
    <div class="today-empty">
        Nothing to show yet: turn on "Show on Home" for the subscription types that should appear here
        @if ($area === 'account')
            (<a href="{{ route('account.subscription-types.index') }}" class="font-medium">Subscription Types</a>).
        @endif
    </div>
@endforelse
