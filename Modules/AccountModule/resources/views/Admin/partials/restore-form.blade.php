<form method="POST" action="{{ route('admin.accounts.restore', $account->id) }}"
    data-confirm="&quot;{{ $account->name }}&quot; will be restored with its previous status ({{ $account->status_label }})."
    data-confirm-title="Restore this account?"
    data-confirm-button="Restore"
    data-confirm-variant="success">
    @csrf
    @method('PATCH')
    <button type="submit" data-tw-dismiss="dropdown" class="{{ config('layoutmodule.menu.item') }} text-success">
        <i data-lucide="rotate-ccw" class="{{ config('layoutmodule.menu.icon') }}"></i> Restore
    </button>
</form>
