{{-- not for a category used by payments (the server refuses it too) --}}
<form method="POST" action="{{ route('account.income-categories.destroy', $category->id) }}" class="{{ !empty($menu) ? '' : 'inline-form' }}" data-ajax data-row="{{ $category->id }}"
    data-confirm="&quot;{{ $category->name }}&quot; will be deleted from your income categories list."
    data-confirm-title="Delete this income category?"
    data-confirm-button="Delete"
    data-confirm-variant="danger">
    @csrf
    @method('DELETE')
    @if (!empty($menu))
        <button type="submit" data-tw-dismiss="dropdown" class="{{ config('layoutmodule.menu.item') }} text-danger" title="{{ $category->payments_count ? 'Used by payments' : 'Delete' }}" @disabled($category->payments_count)>
            <i data-lucide="trash-2" class="{{ config('layoutmodule.menu.icon') }}"></i> Delete
        </button>
    @else
        <button type="submit" class="btn btn-danger" title="{{ $category->payments_count ? 'Used by payments' : 'Delete' }}" @disabled($category->payments_count)><i data-lucide="trash-2"></i> Delete</button>
    @endif
</form>
