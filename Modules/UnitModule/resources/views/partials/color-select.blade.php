{{--
    Colors drop menu (template dropdown): each option shows the color + its name.
    Sends color_id. $colors = [id => name], $colorValues = [id => hex], $selected = id|null
--}}
@php
    $selected = (string) old('color_id', $selected ?? '');
    // the template moves the open menu to <body>, so the options find the button by this id
    $currentId = 'color-select-current-' . uniqid();
    $toggleClass = config('layoutmodule.form.select') . ($errors->has('color_id') ? ' border-danger' : '');
    $toggleClass = $errors->has('color_id') ? str_replace('border-slate-200', '', $toggleClass) : $toggleClass;
@endphp
<div data-field="color_id">
    <label class="{{ config('layoutmodule.form.label') }}">Color <span class="text-danger">*</span></label>

    @if ($colors)
        <div data-tw-merge="" data-tw-placement="bottom-start" class="dropdown relative color-select field-medium">
            <button type="button" data-tw-toggle="dropdown" aria-expanded="false" class="{{ $toggleClass }} color-select__toggle">
                <span class="color-select__current" id="{{ $currentId }}">
                    @if ($selected !== '' && isset($colors[$selected]))
                        @include('unitmodule::partials.color-swatch', ['value' => $colorValues[$selected]])
                        {{ $colors[$selected] }}
                    @else
                        <span class="text-slate-400">Select a color</span>
                    @endif
                </span>
            </button>
            <div class="dropdown-menu js-menu absolute z-[9999]">
                <div data-tw-merge="" class="dropdown-content rounded-md border-transparent bg-white p-2 shadow-[0px_3px_10px_#00000017] dark:border-transparent dark:bg-darkmode-600 color-select__menu">
                    @foreach ($colors as $colorId => $colorName)
                        <label data-tw-dismiss="dropdown" data-current="{{ $currentId }}" class="color-select__option cursor-pointer flex items-center gap-2 p-2 transition duration-300 ease-in-out rounded-md hover:bg-slate-200/60">
                            <input type="radio" name="color_id" value="{{ $colorId }}" class="color-select__radio" @checked($selected === (string) $colorId)>
                            @include('unitmodule::partials.color-swatch', ['value' => $colorValues[$colorId]])
                            <span>{{ $colorName }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="text-slate-500">No colors yet, please ask the administrator to add colors.</div>
    @endif

    @error('color_id')
        <div class="{{ config('layoutmodule.form.error') }}">{{ $message }}</div>
    @enderror
</div>
