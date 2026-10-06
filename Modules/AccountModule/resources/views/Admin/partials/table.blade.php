<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Account</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Registered</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($accounts as $account)
                <tr>
                    <td>
                        @if ($account->trashed())
                            <span class="font-medium">{{ $account->name }}</span>
                        @else
                            <a href="{{ route('admin.accounts.show', $account->id) }}" class="font-medium">{{ $account->name }}</a>
                        @endif
                    </td>
                    <td>{{ $account->user?->email }}</td>
                    <td>{{ $account->phone }}</td>
                    <td>@include('accountmodule::Admin.partials.row-status')</td>
                    <td class="whitespace-nowrap">{{ $account->created_at->format('Y-m-d') }}</td>
                    <td>@include('accountmodule::Admin.partials.row-actions')</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty-state">No accounts found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
