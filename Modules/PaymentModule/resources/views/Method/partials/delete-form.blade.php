{{-- not for a method used by payments (the server refuses it too) --}}
<form method="POST" action="{{ route('account.payment-methods.destroy', $method->id) }}" class="{{ !empty($menu) ? '' : 'inline-form' }}" data-ajax data-row="{{ $method->id }}"
    data-confirm="&quot;{{ $method->name }}&quot; will be deleted from your payment methods list."
    data-confirm-title="Delete this payment method?"
    data-confirm-button="Delete"
    data-confirm-variant="danger">
    @csrf
    @method('DELETE')
    @if (!empty($menu))
        <button type="submit" data-tw-dismiss="dropdown" class="{{ config('layoutmodule.menu.item') }} text-danger" title="{{ $method->payments_count ? 'Used by cash in' : 'Delete' }}" @disabled($method->payments_count)>
            <i data-lucide="trash-2" class="{{ config('layoutmodule.menu.icon') }}"></i> Delete
        </button>
    @else
        <button type="submit" class="btn btn-danger" title="{{ $method->payments_count ? 'Used by cash in' : 'Delete' }}" @disabled($method->payments_count)><i data-lucide="trash-2"></i> Delete</button>
    @endif
</form>
