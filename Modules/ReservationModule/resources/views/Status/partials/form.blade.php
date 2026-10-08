{{-- Reservation status create / edit fields. $status is null on create. --}}
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    @include('layoutmodule::partials.field', ['name' => 'name_ar', 'label' => 'Name (Arabic)', 'required' => true, 'value' => $status?->name_ar, 'attrs' => 'dir="rtl" autofocus'])
    @include('layoutmodule::partials.field', ['name' => 'name_en', 'label' => 'Name (English)', 'value' => $status?->name_en, 'hint' => 'e.g. Confirmed, Waiting, Cancelled.'])
    @include('layoutmodule::partials.field', ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'value' => $status?->sort_order,
        'attrs' => 'min="0" max="65535" step="1"', 'hint' => $status ? 'Lower first in the lists.' : 'Lower first in the lists. Empty = after the last one.'])
    @include('layoutmodule::partials.field', ['name' => 'color', 'label' => 'Color', 'type' => 'select', 'required' => true,
        'options' => \Modules\ReservationModule\app\Models\ReservationStatus::COLORS, 'value' => $status?->color ?? 'slate', 'hint' => 'The color of its badge.'])
    @include('layoutmodule::partials.checkbox', ['name' => 'is_counted', 'label' => 'Counts (active reservations)', 'checked' => $status?->is_counted ?? true,
        'hint' => 'Turn it off for cancelled like statuses: their reservations are not counted in the package price and hours.'])
    @include('layoutmodule::partials.checkbox', ['name' => 'is_default', 'label' => 'Default status', 'checked' => $status?->is_default ?? false,
        'hint' => 'Given to the new reservations. Only one default status, it must be active.'])
    @include('layoutmodule::partials.checkbox', ['name' => 'is_active', 'label' => 'Active', 'checked' => $status?->is_active ?? true, 'hint' => 'Inactive statuses are hidden from the reservation form.'])
</div>
