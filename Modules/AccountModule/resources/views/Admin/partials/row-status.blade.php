{{-- Status column: active / inactive accounts get the switch (ajax), the others their badge. --}}
@if ($account->trashed())
    <span class="badge badge-rejected">Deleted {{ $account->deleted_at->format('Y-m-d') }}</span>
@elseif (in_array($account->status, [\Modules\AccountModule\app\Models\Account::STATUS_ACTIVE, \Modules\AccountModule\app\Models\Account::STATUS_INACTIVE]))
    @include('layoutmodule::partials.row-toggle', [
        'url' => route('admin.accounts.status', $account->id), 'row' => $account->id,
        'name' => 'status', 'checked' => $account->status == \Modules\AccountModule\app\Models\Account::STATUS_ACTIVE,
        'on' => \Modules\AccountModule\app\Models\Account::STATUS_ACTIVE, 'off' => \Modules\AccountModule\app\Models\Account::STATUS_INACTIVE,
    ])
@else
    @include('accountmodule::Admin.partials.status-badge')
@endif
