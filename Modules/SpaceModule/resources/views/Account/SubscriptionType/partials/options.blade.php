@php($enabledOptions = collect(\Modules\SpaceModule\app\Models\SubscriptionType::OPTIONS)->filter(fn ($label, $option) => $subscriptionType->$option))
@forelse ($enabledOptions as $optionLabel)
    <span class="badge badge-active mr-1">{{ $optionLabel }}</span>
@empty
    -
@endforelse
