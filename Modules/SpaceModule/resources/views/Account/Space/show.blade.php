{{-- Full page: the space + its units. In the popup (View): the space details only. --}}
@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Space Details
@endsection

@section('modal-size', 'lg')

@section('actions')
    @if (request()->ajax())
        <a href="{{ route('account.spaces.show', $space->id) }}" class="btn btn-secondary"><i data-lucide="layout-grid"></i> Units</a>
    @endif
    <a href="{{ route('account.spaces.edit', $space->id) }}" data-modal class="btn btn-secondary"><i data-lucide="pencil"></i> Edit</a>
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
                <a href="{{ route('account.subscription-types.show', $type->id) }}" data-modal class="badge badge-inactive mr-1">{{ $type->name }}</a>
            @empty
                <div class="text-slate-500">No subscription types assigned.</div>
            @endforelse

            <div class="form-section mt-6">Plans</div>
            @forelse ($space->plans as $plan)
                <a href="{{ route('account.plans.show', $plan->id) }}" data-modal class="badge badge-inactive mr-1">{{ $plan->name }}</a>
            @empty
                <div class="text-slate-500">No plans assigned.</div>
            @endforelse
        </div>

        {{-- images (right side) --}}
        <div class="col-span-12 lg:col-span-4">
            <div class="form-section">Images ({{ $space->images->count() }})</div>
            @include('layoutmodule::partials.gallery', ['images' => $space->images, 'alt' => $space->name])
        </div>
    </div>

    @unless (request()->ajax())
        @include('unitmodule::Account.Unit.partials.list')

        <div class="form-actions">
            <a href="{{ route('account.spaces.index') }}" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Back to Spaces</a>
        </div>
    @endunless
@endsection
