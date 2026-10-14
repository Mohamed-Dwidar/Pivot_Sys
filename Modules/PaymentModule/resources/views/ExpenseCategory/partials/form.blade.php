{{-- Expense category create / edit fields. $category is null on create. --}}
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    @include('layoutmodule::partials.field', ['name' => 'name_ar', 'label' => 'Name (Arabic)', 'required' => true, 'value' => $category?->name_ar, 'attrs' => 'dir="rtl" autofocus'])
    @include('layoutmodule::partials.field', ['name' => 'name_en', 'label' => 'Name (English)', 'value' => $category?->name_en, 'hint' => 'e.g. Electricity, Cleaning Tools, Salaries.'])
    @include('layoutmodule::partials.checkbox', ['name' => 'is_active', 'label' => 'Active', 'checked' => $category?->is_active ?? true, 'hint' => 'Inactive categories are hidden from the expense form.'])
</div>
