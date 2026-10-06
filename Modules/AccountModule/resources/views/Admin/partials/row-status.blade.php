@if ($account->trashed())
    <span class="badge badge-rejected">Deleted {{ $account->deleted_at->format('Y-m-d') }}</span>
@else
    @include('accountmodule::Admin.partials.status-badge')
@endif
