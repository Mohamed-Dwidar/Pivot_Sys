@extends('layoutmodule::account.main')

@section('title')
    Jobs
@endsection

@section('actions')
    <a href="{{ route('account.jobs.create') }}" class="btn btn-primary"><i data-lucide="plus"></i> Add Job</a>
@endsection

@section('content')
    <form method="GET" action="{{ route('account.jobs.index') }}" class="filter-bar">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="{{ config('layoutmodule.form.input') }}" placeholder="Search by name">
        <button type="submit" class="btn btn-primary"><i data-lucide="search"></i> Search</button>
        @if (!empty($filters['search']))
            <a href="{{ route('account.jobs.index') }}" class="btn btn-secondary">Clear</a>
        @endif
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Added</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobs as $job)
                    <tr>
                        <td>{{ $jobs->firstItem() + $loop->index }}</td>
                        <td>
                            <a href="{{ route('account.jobs.show', $job->id) }}" class="font-medium">{{ $job->name }}</a>
                        </td>
                        <td class="whitespace-nowrap">{{ $job->created_at->format('Y-m-d') }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('account.jobs.show', $job->id) }}" class="btn btn-secondary btn-sm" title="View"><i data-lucide="eye"></i></a>
                                <a href="{{ route('account.jobs.edit', $job->id) }}" class="btn btn-secondary btn-sm" title="Edit"><i data-lucide="pencil"></i></a>
                                @include('jobmodule::Account.partials.delete-form', ['size' => 'btn-sm'])
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-state">
                            No jobs yet.
                            <a href="{{ route('account.jobs.create') }}" class="text-primary">Add your first job</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $jobs->links('layoutmodule::pagination') }}
@endsection
