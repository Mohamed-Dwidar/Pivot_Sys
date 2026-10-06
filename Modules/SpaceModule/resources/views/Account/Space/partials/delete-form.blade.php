<form method="POST" action="{{ route('account.spaces.destroy', $space->id) }}" class="inline-form"
    data-confirm="&quot;{{ $space->name }}&quot; and its units will be deleted."
    data-confirm-title="Delete this space?"
    data-confirm-button="Delete"
    data-confirm-variant="danger">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger {{ $size ?? '' }}" title="Delete">
        <i data-lucide="trash-2"></i> @if (empty($size)) Delete @endif
    </button>
</form>
