{{-- the member name opens the reservation view (popup) --}}
@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
<a href="{{ route($area . '.reservations.show', $reservation->id) }}" data-modal class="font-medium">{{ $reservation->member?->name ?? '-' }}</a>
<div class="mt-0.5 text-xs text-slate-500">
    {{ $reservation->member?->phone }}
    @if ($reservation->package)
        &middot; <i data-lucide="package" class="inline h-3 w-3"></i> {{ $reservation->package->name }}
    @endif
</div>
