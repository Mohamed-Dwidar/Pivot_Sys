{{--
    Reservation create / edit fields. $reservation is null on create (or not saved yet: a renewal pre-filled from a subscription). Every drop menu is searchable (Tom Select).
    The drop menus chain (custom.js [data-options-url]): subscription type -> spaces -> units -> plans, loaded by ajax.
    The package locks the member, the plan fills the amount; discount % / discount value / net amount calculate each other
    (custom.js "Reservation form"), the server calculates them again (and the subscription day from the start date).
    The status is not chosen here (a new reservation gets the first active status, it is changed in another place).
--}}
@php
    $area = request()->routeIs('employee.*') ? 'employee' : 'account';
    // the unit capacity (data-capacity): 1 = the number of people is 1 and can not be changed (custom.js)
    $unitOptions = collect($units)->mapWithKeys(fn ($unit) => [$unit['id'] => ['name' => $unit['name'], 'data' => ['capacity' => $unit['capacity']]]])->all();
    // data-time-based: the plan reservations have a start / end time (custom.js shows the time inputs)
    $planOptions = collect($plans)->mapWithKeys(fn ($plan) => [$plan['id'] => ['name' => $plan['name'], 'data' => ['amount' => $plan['amount'], 'time-based' => $plan['timeBased'], 'period-hours' => $plan['periodHours'], 'lease-period' => $plan['leasePeriod']]]])->all();
    $packageOptions = collect($packages)->map(fn ($package) => ['name' => $package['name'], 'data' => ['member-id' => $package['member_id']]])->all();
    // the date / time inputs need Y-m-d / H:i values (the browser shows them in the user's format)
    // create: today, now -> in 1 hour (pre-filled from the Today page: its start -> in 1 hour); edit / renew: the saved values
    // (the times only for a time based plan)
    $now = now();
    $prefilled = $reservation && !$reservation->exists && !$reservation->plan_id;
    $fresh = !$reservation || $prefilled;
    $startDefault = $prefilled && $reservation->start_at ? $reservation->start_at : $now;
    $endDefault = $startDefault->copy()->addHour();
    $timeBased = (bool) $reservation?->plan?->is_time_based;
    $startAt = old('start_at', $fresh ? $startDefault->format('Y-m-d') : $reservation->start_at?->format('Y-m-d'));
    $endAt = $fresh ? $endDefault->format('Y-m-d') : $reservation->end_at?->format('Y-m-d');
    $startTime = old('start_time', $fresh ? $startDefault->format('H:i') : ($timeBased ? $reservation->start_at?->format('H:i') : null));
    $endTime = old('end_time', $fresh ? $endDefault->format('H:i') : ($timeBased ? $reservation->end_at?->format('H:i') : null));
    // a new reservation at night (before the cutoff of Settings › General): a full day one is for the business date (the day
    // before), a time based one for now — custom.js reservationTimeBased() switches the untouched dates with the plan
    $nightDates = '';
    $businessDate = \Modules\AccountModule\app\Models\Account::current()?->businessDate();
    if (!$reservation && $businessDate && !$businessDate->isSameDay($now)) {
        $nightDates = ' data-business-date="' . $businessDate->format('Y-m-d') . '"';
        $startAt = old('start_at', $businessDate->format('Y-m-d'));
        $endAt = $businessDate->format('Y-m-d');
    }
    // "Renewal of #": the account's edit form only (a mistake fix); the Renew form sends it hidden
    $renewalField = $reservation?->exists && $area === 'account' && $reservation->isPeriod();
@endphp

@if ($reservation && !$reservation->exists && $reservation->renewal_of)
    <input type="hidden" name="renewal_of" value="{{ $reservation->renewal_of }}">
    <div class="mb-5 text-primary">Renewal of #{{ $reservation->renewal_of }}: it starts the day after that period ends.</div>
@endif

<div class="form-section">Member</div>
<div class="grid grid-cols-1 gap-5 md:grid-cols-3">
    @include('layoutmodule::partials.field', ['name' => 'package_id', 'label' => 'Package', 'type' => 'select', 'searchable' => true,
        'options' => ['' => '- No package -'] + $packageOptions, 'value' => $reservation?->package_id ?: null,
        'hint' => 'With a package, the member is the package member.'])
