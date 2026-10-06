<form method="POST" action="{{ route('account.subscription-types.destroy', $subscriptionType->id) }}" class="inline-form"
    data-confirm="&quot;{{ $subscriptionType->name }}&quot; will be deleted and removed from {{ $subscriptionType->spaces_count }} space(s)."
    data-confirm-title="Delete this subscription type?"
    data-confirm-button="Delete"
    data-confirm-variant="danger">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger {{ $size ?? '' }}" title="Delete">
        <i data-lucide="trash-2"></i> @if (empty($size)) Delete @endif
    </button>
</form>
