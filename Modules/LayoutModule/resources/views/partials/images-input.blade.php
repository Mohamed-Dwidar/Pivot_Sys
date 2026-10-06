{{--
    Multiple images upload + "Remove" checkboxes for the saved images.
    Sends images[] (new files) and delete_images[] (ids to remove).
    @include('layoutmodule::partials.images-input', ['images' => $model?->images, 'alt' => $model?->name])
    Each image needs ->id and ->url.
--}}
@if ($images && $images->isNotEmpty())
    <div class="image-grid mb-5">
        @foreach ($images as $image)
            <label class="image-grid__item">
                <img src="{{ $image->url }}" alt="{{ $alt ?? '' }}">
                <span class="image-grid__remove">
                    <input type="checkbox" name="delete_images[]" value="{{ $image->id }}" class="{{ config('layoutmodule.form.checkbox') }}"
                        @checked(in_array($image->id, old('delete_images', [])))>
                    Remove
                </span>
            </label>
        @endforeach
    </div>
@endif
<div>
    <label for="field-images" class="{{ config('layoutmodule.form.label') }}">{{ $images && $images->isNotEmpty() ? 'Add Images' : 'Images' }}</label>
    <input type="file" name="images[]" id="field-images" multiple accept="image/*"
        class="field-long {{ str_replace('border-slate-200', $errors->has('images') || $errors->has('images.*') ? 'border-danger' : 'border-slate-200', config('layoutmodule.form.input')) }}">
    <div class="{{ config('layoutmodule.form.hint') }}">You can select several images (up to 10 at once, max 2 MB each).</div>
    @foreach (array_merge($errors->get('images'), ...array_values($errors->get('images.*'))) as $message)
        <div class="{{ config('layoutmodule.form.error') }}">{{ $message }}</div>
    @endforeach
</div>
