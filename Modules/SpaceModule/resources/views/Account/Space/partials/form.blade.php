{{-- Space create / edit fields. $space is null on create. --}}
<div class="form-section">Space Data</div>
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    @include('layoutmodule::partials.field', ['name' => 'name_ar', 'label' => 'Name (Arabic)', 'required' => true, 'value' => $space?->name_ar, 'attrs' => 'dir="rtl" autofocus'])
    @include('layoutmodule::partials.field', ['name' => 'name_en', 'label' => 'Name (English)', 'required' => true, 'value' => $space?->name_en])
    @include('layoutmodule::partials.checkbox', ['name' => 'is_active', 'label' => 'Active', 'checked' => $space?->is_active ?? true, 'hint' => 'Inactive spaces are hidden from booking.'])
</div>

<div class="form-section mt-8">Subscription Types</div>
@php($selectedTypes = array_map('intval', old('subscription_types', $space?->subscriptionTypes->pluck('id')->all() ?? [])))
@if ($subscriptionTypes)
    <div class="checkbox-list">
        @foreach ($subscriptionTypes as $typeId => $typeName)
            <div class="flex items-center">
                <input type="checkbox" name="subscription_types[]" id="type-{{ $typeId }}" value="{{ $typeId }}" class="{{ config('layoutmodule.form.checkbox') }}"
                    @checked(in_array($typeId, $selectedTypes))>
                <label for="type-{{ $typeId }}" class="{{ config('layoutmodule.form.checkbox_label') }}">{{ $typeName }}</label>
            </div>
        @endforeach
    </div>
@else
    <div class="text-slate-500">
        No subscription types yet. <a href="{{ route('account.subscription-types.create') }}" class="text-primary">Add a subscription type</a>
    </div>
@endif
@foreach (['subscription_types', 'subscription_types.*'] as $errorKey)
    @foreach ($errors->get($errorKey) as $messages)
        @foreach ((array) $messages as $message)
            <div class="{{ config('layoutmodule.form.error') }}">{{ $message }}</div>
        @endforeach
    @endforeach
@endforeach

<div class="form-section mt-8">Images</div>
@include('layoutmodule::partials.images-input', ['images' => $space?->images, 'alt' => $space?->name])
