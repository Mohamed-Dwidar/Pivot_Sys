@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
<a href="{{ route($area . '.members.show', $member->id) }}" data-modal class="font-medium">{{ $member->name }}</a>
@if ($member->email)
    <div class="mt-0.5 text-xs text-slate-500">{{ $member->email }}</div>
@endif
