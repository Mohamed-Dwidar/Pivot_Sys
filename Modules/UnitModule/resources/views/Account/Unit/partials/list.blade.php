{{-- Units of $space, shown on the space page. --}}
<div class="flex items-center mt-8 mb-4">
    <div class="text-base font-medium">Units ({{ $units->count() }})</div>
    <a href="{{ route('account.spaces.units.create', $space->id) }}" class="btn btn-primary btn-sm ml-auto"><i data-lucide="plus"></i> Add Unit</a>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th></th>
                <th>Name</th>
                <th>Subscription Type</th>
                <th>Capacity</th>
                <th>Concurrent</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($units as $unit)
                <tr>
                    <td>
                        @if ($unit->images->isNotEmpty())
                            <img src="{{ $unit->images->first()->url }}" alt="{{ $unit->name }}" class="table-thumb" data-action="zoom">
                        @else
                            <div class="table-thumb table-thumb--empty"><i data-lucide="image"></i></div>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            @if ($unit->color)
                                @include('unitmodule::partials.color-swatch', ['value' => $unit->color->value])
                            @endif
                            <a href="{{ route('account.spaces.units.show', [$space->id, $unit->id]) }}" class="font-medium">{{ $unit->name }}</a>
                        </div>
                    </td>
                    <td>{{ $unit->subscriptionType?->name ?? '-' }}</td>
                    <td>{{ $unit->capacity ?: '-' }}</td>
                    <td>{{ $unit->concurrent_usage }}</td>
                    <td><span class="badge {{ $unit->is_active ? 'badge-active' : 'badge-inactive' }}">{{ $unit->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('account.spaces.units.show', [$space->id, $unit->id]) }}" class="btn btn-secondary btn-sm" title="View"><i data-lucide="eye"></i></a>
                            <a href="{{ route('account.spaces.units.edit', [$space->id, $unit->id]) }}" class="btn btn-secondary btn-sm" title="Edit"><i data-lucide="pencil"></i></a>
                            @include('unitmodule::Account.Unit.partials.delete-form', ['size' => 'btn-sm'])
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty-state">
                        No units in this space yet.
                        <a href="{{ route('account.spaces.units.create', $space->id) }}" class="text-primary">Add the first unit</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
