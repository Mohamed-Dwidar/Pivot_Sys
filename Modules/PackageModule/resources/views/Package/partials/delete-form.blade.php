@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
<form method="POST" action="{{ route($area . '.packages.destroy', $package->id) }}" class="{{ !empty($menu) ? '' : 'inline-form' }}" data-ajax data-row="{{ $package->id }}"
    data-confirm="&quot;{{ $package->name }}&quot; will be deleted."
    data-confirm-title="Delete this package?"
    data-confirm-button="Delete"
    data-confirm-variant="danger">
    @csrf
    @method('DELETE')
    @if (!empty($menu))
        <button type="submit" data-tw-dismiss="dropdown" class="{{ config('layoutmodule.menu.item') }} text-danger" title="{{ $package->reservations_count ? 'Has reservations' : 'Delete' }}" @disabled($package->reservations_count)>
            <i data-lucide="trash-2" class="{{ config('layoutmodule.menu.icon') }}"></i> Delete
        </button>
    @else
        <button type="submit" class="btn btn-danger" title="{{ $package->reservations_count ? 'Has reservations' : 'Delete' }}" @disabled($package->reservations_count)><i data-lucide="trash-2"></i> Delete</button>
    @endif
</form>