</div>
<div class="grid grid-cols-1 gap-5 mt-5 md:grid-cols-1">
    @include('layoutmodule::partials.field', ['name' => 'member_id', 'label' => 'Member', 'type' => 'select', 'required' => true, 'searchable' => true, 'width' => 'long',
        'options' => ['' => '- Select -'] + $members, 'value' => $reservation?->member_id,
        'hint' => empty($members) ? 'No members yet, add them from Members.' : 'Search by name or mobile.'])
</div>

<div class="form-section mt-8">Place & Plan</div>
<div class="grid grid-cols-1 gap-5 md:grid-cols-3">
    @include('layoutmodule::partials.field', ['name' => 'subscription_type_id', 'label' => 'Subscription Type', 'type' => 'select', 'required' => true, 'searchable' => true,
        'options' => ['' => '- Select -'] + $subscriptionTypes, 'value' => $reservation?->subscription_type_id ?: null,
        'hint' => empty($subscriptionTypes) ? 'No subscription types yet, add them from Spaces > Subscription Types.' : 'The spaces with this subscription type are shown.'])
</div>
<div class="grid grid-cols-1 gap-5 mt-5 md:grid-cols-3">
    @include('layoutmodule::partials.field', ['name' => 'space_id', 'label' => 'Space', 'type' => 'select', 'required' => true, 'searchable' => true,
        'options' => ['' => '- Select -'] + $spaces, 'value' => $reservation?->space_id,
        'attrs' => 'data-options-url="' . route($area . '.reservations.options.spaces') . '" data-parent="subscription_type_id" data-none="No spaces with this subscription type"' . (empty($spaces) ? ' disabled' : '')])
    @include('layoutmodule::partials.field', ['name' => 'unit_id', 'label' => 'Unit', 'type' => 'select', 'required' => true, 'searchable' => true,
        'options' => ['' => '- Select -'] + $unitOptions, 'value' => $reservation?->unit_id,
        'attrs' => 'data-options-url="' . route($area . '.reservations.options.units') . '" data-parent="space_id" data-also="subscription_type_id" data-none="No active units for this space and subscription type"' . (empty($unitOptions) ? ' disabled' : '')])
    @include('layoutmodule::partials.field', ['name' => 'plan_id', 'label' => 'Plan', 'type' => 'select', 'required' => true, 'searchable' => true,
        'options' => ['' => '- Select -'] + $planOptions, 'value' => $reservation?->plan_id,
        'attrs' => 'data-options-url="' . route($area . '.reservations.options.plans') . '" data-parent="unit_id" data-none="No plans for this unit"' . (empty($planOptions) ? ' disabled' : ''),
        'hint' => 'The amount is filled from the plan.'])
    @include('layoutmodule::partials.field', ['name' => 'number_of_peoples', 'label' => 'Number of People', 'type' => 'number', 'required' => true,
    'value' => $reservation?->number_of_peoples ?? 1, 'attrs' => 'min="1" step="1" max="100000"', 'hint' => 'Not more than the unit capacity.'])
</div>

<div class="form-section mt-8">Dates</div>
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div class="date-time-pair">
        @include('layoutmodule::partials.field', ['name' => 'start_at', 'label' => 'Start Date', 'type' => 'date', 'required' => true, 'value' => $startAt,
            'attrs' => $nightDates ? $nightDates . ' data-real-date="' . $now->format('Y-m-d') . '"' : ''])
        {{-- only for a time based plan (custom.js [data-time-fields]) --}}
        <div data-time-fields>
            @include('layoutmodule::partials.field', ['name' => 'start_time', 'label' => 'Start Time', 'type' => 'time', 'required' => true, 'value' => $startTime])
        </div>
    </div>
