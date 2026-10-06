<form method="POST" action="{{ route('admin.accounts.destroy', $account->id) }}" class="{{ !empty($menu) ? '' : 'inline-form' }}" data-ajax data-row="{{ $account->id }}"
    data-confirm="&quot;{{ $account->name }}&quot; will be deleted and can not log in. You can restore it later from the Deleted list."
    data-confirm-title="Delete this account?"
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
