{{-- the list Package column: the name (opens the package view, popup), the member name under it (small) --}}
@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
<a href="{{ route($area . '.packages.show', $package->id) }}" data-modal class="font-medium">{{ $package->name }}</a>
<div class="mt-0.5 text-xs text-slate-500">{{ $package->member?->name ?? '-' }}</div>
