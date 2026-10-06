{{-- Subscription type create / edit fields. $subscriptionType is null on create. --}}
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    @include('layoutmodule::partials.field', ['name' => 'name', 'label' => 'Name', 'required' => true, 'value' => $subscriptionType?->name, 'attrs' => 'autofocus'])
</div>

<div class="form-section mt-8">Options</div>
<div class="checkbox-list">
    @foreach (\Modules\SpaceModule\app\Models\SubscriptionType::OPTIONS as $option => $optionLabel)
        @include('layoutmodule::partials.checkbox', ['name' => $option, 'label' => $optionLabel, 'checked' => $subscriptionType?->$option ?? false])
    @endforeach
</div>
