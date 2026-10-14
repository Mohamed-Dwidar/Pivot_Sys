<span class="badge badge-inactive mr-1">{{ \Modules\SpaceModule\app\Models\SubscriptionType::BOOKING_MODES[$subscriptionType->booking_mode] ?? $subscriptionType->booking_mode }}</span>
@php($enabledOptions = collect(\Modules\SpaceModule\app\Models\SubscriptionType::OPTIONS)->filter(fn ($label, $option) => $subscriptionType->$option))
@foreach ($enabledOptions as $optionLabel)
    <span class="badge badge-active mr-1">{{ $optionLabel }}</span>
@endforeach
