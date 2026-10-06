@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Member Details
@endsection

@section('modal-size', 'lg')

@section('actions')
    <a href="{{ route('account.members.edit', $member->id) }}" data-modal class="btn btn-secondary"><i data-lucide="pencil"></i> Edit</a>
    @include('membermodule::Account.partials.delete-form')
@endsection

@section('content')
    <div class="text-lg font-medium">{{ $member->name }}</div>

    <div class="form-section mt-6">Member Data</div>
    <dl class="detail-list">
        <div><dt>Phone</dt><dd>{{ $member->phone }}</dd></div>
        <div><dt>Email</dt><dd>{{ $member->email ?: '-' }}</dd></div>
        <div><dt>National Number</dt><dd>{{ $member->national_number ?: '-' }}</dd></div>
        <div><dt>Company</dt><dd>{{ $member->company?->name ?? '-' }}</dd></div>
        <div><dt>Job</dt><dd>{{ $member->job?->name ?? '-' }}</dd></div>
        <div><dt>Added At</dt><dd>{{ $member->created_at->format('Y-m-d H:i') }}</dd></div>
        <div class="wide"><dt>Source (from where)</dt><dd>{{ $member->from_where ?: '-' }}</dd></div>
        <div class="wide"><dt>Notes</dt><dd>{{ $member->notes ?: '-' }}</dd></div>
    </dl>

    @unless (request()->ajax())
        <div class="form-actions">
            <a href="{{ route('account.members.index') }}" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Back to Members</a>
        </div>
    @endunless
@endsection
