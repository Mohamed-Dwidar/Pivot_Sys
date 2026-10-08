@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

@section('title')
    <div class="text-lg font-medium">{{ $package->name }}</div>
    {{-- <span
            class="badge {{ $package->is_active ? 'badge-active' : 'badge-inactive' }}">{{ $package->is_active ? 'Active' : 'Inactive' }}</span> --}}
@endsection

@section('modal-size', 'lg')

@section('actions')
    <a href="{{ route($area . '.packages.edit', $package->id) }}" data-modal class="btn btn-secondary"><i
            data-lucide="pencil"></i> Edit</a>
    @include('packagemodule::Package.partials.delete-form')
@endsection

@section('content')
    {{-- left: the member name + phone (smaller); right: the package net (green badge) --}}
    <div class="flex flex-wrap items-center gap-4">
        <div>
            <div class="text-lg font-medium">
                {{ $package->member ? $package->member->name : '-' }}
            </div>
            <div class="mt-1 text-sm text-slate-500">
                {{ $package->member ? $package->member->phone : '-' }}
            </div>
        </div>
        <span class="badge badge-active package-total ml-auto"
            title="Package net (the price - the discount)">{{ number_format($package->after_discount, 2) }}</span>
    </div>

    <div class="form-section mt-6">Package Data</div>
    <dl class="detail-list">
        <div>
            <dt>Period</dt>
            <dd>{{ $package->period }}</dd>
        </div>
        <div>
            <dt>Price <span class="text-slate-400">(reservations net)</span></dt>
            <dd class="font-medium text-success">{{ number_format($package->amount, 2) }}</dd>
        </div>
        <div>
            <dt>Discount</dt>
            <dd>{{ rtrim(rtrim(number_format($package->discount_percentage, 2), '0'), '.') }}%</dd>
        </div>
        <div>
            <dt>Net</dt>
            <dd class="font-medium text-success">{{ number_format($package->after_discount, 2) }}</dd>
        </div>
        @php($hours = $package->reservationsHours())
        <div>
            <dt>Total Hours</dt>
            <dd>
                {{ rtrim(rtrim(number_format($hours['hours'], 2), '0'), '.') }}
                {{ $hours['hours'] == 1 ? 'hour' : 'hours' }}
                @if ($hours['open'])
                    <span class="text-xs text-slate-500">(+ {{ $hours['open'] }} continuous reservation(s), not
                        counted)</span>
                @endif
            </dd>
        </div>
        <div>
            <dt>Added At</dt>
            <dd>{{ $package->created_at->format('Y-m-d H:i') }}</dd>
        </div>
        <div class="wide">
            <dt>Notes</dt>
            <dd>{{ $package->notes ?: '-' }}</dd>
        </div>
    </dl>

    <div class="form-section mt-8">Reservations ({{ $package->reservations->count() }})</div>
    @if ($package->reservations->isNotEmpty())
        {{-- scrolls when there are many reservations, the header stays visible (custom.css .table-wrap--scroll) --}}
        <div class="table-wrap table-wrap--scroll">
            {{-- fixed layout: the Space / Unit width is kept (custom.css .data-table--fixed, th.col-place) --}}
            <table class="data-table data-table--fixed">
                <thead>
                    <tr>
                        <th class="col-place">Space / Unit</th>
                        <th class="col-period">Period</th>
                        <th>Status</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($package->reservations as $reservation)
                        <tr>
                            <td>
                                <a href="{{ route($area . '.reservations.show', $reservation->id) }}" data-modal
                                    class="font-medium">
                                    @include('reservationmodule::Reservation.partials.row-place')
                                </a>
                            </td>
                            <td>
                                <a href="{{ route($area . '.reservations.show', $reservation->id) }}" data-modal
                                    class="font-medium">{{ $reservation->period }}</a>
                            </td>

                            <td>
                                <span class="{{ $reservation->status?->badge_class ?? 'badge badge-status-slate' }}">{{ $reservation->status?->name ?? '-' }}</span>
                                @if ($reservation->status && !$reservation->status->is_counted)
                                    <div class="mt-1 text-xs text-slate-500">not counted</div>
                                @endif
                            </td>
                            <td>{{ number_format($reservation->net_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-slate-500">No reservations in this package yet.</div>
    @endif

    @unless (request()->ajax())
        <div class="form-actions">
            <a href="{{ route($area . '.packages.index') }}" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Back to
                Packages</a>
        </div>
    @endunless
@endsection
