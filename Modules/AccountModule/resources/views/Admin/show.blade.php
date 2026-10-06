@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::admin.main')

@section('title')
    Account Details
@endsection

@section('modal-size', 'lg')

@section('actions')
    @include('accountmodule::Admin.partials.status-actions')
    <a href="{{ route('admin.accounts.edit', $account->id) }}" data-modal class="btn btn-secondary"><i data-lucide="pencil"></i> Edit</a>
    @include('accountmodule::Admin.partials.delete-form')
@endsection

@section('content')
    <div class="flex items-center gap-4">
        @include('accountmodule::Account.partials.logo')
        <div>
            <div class="text-lg font-medium">{{ $account->name }}</div>
            <div class="mt-2">@include('accountmodule::Admin.partials.status-badge')</div>
        </div>
    </div>

    <div class="form-section mt-8">Account Data</div>
    <dl class="detail-list">
        <div><dt>Phone</dt><dd>{{ $account->phone }}</dd></div>
        <div><dt>Address</dt><dd>{{ $account->address ?: '-' }}</dd></div>
        <div><dt>Profile Completion</dt><dd>{{ $account->profileCompletion() }}%</dd></div>
        <div class="wide"><dt>Description (Arabic)</dt><dd dir="rtl">{{ $account->description_ar ?: '-' }}</dd></div>
        <div class="wide"><dt>Description (English)</dt><dd>{{ $account->description_en ?: '-' }}</dd></div>
    </dl>

    <div class="form-section mt-8">Login Details</div>
    <dl class="detail-list">
        <div><dt>Email</dt><dd>{{ $account->user?->email }}</dd></div>
        <div><dt>Registered At</dt><dd>{{ $account->created_at->format('Y-m-d H:i') }}</dd></div>
        <div><dt>Approved At</dt><dd>{{ $account->approved_at?->format('Y-m-d H:i') ?? '-' }}</dd></div>
    </dl>
@endsection
