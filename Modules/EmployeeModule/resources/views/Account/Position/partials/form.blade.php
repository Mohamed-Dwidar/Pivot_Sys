{{-- Position create / edit fields. $position is null on create. --}}
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    @include('layoutmodule::partials.field', ['name' => 'name', 'label' => 'Name', 'required' => true, 'value' => $position?->name, 'attrs' => 'autofocus'])
    @include('layoutmodule::partials.checkbox', ['name' => 'is_active', 'label' => 'Active', 'checked' => $position?->is_active ?? true, 'hint' => 'Inactive positions are hidden from the employee form.'])
</div>
