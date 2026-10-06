<form method="POST" action="{{ route('account.spaces.destroy', $space->id) }}" class="{{ !empty($menu) ? '' : 'inline-form' }}" data-ajax data-row="{{ $space->id }}"
    data-confirm="&quot;{{ $space->name }}&quot; and its units will be deleted."
    data-confirm-title="Delete this space?"
    data-confirm-button="Delete"
    data-confirm-variant="danger">
    @csrf
    @method('DELETE')
    @if (!empty($menu))
        <button type="submit" data-tw-dismiss="dropdown" class="{{ config('layoutmodule.menu.item') }} text-danger" title="Delete">
            <i data-lucide="trash-2" class="{{ config('layoutmodule.menu.icon') }}"></i> Delete
        </button>
    @else
        <button type="submit" class="btn btn-danger {{ $size ?? '' }}" title="Delete">
            <i data-lucide="trash-2"></i> @if (empty($size)) Delete @endif
        </button>
    @endif
</form>
