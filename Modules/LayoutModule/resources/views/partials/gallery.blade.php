{{--
    Small thumbnails + a viewer (template modal) to navigate between the images.
    @include('layoutmodule::partials.gallery', ['images' => $model->images, 'alt' => $model->name])
    Each image needs ->url. Navigation: arrows, the thumbnails strip, keyboard left / right, Esc to close.
--}}
@php($galleryId = 'gallery-' . uniqid())
@if ($images->isNotEmpty())
    <div class="gallery-thumbs">
        @foreach ($images as $image)
            <button type="button" class="gallery-thumbs__item" data-gallery-open="{{ $galleryId }}" data-index="{{ $loop->index }}" title="View image {{ $loop->iteration }}">
                <img src="{{ $image->url }}" alt="{{ $alt ?? '' }}">
            </button>
        @endforeach
    </div>

    <div id="{{ $galleryId }}" data-tw-backdrop="" aria-hidden="true" tabindex="-1" class="modal gallery-modal">
        <div class="gallery-modal__dialog">
            <div class="gallery-modal__top">
                <span class="gallery-modal__counter"></span>
                <button type="button" class="gallery-modal__close" data-tw-dismiss="modal" title="Close"><i data-lucide="x"></i></button>
            </div>

            <div class="gallery-modal__stage">
                <button type="button" class="gallery-modal__nav gallery-modal__nav--prev" data-gallery-step="-1" title="Previous"><i data-lucide="chevron-left"></i></button>
                <img class="gallery-modal__image" src="" alt="{{ $alt ?? '' }}">
                <button type="button" class="gallery-modal__nav gallery-modal__nav--next" data-gallery-step="1" title="Next"><i data-lucide="chevron-right"></i></button>
            </div>

            <div class="gallery-modal__strip">
                @foreach ($images as $image)
                    <button type="button" class="gallery-modal__strip-item" data-gallery-go="{{ $loop->index }}" data-src="{{ $image->url }}">
                        <img src="{{ $image->url }}" alt="{{ $alt ?? '' }}">
                    </button>
                @endforeach
            </div>
        </div>
    </div>
@else
    <div class="text-slate-500">No images yet.</div>
@endif
