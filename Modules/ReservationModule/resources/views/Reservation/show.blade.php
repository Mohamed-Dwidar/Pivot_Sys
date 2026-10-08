@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

@section('title')
    {{-- one line: Reservation : space › (unit color) unit --}}
    <span class="reservation-title">
        <div class="text-lg font-medium">{{ $reservation->member?->name ?? '-' }}</div>
        <div>
        @if ($reservation->package)
            <a href="{{ route($area . '.packages.show', $reservation->package_id) }}" data-modal class="badge badge-active mr-1" title="Package">
                <i data-lucide="package" class="inline h-3 w-3"></i> {{ $reservation->package->name }}
            </a>
        @endif
        </div>
    </span>
@endsection

@section('modal-size', 'lg')

@section('actions')
    {{-- the status: a drop menu like its badge, changed by ajax --}}
    @include('reservationmodule::Reservation.partials.status-menu')
    <a href="{{ route($area . '.reservations.edit', $reservation->id) }}" data-modal class="btn btn-secondary"><i data-lucide="pencil"></i> Edit</a>
    @include('reservationmodule::Reservation.partials.delete-form')
@endsection

@section('content')
    {{-- the member name + the package (green badge, opens the package) --}}
    <div class="flex flex-wrap items-center gap-3">
        @if ($reservation->unit?->color)
            @include('unitmodule::partials.color-swatch', ['value' => $reservation->unit->color->value])
        @endif
        {{ $reservation->space?->name ?? '-' }}
        &rsaquo;
        {{ $reservation->unit?->name ?? '-' }}
    </div>
    {{-- the period (the status is in the header menu) --}}
    <div class="mt-2 text-slate-500">{{ $reservation->period }}</div>

   <div class="form-section">&nbsp;</div>

    <dl class="detail-list">
        {{-- "21-10-2026 | 11:00 AM" (the time only for a time based plan, see Reservation::formatAt) --}}
        <div><dt>Start</dt><dd>{{ $reservation->formatAt($reservation->start_at) }}</dd></div>
        <div><dt>End</dt><dd>{{ $reservation->is_continue ? 'Continues' : $reservation->formatAt($reservation->end_at) }}</dd></div>

        <div><dt>Member Phone</dt><dd>{{ $reservation->member?->phone ?? '-' }}</dd></div>

        <div><dt>Number of People</dt><dd>{{ $reservation->number_of_peoples }}</dd></div>
        <div><dt>Auto Renew</dt><dd>
            {{-- the reservation column is is_continue (its subscription type is auto renew) --}}
            {{ $reservation->is_continue ? 'Yes' : 'No' }}
        </dd></div>
    </dl>

   <div class="form-section">&nbsp;</div>

    <dl class="detail-list">
        <div><dt>Amount</dt><dd>{{ number_format($reservation->amount, 2) }}</dd></div>
        <div><dt>Discount</dt><dd>{{ number_format($reservation->discount_value, 2) }} ({{ rtrim(rtrim(number_format($reservation->discount_percentage, 2), '0'), '.') }}%)</dd></div>
        <div><dt>Net Amount</dt><dd class="font-medium text-success">{{ number_format($reservation->net_amount, 2) }}</dd></div>
    </dl>

    <div class="form-section">&nbsp;</div>

    <dl class="detail-list">
        <div class="wide"><dt>Notes</dt><dd class="whitespace-pre-line">{{ $reservation->notes ?: '-' }}</dd></div>
        <div class="wide">
            <dd class="whitespace-pre-line">
                Added by <b>{{ $reservation->creatable?->userable?->displayName() ?? $reservation->creatable?->email ?? '-' }}</b>
                |
                At {{ $reservation->created_at->format('d-m-Y h:i A') }}
            </dd>
        </div>
    </dl>

    @unless (request()->ajax())
        <div class="form-actions">
            <a href="{{ route($area . '.reservations.index') }}" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Back to Reservations</a>
        </div>
    @endunless
@endsection
