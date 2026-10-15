{{-- Subscription type create / edit fields. $subscriptionType is null on create. --}}
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    @include('layoutmodule::partials.field', ['name' => 'name', 'label' => 'Name', 'required' => true, 'value' => $subscriptionType?->name, 'attrs' => 'autofocus'])
    @include('layoutmodule::partials.field', ['name' => 'booking_mode', 'label' => 'Booking Mode', 'type' => 'select', 'required' => true,
        'options' => \Modules\SpaceModule\app\Models\SubscriptionType::BOOKING_MODES, 'value' => $subscriptionType?->booking_mode ?? 'hourly',
        'hint' => 'Hourly = a timeline on the Schedule page, Full Day = the day\'s list, Period = subscriptions with renewals.'])
    @include('layoutmodule::partials.field', ['name' => 'home_order', 'label' => 'Schedule Page Order', 'type' => 'number', 'value' => $subscriptionType?->home_order ?? 0,
        'attrs' => 'min=0 max=999', 'hint' => 'The order of its section on the Schedule page (with Show on Home).'])
</div>

<div class="form-section mt-8">Options</div>
<div class="checkbox-list">
    @foreach (\Modules\SpaceModule\app\Models\SubscriptionType::OPTIONS as $option => $optionLabel)
        {{-- Contract: period types only --}}
        <div @if ($option === 'is_contract') data-show-if="booking_mode=period" @endif>
            @include('layoutmodule::partials.checkbox', ['name' => $option, 'label' => $optionLabel, 'checked' => $subscriptionType?->$option ?? false])
        </div>
    @endforeach
</div>
