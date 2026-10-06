{{--
    Template style checkbox; sends 0 when it is not checked.
    @include('layoutmodule::partials.checkbox', ['name' => 'is_active', 'label' => 'Active', 'checked' => true, 'hint' => '...'])
--}}
@php($id = 'field-' . $name)
<div data-field="{{ $name }}">
    <input type="hidden" name="{{ $name }}" value="0">
    <div class="flex items-center">
        <input type="checkbox" name="{{ $name }}" id="{{ $id }}" value="1" class="{{ config('layoutmodule.form.checkbox') }}"
            @checked(old($name, $checked ?? false))>
        <label for="{{ $id }}" class="{{ config('layoutmodule.form.checkbox_label') }}">{{ $label }}</label>
    </div>
    @if (!empty($hint))
        <div class="{{ config('layoutmodule.form.hint') }}">{{ $hint }}</div>
    @endif
    @error($name)
        <div class="{{ config('layoutmodule.form.error') }}">{{ $message }}</div>
    @enderror
</div>
