<form method="POST" action="{{ route('account.companies.destroy', $company->id) }}" class="inline-form"
    data-confirm="&quot;{{ $company->name_ar }}&quot; will be deleted from your companies list."
    data-confirm-title="Delete this company?"
    data-confirm-button="Delete"
    data-confirm-variant="danger">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger {{ $size ?? '' }}" title="Delete">
        <i data-lucide="trash-2"></i> @if (empty($size)) Delete @endif
    </button>
</form>
