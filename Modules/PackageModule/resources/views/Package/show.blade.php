@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

@section('title')
    Package Details
@endsection

@section('modal-size', 'lg')

@section('actions')
    <a href="{{ route($area . '.packages.edit', $package->id) }}" data-modal class="btn btn-secondary"><i data-lucide="pencil"></i> Edit</a>
    @include('packagemodule::Package.partials.delete-form')
@endsection

@section('content')
    <div class="flex items-center gap-3">
        <div class="text-lg font-medium">{{ $package->name }}</div>
        <span class="badge {{ $package->is_active ? 'badge-active' : 'badge-inactive' }}">{{ $package->is_active ? 'Active' : 'Inactive' }}</span>
    </div>

    <div class="form-section mt-6">Package Data</div>
    <dl class="detail-list">
        <div><dt>Member</dt><dd>{{ $package->member ? $package->member->name . ' - ' . $package->member->phone : '-' }}</dd></div>
        <div><dt>Period</dt><dd>{{ $package->period }}</dd></div>
        <div><dt>Amount</dt><dd>{{ number_format($package->amount, 2) }}</dd></div>
        <div><dt>Discount</dt><dd>{{ rtrim(rtrim(number_format($package->discount_percentage, 2), '0'), '.') }}%</dd></div>
        <div><dt>After Discount</dt><dd class="font-medium">{{ number_format($package->after_discount, 2) }}</dd></div>
        <div><dt>Reservations Total</dt><dd>{{ number_format($package->total_amount, 2) }}</dd></div>
        <div><dt>Remaining</dt><dd class="font-medium {{ $package->remaining < 0 ? 'text-danger' : '' }}">{{ number_format($package->remaining, 2) }}</dd></div>
        <div><dt>Added At</dt><dd>{{ $package->created_at->format('Y-m-d H:i') }}</dd></div>
        <div class="wide"><dt>Notes</dt><dd>{{ $package->notes ?: '-' }}</dd></div>
    </dl>

    <div class="form-section mt-8">Reservations ({{ $package->reservations->count() }})</div>
    @if ($package->reservations->isNotEmpty())
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>Period</th><th>Space / Unit</th><th>Status</th><th>Net Amount</th></tr>
                </thead>
                <tbody>
                    @foreach ($package->reservations as $reservation)
                        <tr>
                            <td class="whitespace-nowrap">
                                <a href="{{ route($area . '.reservations.show', $reservation->id) }}" data-modal class="font-medium">{{ $reservation->period }}</a>
                            </td>
                            <td>{{ $reservation->space?->name ?? '-' }} &rsaquo; {{ $reservation->unit?->name ?? '-' }}</td>
                            <td>{{ $reservation->status?->name ?? '-' }}</td>
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
            <a href="{{ route($area . '.packages.index') }}" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Back to Packages</a>
        </div>
    @endunless
@endsection
