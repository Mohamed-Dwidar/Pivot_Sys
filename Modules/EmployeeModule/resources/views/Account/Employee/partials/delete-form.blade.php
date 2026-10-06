<form method="POST" action="{{ route('account.employees.destroy', $employee->id) }}" class="{{ !empty($menu) ? '' : 'inline-form' }}" data-ajax data-row="{{ $employee->id }}"
    data-confirm="&quot;{{ $employee->name }}&quot; will be deleted and his login removed, he will not be able to log in."
    data-confirm-title="Delete this employee?"
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
