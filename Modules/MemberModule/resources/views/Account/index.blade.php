@extends('layoutmodule::account.main')

@section('title')
    Members
@endsection

@section('actions')
    <a href="{{ route('account.members.create') }}" class="btn btn-primary"><i data-lucide="plus"></i> Add Member</a>
@endsection

@section('content')
    <form method="GET" action="{{ route('account.members.index') }}" class="filter-bar">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="{{ config('layoutmodule.form.input') }}" placeholder="Name, phone, email or national number">
        <select name="company_id" class="{{ config('layoutmodule.form.select') }}" aria-label="Company">
            <option value="">All Companies</option>
            @foreach ($companies as $id => $name)
                <option value="{{ $id }}" @selected(($filters['company_id'] ?? '') == $id)>{{ $name }}</option>
            @endforeach
        </select>
        <select name="job_id" class="{{ config('layoutmodule.form.select') }}" aria-label="Job">
            <option value="">All Jobs</option>
            @foreach ($jobs as $id => $name)
                <option value="{{ $id }}" @selected(($filters['job_id'] ?? '') == $id)>{{ $name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-dark"><i data-lucide="search"></i> Search</button>
        @if (array_filter($filters))
            <a href="{{ route('account.members.index') }}" class="btn btn-secondary">Clear</a>
        @endif
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Company</th>
                    <th>Job</th>
                    <th>Added</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $member)
                    <tr>
                        <td>{{ $members->firstItem() + $loop->index }}</td>
                        <td>
                            <a href="{{ route('account.members.show', $member->id) }}" class="font-medium">{{ $member->name }}</a>
                            @if ($member->email)
                                <div class="mt-0.5 text-xs text-slate-500">{{ $member->email }}</div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap">{{ $member->phone }}</td>
                        <td>{{ $member->company?->name_ar ?? '-' }}</td>
                        <td>{{ $member->job?->name_ar ?? '-' }}</td>
                        <td class="whitespace-nowrap">{{ $member->created_at->format('Y-m-d') }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('account.members.show', $member->id) }}" class="btn btn-secondary btn-sm" title="View"><i data-lucide="eye"></i></a>
                                <a href="{{ route('account.members.edit', $member->id) }}" class="btn btn-secondary btn-sm" title="Edit"><i data-lucide="pencil"></i></a>
                                @include('membermodule::Account.partials.delete-form', ['size' => 'btn-sm'])
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            No members found.
                            <a href="{{ route('account.members.create') }}" class="text-primary">Add a member</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $members->links('layoutmodule::pagination') }}
@endsection
