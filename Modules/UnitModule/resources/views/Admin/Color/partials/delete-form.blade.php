<form method="POST" action="{{ route('admin.colors.destroy', $color->id) }}" class="{{ !empty($menu) ? '' : 'inline-form' }}" data-ajax data-row="{{ $color->id }}"
    data-confirm="&quot;{{ $color->name }}&quot; will be deleted permanently."
    data-confirm-title="Delete this color?"
    data-confirm-button="Delete"
    data-confirm-variant="danger">
    @csrf
    @method('DELETE')
    @if (!empty($menu))
        <button type="submit" data-tw-dismiss="dropdown" class="{{ config('layoutmodule.menu.item') }} text-danger" title="{{ $color->units_count ? 'Used by units' : 'Delete' }}" @disabled($color->units_count)>
            <i data-lucide="trash-2" class="{{ config('layoutmodule.menu.icon') }}"></i> Delete
        </button>
    @else
        <button type="submit" class="btn btn-danger {{ $size ?? '' }}" title="{{ $color->units_count ? 'Used by units' : 'Delete' }}" @disabled($color->units_count)>
            <i data-lucide="trash-2"></i> @if (empty($size)) Delete @endif
        </button>
    @endif
</form>
