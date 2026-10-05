<form method="POST" action="{{ route('account.jobs.destroy', $job->id) }}" class="inline-form"
    data-confirm="&quot;{{ $job->name_ar }}&quot; will be deleted from your jobs list."
    data-confirm-title="Delete this job?"
    data-confirm-button="Delete"
    data-confirm-variant="danger">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger {{ $size ?? '' }}" title="Delete">
        <i data-lucide="trash-2"></i> @if (empty($size)) Delete @endif
    </button>
</form>
