{{-- Approve / reject / activate / deactivate buttons for the current status, each asks for confirmation. --}}
@php
    // [new status, label, button class, icon, confirm variant, confirm message]
    $approve = ['active', 'Approve', 'btn-success', 'check', 'success', 'will be able to log in to the system.'];
    $actions = [
        'pending' => [$approve, ['rejected', 'Reject', 'btn-danger', 'x', 'danger', 'will not be able to log in to the system.']],
        'active' => [['inactive', 'Deactivate', 'btn-warning', 'pause-circle', 'warning', 'will be logged out and can not log in until it is activated again.']],
        'inactive' => [['active', 'Activate', 'btn-success', 'play-circle', 'success', 'will be able to log in to the system again.']],
        'rejected' => [$approve],
    ][$account->status] ?? [];
@endphp
@foreach ($actions as [$newStatus, $actionLabel, $btnClass, $icon, $variant, $confirmText])
    <form method="POST" action="{{ route('admin.accounts.status', $account->id) }}" class="inline-form"
        data-confirm="&quot;{{ $account->name }}&quot; {{ $confirmText }}"
        data-confirm-title="{{ $actionLabel }} this account?"
        data-confirm-button="{{ $actionLabel }}"
        data-confirm-variant="{{ $variant }}">
        @csrf
        @method('PATCH')
        <input type="hidden" name="status" value="{{ $newStatus }}">
        <button type="submit" class="btn {{ $btnClass }} {{ $size ?? '' }}" title="{{ $actionLabel }}">
            <i data-lucide="{{ $icon }}"></i> {{ $actionLabel }}
        </button>
    </form>
@endforeach
