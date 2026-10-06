<a href="{{ route('account.members.show', $member->id) }}" class="font-medium">{{ $member->name }}</a>
@if ($member->email)
    <div class="mt-0.5 text-xs text-slate-500">{{ $member->email }}</div>
@endif
