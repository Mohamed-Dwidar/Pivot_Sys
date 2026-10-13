{{-- Activate / Deactivate in the row menu (with a confirmation), instead of a switch in every row; $package, $menu --}}
@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
<form method="POST" action="{{ route($area . '.packages.active', $package->id) }}" data-ajax data-row="{{ $package->id }}"
    data-confirm="{{ $package->is_active ? 'The package will be inactive: it is no longer offered in the reservation form.' : 'The package will be active again: it can be chosen in the reservation form.' }}"
    data-confirm-title="{{ $package->is_active ? 'Deactivate' : 'Activate' }} &quot;{{ $package->name }}&quot;?"
    data-confirm-button="{{ $package->is_active ? 'Deactivate' : 'Activate' }}"
    data-confirm-variant="{{ $package->is_active ? 'warning' : 'success' }}">
    @csrf
    @method('PATCH')
    <input type="hidden" name="is_active" value="{{ $package->is_active ? 0 : 1 }}">
    <button type="submit" data-tw-dismiss="dropdown" class="{{ config('layoutmodule.menu.item') }}">
        <i data-lucide="{{ $package->is_active ? 'circle-off' : 'check-circle' }}" class="{{ config('layoutmodule.menu.icon') }}"></i>
        {{ $package->is_active ? 'Deactivate' : 'Activate' }}
    </button>
</form>
