@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
<form method="POST" action="{{ route($area . '.reservations.destroy', $reservation->id) }}" class="{{ !empty($menu) ? '' : 'inline-form' }}" data-ajax data-row="{{ $reservation->id }}"
    data-confirm="The reservation of &quot;{{ $reservation->member?->name }}&quot; ({{ $reservation->period }}) will be deleted."
    data-confirm-title="Delete this reservation?"
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
