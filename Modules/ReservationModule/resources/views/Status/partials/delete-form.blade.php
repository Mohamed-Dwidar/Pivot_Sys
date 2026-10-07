{{-- not for the default status or a status used by reservations (the server refuses it too) --}}
<form method="POST" action="{{ route('account.reservation-statuses.destroy', $status->id) }}" class="{{ !empty($menu) ? '' : 'inline-form' }}" data-ajax data-row="{{ $status->id }}"
    data-confirm="&quot;{{ $status->name }}&quot; will be deleted from your statuses list."
    data-confirm-title="Delete this status?"
    data-confirm-button="Delete"
    data-confirm-variant="danger">
    @csrf
    @method('DELETE')
    @if (!empty($menu))
        <button type="submit" data-tw-dismiss="dropdown" class="{{ config('layoutmodule.menu.item') }} text-danger" title="{{ $status->is_default ? 'The default status' : ($status->reservations_count ? 'Used by reservations' : 'Delete') }}" @disabled($status->is_default || $status->reservations_count)>
            <i data-lucide="trash-2" class="{{ config('layoutmodule.menu.icon') }}"></i> Delete
        </button>
    @else
        <button type="submit" class="btn btn-danger" title="{{ $status->is_default ? 'The default status' : ($status->reservations_count ? 'Used by reservations' : 'Delete') }}" @disabled($status->is_default || $status->reservations_count)><i data-lucide="trash-2"></i> Delete</button>
    @endif
</form>
