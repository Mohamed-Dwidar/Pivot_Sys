{{-- its payments are free again (they can be attached to another certificate) --}}
<form method="POST" action="{{ route('account.withholding-certificates.destroy', $certificate->id) }}" class="{{ !empty($menu) ? '' : 'inline-form' }}" data-ajax data-row="{{ $certificate->id }}"
    data-confirm="Certificate &quot;{{ $certificate->certificate_no }}&quot; will be deleted, its withholding payments stay (without a certificate)."
    data-confirm-title="Delete this certificate?"
    data-confirm-button="Delete"
    data-confirm-variant="danger">
    @csrf
    @method('DELETE')
    @if (!empty($menu))
        <button type="submit" data-tw-dismiss="dropdown" class="{{ config('layoutmodule.menu.item') }} text-danger">
            <i data-lucide="trash-2" class="{{ config('layoutmodule.menu.icon') }}"></i> Delete
        </button>
    @else
        <button type="submit" class="btn btn-danger"><i data-lucide="trash-2"></i> Delete</button>
    @endif
</form>
