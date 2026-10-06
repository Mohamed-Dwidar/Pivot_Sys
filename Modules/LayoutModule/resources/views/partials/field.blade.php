{{--
    Form field (template style) with label + hint + validation error.
    @include('layoutmodule::partials.field', [
        'name' => 'name_ar', 'label' => 'Name (Arabic)',
        'type' => 'text|email|password|tel|textarea|select|file',  (default text)
        'value' => $model->name_ar ?? null, 'required' => true,
        'options' => [value => label] (select), 'hint' => '...', 'attrs' => 'dir=rtl autofocus',
        'size' => 'lg' (login / register pages),
    ])
--}}
@php
    $type = $type ?? 'text';
    $id = 'field-' . $name;
    $current = $type == 'password' || $type == 'file' ? null : old($name, $value ?? null);

    $classKey = in_array($type, ['textarea', 'select']) ? $type : (($size ?? null) == 'lg' ? 'input_lg' : 'input');
    $class = config('layoutmodule.form.' . $classKey);
    // number / date / time / select / file are not full width (see custom.css "Field widths")
    $widthClass = [
        'number' => 'field-short', 'date' => 'field-short', 'time' => 'field-short', 'datetime-local' => 'field-medium',
        'select' => 'field-medium', 'file' => 'field-long',
    ][$type] ?? null;
    if ($widthClass) {
        $class .= ' ' . $widthClass;
    }
    if ($errors->has($name)) {
        $class = str_replace(['border-slate-200', 'border-slate-300/80'], 'border-danger', $class);
    }
@endphp
<div data-field="{{ $name }}">
    <label for="{{ $id }}" class="{{ config('layoutmodule.form.label') }}">
        {{ $label }}
        @if (!empty($required))
            <span class="text-danger">*</span>
        @endif
    </label>

    @if ($type == 'textarea')
        <textarea name="{{ $name }}" id="{{ $id }}" rows="4" class="{{ $class }}" {!! $attrs ?? '' !!}>{{ $current }}</textarea>
    @elseif ($type == 'select')
        <select name="{{ $name }}" id="{{ $id }}" class="{{ $class }}" {!! $attrs ?? '' !!}>
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) $current === (string) $optionValue)>{{ $optionLabel }}</option>
            @endforeach
        </select>
    @else
        <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" class="{{ $class }}"
            @if ($current !== null) value="{{ $current }}" @endif
            @if ($type == 'file') accept="image/*" @endif
            {!! $attrs ?? '' !!}>
    @endif

    @if (!empty($hint))
        <div class="{{ config('layoutmodule.form.hint') }}">{{ $hint }}</div>
    @endif
    @error($name)
        <div class="{{ config('layoutmodule.form.error') }}">{{ $message }}</div>
    @enderror
</div>