</div>
<div class="grid grid-cols-1 gap-5 mt-5 md:grid-cols-3">
    <div class="date-time-pair">
        {{-- always required ("The reservation continues" removed 2026-10-15: a subscription is renewed by a new period, renewal_of) --}}
        @include('layoutmodule::partials.field', ['name' => 'end_at', 'label' => 'End Date', 'type' => 'date', 'required' => true,
            'value' => old('end_at', $endAt),
            'attrs' => 'data-default-from="start_at"' . ($nightDates ? $nightDates . ' data-real-date="' . $endDefault->format('Y-m-d') . '"' : '')])
        <div data-time-fields>
            @include('layoutmodule::partials.field', ['name' => 'end_time', 'label' => 'End Time', 'type' => 'time', 'required' => true, 'value' => $endTime])
        </div>
    </div>
</div>

<div class="form-section mt-8">Amount</div>
{{-- discount_type = the one typed last (percentage | value | net), the server keeps it and calculates the others --}}
<input type="hidden" name="discount_type" value="{{ old('discount_type', 'percentage') }}">
<div class="grid grid-cols-1 gap-5 md:grid-cols-3">
    @include('layoutmodule::partials.field', ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true, 'value' => $reservation?->amount ?? 0,
        'attrs' => 'min="0" step="0.01" max="99999999"'])
    @include('layoutmodule::partials.field', ['name' => 'discount_percentage', 'label' => 'Discount %', 'type' => 'number', 'value' => $reservation?->discount_percentage ?? 0,
        'attrs' => 'min="0" max="100" step="0.01"'])
    @include('layoutmodule::partials.field', ['name' => 'discount_value', 'label' => 'Discount Value', 'type' => 'number', 'value' => $reservation?->discount_value ?? 0,
        'attrs' => 'min="0" step="0.01"'])
    @include('layoutmodule::partials.field', ['name' => 'net_amount', 'label' => 'Net Amount', 'type' => 'number', 'value' => $reservation?->net_amount ?? 0,
        'attrs' => 'min="0" step="0.01" data-net', 'hint' => 'Change the discount % / value or the net, the others are calculated.'])
</div>

{{-- only when the subscription type can repeat (custom.js [data-type-allows]) --}}
{{-- on create only: the copies are made when it is saved (not changed on edit) --}}
@unless ($reservation?->exists)
    <div data-type-allows="can-repeat">
        <div class="form-section mt-8">Repeat</div>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-4">
            <div class="md:pt-8">
                @include('layoutmodule::partials.checkbox', ['name' => 'is_repeat', 'label' => 'Repeat this reservation', 'checked' => false])
            </div>
            <div data-show-if="is_repeat">
                @include('layoutmodule::partials.field', ['name' => 'repeat_frequency', 'label' => 'Repeat Frequency', 'type' => 'select', 'searchable' => true, 'required' => true,
                    'options' => \Modules\ReservationModule\app\Models\Reservation::REPEAT_FREQUENCIES, 'value' => 'weekly'])
            </div>
            <div data-show-if="is_repeat">
                @include('layoutmodule::partials.field', ['name' => 'repeat_interval', 'label' => 'Repeat Every', 'type' => 'number', 'required' => true,
                    'value' => 1, 'attrs' => 'min="1" step="1" max="365"', 'hint' => 'e.g. 2 with weekly = every 2 weeks.'])
            </div>
            <div data-show-if="is_repeat">
                @include('layoutmodule::partials.field', ['name' => 'repeat_until', 'label' => 'Repeat Until', 'type' => 'date', 'required' => true,
                    'hint' => 'A copy of the reservation is added for each repeat until this date.'])
            </div>
        </div>
    </div>
@endunless

@if ($renewalField)
    <div class="form-section mt-8">Renewal</div>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
        @include('layoutmodule::partials.field', ['name' => 'renewal_of', 'label' => 'Renewal of #', 'type' => 'number', 'value' => $reservation->renewal_of,
            'attrs' => 'min="1" step="1"', 'hint' => 'The reservation number of the period this one renews (empty = none). Same subscription type, not renewed by another one.'])
    </div>
@endif

<div class="mt-5">
    @include('layoutmodule::partials.field', ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'value' => $reservation?->notes])
</div>
