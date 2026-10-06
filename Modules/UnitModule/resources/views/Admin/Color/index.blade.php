@extends('layoutmodule::admin.main')

@section('title')
    Colors
@endsection

@section('actions')
    <a href="{{ route('admin.colors.create') }}" class="btn btn-primary"><i data-lucide="plus"></i> Add Color</a>
@endsection

@section('content')
    <form method="GET" action="{{ route('admin.colors.index') }}" class="filter-bar">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="{{ config('layoutmodule.form.input') }}" placeholder="Search by name">
        <button type="submit" class="btn btn-primary"><i data-lucide="search"></i> Search</button>
        @if (!empty($filters['search']))
            <a href="{{ route('admin.colors.index') }}" class="btn btn-secondary">Clear</a>
        @endif
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Color</th>
                    <th>Value</th>
                    <th>Used by Units</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($colors as $color)
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                @include('unitmodule::partials.color-swatch', ['value' => $color->value])
                                <span class="font-medium">{{ $color->name }}</span>
                            </div>
                        </td>
                        <td>{{ $color->value }}</td>
                        <td>{{ $color->units_count }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('admin.colors.edit', $color->id) }}" class="btn btn-secondary btn-sm" title="Edit"><i data-lucide="pencil"></i></a>
                                @include('unitmodule::Admin.Color.partials.delete-form', ['size' => 'btn-sm'])
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-state">
                            No colors found.
                            <a href="{{ route('admin.colors.create') }}" class="text-primary">Add a color</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $colors->links('layoutmodule::pagination') }}
@endsection
