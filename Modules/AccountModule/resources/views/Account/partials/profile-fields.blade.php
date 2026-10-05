{{-- Account profile fields, shared by the admin form and the account "My Profile" page. --}}
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    @include('layoutmodule::partials.field', ['name' => 'name_ar', 'label' => 'Name (Arabic)', 'required' => true, 'value' => $account?->name_ar, 'attrs' => 'dir=rtl'])
    @include('layoutmodule::partials.field', ['name' => 'name_en', 'label' => 'Name (English)', 'value' => $account?->name_en])
    @include('layoutmodule::partials.field', ['name' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'required' => true, 'value' => $account?->phone, 'attrs' => 'inputmode="tel" pattern="\+?[0-9]{8,15}" placeholder="01012345678"'])
    @include('layoutmodule::partials.field', ['name' => 'address', 'label' => 'Address', 'value' => $account?->address])
    @include('layoutmodule::partials.field', ['name' => 'description_ar', 'label' => 'Description (Arabic)', 'type' => 'textarea', 'value' => $account?->description_ar, 'attrs' => 'dir=rtl'])
    @include('layoutmodule::partials.field', ['name' => 'description_en', 'label' => 'Description (English)', 'type' => 'textarea', 'value' => $account?->description_en])
    <div class="flex items-center gap-4">
        @if ($account)
            @include('accountmodule::Account.partials.logo', ['account' => $account])
        @endif
        <div class="flex-1">
            @include('layoutmodule::partials.field', ['name' => 'logo', 'label' => 'Logo', 'type' => 'file', 'hint' => 'Image, max 2 MB.'])
        </div>
    </div>
</div>
