<form method="POST" action="{{ route('account.spaces.units.destroy', [$unit->space_id, $unit->id]) }}" class="inline-form"
    data-confirm="&quot;{{ $unit->name }}&quot; will be deleted."
    data-confirm-title="Delete this unit?"
    data-confirm-button="Delete"
    data-confirm-variant="danger">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger {{ $size ?? '' }}" title="Delete">
        <i data-lucide="trash-2"></i> @if (empty($size)) Delete @endif
    </button>
</form>
