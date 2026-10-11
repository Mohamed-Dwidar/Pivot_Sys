{{--
    Extra / adjustment fields of a package. $extra (new on create), $package, $categories.
    extra = a sale (income category, revenue); adjustment = a balance move (no category, can be negative).
    The amount = quantity × unit price (the server calculates it).
--}}
<div class="grid grid-cols-1 gap-5 md:grid-cols-3">
    @include('layoutmodule::partials.field', ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'required' => true,
        'options' => \Modules\PaymentModule\app\Models\Extra::TYPES, 'value' => $extra->type,
        'hint' => 'Extra = a sale (revenue). Adjustment = a balance move (carry-over), not revenue.'])
    <div data-show-if="type=extra">
        @include('layoutmodule::partials.field', ['name' => 'income_category_id', 'label' => 'Income Category', 'type' => 'select', 'required' => true, 'searchable' => true,
            'options' => ['' => '- Select -'] + $categories, 'value' => $extra->income_category_id ?: null])
    </div>
    @include('layoutmodule::partials.field', ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true, 'value' => $extra->date?->format('Y-m-d')])
</div>
<div class="grid grid-cols-1 gap-5 mt-5 md:grid-cols-3">
    @include('layoutmodule::partials.field', ['name' => 'item', 'label' => 'Item', 'required' => true, 'value' => $extra->item, 'attrs' => 'maxlength="255"',
        'hint' => 'e.g. Copies, Buffet, Carry-over from last month.'])
    @include('layoutmodule::partials.field', ['name' => 'quantity', 'label' => 'Quantity', 'type' => 'number', 'required' => true, 'value' => $extra->quantity,
        'attrs' => 'min="0.01" step="0.01" max="99999"'])
    @include('layoutmodule::partials.field', ['name' => 'unit_price', 'label' => 'Unit Price', 'type' => 'number', 'required' => true, 'value' => $extra->unit_price,
        'attrs' => 'step="0.01" min="-99999999" max="99999999"', 'hint' => 'Negative for a return / a credit.'])
</div>
<div class="mt-5">
    @include('layoutmodule::partials.field', ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'value' => $extra->notes])
</div>
