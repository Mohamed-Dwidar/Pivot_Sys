<form method="POST" action="{{ route('account.members.destroy', $member->id) }}" class="inline-form"
    data-confirm="&quot;{{ $member->name }}&quot; will be deleted permanently."
    data-confirm-title="Delete this member?"
    data-confirm-button="Delete"
    data-confirm-variant="danger">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger {{ $size ?? '' }}" title="Delete">
        <i data-lucide="trash-2"></i> @if (empty($size)) Delete @endif
    </button>
</form>
