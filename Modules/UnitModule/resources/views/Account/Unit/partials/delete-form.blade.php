<form method="POST" action="{{ route('account.spaces.units.destroy', [$unit->space_id, $unit->id]) }}" class="{{ !empty($menu) ? '' : 'inline-form' }}" data-ajax data-row="{{ $unit->id }}"
    data-confirm="&quot;{{ $unit->name }}&quot; will be deleted."
    data-confirm-title="Delete this unit?"
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
