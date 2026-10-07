{{-- Package create / edit fields. $package is null on create. $members = [id => "name mobile"] --}}
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    @include('layoutmodule::partials.field', ['name' => 'name', 'label' => 'Name', 'required' => true, 'value' => $package?->name, 'attrs' => 'autofocus'])
    @include('layoutmodule::partials.field', ['name' => 'member_id', 'label' => 'Member', 'type' => 'select', 'required' => true,
        'options' => ['' => '- Select -'] + $members, 'value' => $package?->member_id, 'searchable' => true, 'width' => 'xlong', 'wrapperClass' => 'md:col-span-2',
        'hint' => empty($members) ? 'No members yet, add them from Members.' : 'The reservations of this package are for this member.'])
    @include('layoutmodule::partials.field', ['name' => 'date_from', 'label' => 'Start Date', 'type' => 'date', 'value' => $package?->date_from?->format('Y-m-d') ?? today()->format('Y-m-d')])
    @include('layoutmodule::partials.field', ['name' => 'date_to', 'label' => 'End Date', 'type' => 'date', 'value' => $package?->date_to?->format('Y-m-d')])
</div>

<div class="form-section mt-8">Price</div>
{{-- the discount % and the after discount calculate each other; discount_type = the one typed last (the server keeps it) --}}
<div class="grid grid-cols-1 gap-5 md:grid-cols-3" data-package-price>
    <input type="hidden" name="discount_type" value="{{ old('discount_type', 'percentage') }}" data-calc="type">
    @include('layoutmodule::partials.field', ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true, 'value' => $package?->amount ?? 0,
        'attrs' => 'min="0" step="0.01" max="99999999" data-calc="amount"'])
    @include('layoutmodule::partials.field', ['name' => 'discount_percentage', 'label' => 'Discount %', 'type' => 'number', 'value' => $package?->discount_percentage ?? 0,
        'attrs' => 'min="0" max="100" step="0.01" data-calc="discount"'])
    @include('layoutmodule::partials.field', ['name' => 'after_discount', 'label' => 'After Discount', 'type' => 'number',
        'value' => $package?->after_discount ?? 0, 'attrs' => 'min="0" step="0.01" max="99999999" data-calc="result"', 'hint' => 'Change it to calculate the discount %.'])
</div>

<div class="grid grid-cols-1 gap-5 mt-5 md:grid-cols-2">
    @include('layoutmodule::partials.field', ['name' => 'notes', 'label' => 'Notes', 'value' => $package?->notes])
    @include('layoutmodule::partials.checkbox', ['name' => 'is_active', 'label' => 'Active', 'checked' => $package?->is_active ?? true, 'hint' => 'Inactive packages can not be chosen for new reservations.'])
</div>
