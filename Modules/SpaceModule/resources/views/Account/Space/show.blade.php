@extends('layoutmodule::account.main')

@section('title')
    Space Details
@endsection

@section('actions')
    <a href="{{ route('account.spaces.edit', $space->id) }}" class="btn btn-secondary"><i data-lucide="pencil"></i> Edit</a>
    @include('spacemodule::Account.Space.partials.delete-form')
@endsection

@section('content')
    <div class="grid grid-cols-12 gap-6">
        {{-- details --}}
        <div class="col-span-12 lg:col-span-8">
            <div class="flex items-center gap-3">
                <div class="text-lg font-medium">{{ $space->name }}</div>
                @include('spacemodule::Account.Space.partials.status-badge')
            </div>

            <div class="form-section mt-6">Subscription Types</div>
            @forelse ($space->subscriptionTypes as $type)
                <a href="{{ route('account.subscription-types.show', $type->id) }}" class="badge badge-inactive mr-1">{{ $type->name }}</a>
            @empty
                <div class="text-slate-500">No subscription types assigned.</div>
            @endforelse
        </div>

        {{-- images (right side) --}}
        <div class="col-span-12 lg:col-span-4">
            <div class="form-section">Images ({{ $space->images->count() }})</div>
            @include('layoutmodule::partials.gallery', ['images' => $space->images, 'alt' => $space->name])
        </div>
    </div>

    @include('unitmodule::Account.Unit.partials.list', ['units' => $units])

    <div class="form-actions">
        <a href="{{ route('account.spaces.index') }}" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Back to Spaces</a>
    </div>
@endsection
