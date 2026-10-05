@extends('layoutmodule::account.main')

@section('title')
    Companies
@endsection

@section('actions')
    <a href="{{ route('account.companies.create') }}" class="btn btn-primary"><i data-lucide="plus"></i> Add Company</a>
@endsection

@section('content')
    <form method="GET" action="{{ route('account.companies.index') }}" class="filter-bar">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="{{ config('layoutmodule.form.input') }}" placeholder="Search by name">
        <button type="submit" class="btn btn-primary"><i data-lucide="search"></i> Search</button>
        @if (!empty($filters['search']))
            <a href="{{ route('account.companies.index') }}" class="btn btn-secondary">Clear</a>
        @endif
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name (Arabic)</th>
                    <th>Name (English)</th>
                    <th>Added</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($companies as $company)
                    <tr>
                        <td>{{ $companies->firstItem() + $loop->index }}</td>
                        <td>
                            <a href="{{ route('account.companies.show', $company->id) }}" class="font-medium">{{ $company->name_ar }}</a>
                        </td>
                        <td>{{ $company->name_en ?: '-' }}</td>
                        <td class="whitespace-nowrap">{{ $company->created_at->format('Y-m-d') }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('account.companies.show', $company->id) }}" class="btn btn-secondary btn-sm" title="View"><i data-lucide="eye"></i></a>
                                <a href="{{ route('account.companies.edit', $company->id) }}" class="btn btn-secondary btn-sm" title="Edit"><i data-lucide="pencil"></i></a>
                                @include('companymodule::Account.partials.delete-form', ['size' => 'btn-sm'])
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            No companies yet.
                            <a href="{{ route('account.companies.create') }}" class="text-primary">Add your first company</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $companies->links('layoutmodule::pagination') }}
@endsection
