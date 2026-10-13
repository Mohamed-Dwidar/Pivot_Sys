{{-- the list Member column: the name (opens the reservation view, popup), the phone under it, the package as a small line (opens the package view) --}}
@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
<a href="{{ route($area . '.reservations.show', $reservation->id) }}" data-modal class="font-medium">{{ $reservation->member?->name ?? '-' }}</a>
@if ($reservation->member?->phone)
    <div class="mt-0.5 text-xs text-slate-500">{{ $reservation->member->phone }}</div>
@endif
@if ($reservation->package)
    <div class="mt-0.5 text-xs"><a href="{{ route($area . '.packages.show', $reservation->package_id) }}" data-modal title="Open the package"><i data-lucide="package" class="inline h-3 w-3"></i> {{ $reservation->package->name }}</a></div>
@endif
