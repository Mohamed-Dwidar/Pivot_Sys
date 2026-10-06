{{-- Color create / edit fields. $color is null on create. --}}
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    @include('layoutmodule::partials.field', ['name' => 'name', 'label' => 'Name', 'required' => true, 'value' => $color?->name, 'attrs' => 'autofocus'])
    @include('layoutmodule::partials.field', ['name' => 'value', 'label' => 'Color', 'type' => 'color', 'required' => true, 'value' => $color?->value ?? '#2563eb'])
</div>
