{{-- Package create / edit fields. $package is null on create. $members = [id => "name mobile"] --}}
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    @include('layoutmodule::partials.field', ['name' => 'name', 'label' => 'Name', 'required' => true, 'value' => $package?->name, 'attrs' => 'autofocus'])
    @include('layoutmodule::partials.field', ['name' => 'member_id', 'label' => 'Member', 'type' => 'select', 'required' => true,
        'options' => ['' => '- Select -'] + $members, 'value' => $package?->member_id, 'searchable' => true, 'width' => 'xlong', 'wrapperClass' => 'md:col-span-2',
        'hint' => empty($members) ? 'No members yet, add them from Members.' : 'The reservations of this package are for this member.'])
</div>

<div class="form-section mt-8">Price</div>
{{-- the price = the net amounts of the package reservations (calculated, not typed);
     the discount % and the net calculate each other, discount_type = the one typed last (saved as a %) --}}
<div class="grid grid-cols-1 gap-5 md:grid-cols-3" data-package-price>
    <input type="hidden" name="discount_type" value="{{ old('discount_type', 'percentage') }}" data-calc="type">
    @include('layoutmodule::partials.field', ['name' => 'amount_display', 'label' => 'Price', 'type' => 'number', 'value' => $package?->reservationsTotal() ?? 0,
        'attrs' => 'readonly tabindex="-1" data-calc="amount"', 'hint' => $package ? 'The net amounts of its reservations.' : 'The net amounts of its reservations (0 until reservations are added).'])
    @include('layoutmodule::partials.field', ['name' => 'discount_percentage', 'label' => 'Discount %', 'type' => 'number', 'value' => $package?->discount_percentage ?? 0,
        'attrs' => 'min="0" max="100" step="0.01" data-calc="discount"'])
    @include('layoutmodule::partials.field', ['name' => 'after_discount', 'label' => 'Net', 'type' => 'number',
        'value' => $package?->after_discount ?? 0, 'attrs' => 'min="0" step="0.01" max="99999999" data-calc="result"', 'hint' => 'The price - the discount. Change it to calculate the discount %.'])
</div>

<div class="grid grid-cols-1 gap-5 mt-5 md:grid-cols-2">
    @include('layoutmodule::partials.field', ['name' => 'notes', 'label' => 'Notes', 'value' => $package?->notes])
    @include('layoutmodule::partials.checkbox', ['name' => 'is_active', 'label' => 'Active', 'checked' => $package?->is_active ?? true, 'hint' => 'Inactive packages can not be chosen for new reservations.'])
</div>
