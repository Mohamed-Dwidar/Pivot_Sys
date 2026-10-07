{{--
    Form field (template style) with label + hint + validation error.
    @include('layoutmodule::partials.field', [
        'name' => 'name_ar', 'label' => 'Name (Arabic)',
        'type' => 'text|email|password|tel|textarea|select|file',  (default text)
        'value' => $model->name_ar ?? null, 'required' => true,
        'options' => [value => label] or [value => ['name' => label, 'data' => ['amount' => 10]]] (select), 'hint' => '...', 'attrs' => 'dir=rtl autofocus',
        'size' => 'lg' (login / register pages),
        'searchable' => true (select: type to search its options, template Tom Select, see custom.js initForms),
        'width' => 'short|medium|long|xlong|full' (instead of the width of its type), 'wrapperClass' => 'md:col-span-2' (the field box),
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
    if (!empty($width)) {
        $widthClass = $width == 'full' ? null : 'field-' . $width;
    }
    if ($widthClass) {
        $class .= ' ' . $widthClass;
    }
    // Tom Select builds its own box from the select, it gets only the width class
    if ($type == 'select' && !empty($searchable)) {
        $class = 'tom-select ' . $widthClass;
    }
    if ($errors->has($name)) {
        $class = str_replace(['border-slate-200', 'border-slate-300/80'], 'border-danger', $class);
    }
@endphp
<div data-field="{{ $name }}" @if (!empty($wrapperClass)) class="{{ $wrapperClass }}" @endif>
    <label for="{{ $id }}" class="{{ config('layoutmodule.form.label') }}">
        {{ $label }}
        @if (!empty($required))
            <span class="text-danger">*</span>
        @endif
    </label>

    @if ($type == 'textarea')
        <textarea name="{{ $name }}" id="{{ $id }}" rows="4" class="{{ $class }}" {!! $attrs ?? '' !!}>{{ $current }}</textarea>
    @elseif ($type == 'select')
        <select name="{{ $name }}" id="{{ $id }}" class="{{ $class }}" @if (!empty($searchable)) data-searchable @endif {!! $attrs ?? '' !!}>
            @foreach ($options as $optionValue => $optionLabel)
                {{-- an option can be ['name' => ..., 'data' => ['amount' => 10]] (data-amount="10") --}}
                <option value="{{ $optionValue }}" @selected((string) $current === (string) $optionValue)
                    @if (is_array($optionLabel)) @foreach ($optionLabel['data'] ?? [] as $dataKey => $dataValue) data-{{ $dataKey }}="{{ $dataValue }}" @endforeach @endif
                    >{{ Str::humanize(is_array($optionLabel) ? $optionLabel['name'] : $optionLabel) }}</option>
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
