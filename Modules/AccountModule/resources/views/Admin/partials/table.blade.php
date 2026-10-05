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
                            <span class="font-medium">{{ $account->name_ar }}</span>
                        @else
                            <a href="{{ route('admin.accounts.show', $account->id) }}" class="font-medium">{{ $account->name_ar }}</a>
                        @endif
                        @if ($account->name_en)
                            <div class="mt-0.5 text-xs text-slate-500">{{ $account->name_en }}</div>
                        @endif
                    </td>
                    <td>{{ $account->user?->email }}</td>
                    <td>{{ $account->phone }}</td>
                    <td>
                        @if ($account->trashed())
                            <span class="badge badge-rejected">Deleted {{ $account->deleted_at->format('Y-m-d') }}</span>
                        @else
                            @include('accountmodule::Admin.partials.status-badge')
                        @endif
                    </td>
                    <td class="whitespace-nowrap">{{ $account->created_at->format('Y-m-d') }}</td>
                    <td>
                        <div class="actions">
                            @if ($account->trashed())
                                <form method="POST" action="{{ route('admin.accounts.restore', $account->id) }}" class="inline-form"
                                    data-confirm="&quot;{{ $account->name_ar }}&quot; will be restored with its previous status ({{ $account->status_label }})."
                                    data-confirm-title="Restore this account?"
                                    data-confirm-button="Restore"
                                    data-confirm-variant="success">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm" title="Restore"><i data-lucide="rotate-ccw"></i> Restore</button>
                                </form>
                            @else
                                @include('accountmodule::Admin.partials.status-actions', ['size' => 'btn-sm'])
                                <a href="{{ route('admin.accounts.show', $account->id) }}" class="btn btn-secondary btn-sm" title="View"><i data-lucide="eye"></i></a>
                                <a href="{{ route('admin.accounts.edit', $account->id) }}" class="btn btn-secondary btn-sm" title="Edit"><i data-lucide="pencil"></i></a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty-state">No accounts found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
