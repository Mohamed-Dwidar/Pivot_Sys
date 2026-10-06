<form method="POST" action="{{ route('account.plans.destroy', $plan->id) }}" class="{{ !empty($menu) ? '' : 'inline-form' }}" data-ajax data-row="{{ $plan->id }}"
    data-confirm="&quot;{{ $plan->name }}&quot; will be deleted and removed from its spaces and units."
    data-confirm-title="Delete this plan?"
    data-confirm-button="Delete"
    data-confirm-variant="danger">
    @csrf
    @method('DELETE')
    @if (!empty($menu))
        <button type="submit" data-tw-dismiss="dropdown" class="{{ config('layoutmodule.menu.item') }} text-danger" title="Delete">
            <i data-lucide="trash-2" class="{{ config('layoutmodule.menu.icon') }}"></i> Delete
        </button>
    @else
        <button type="submit" class="btn btn-danger" title="Delete"><i data-lucide="trash-2"></i> Delete</button>
    @endif
</form>
