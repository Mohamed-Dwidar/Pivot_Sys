@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

@section('title')
    {{-- the member (name, phone under it) + the package badge (opens the package) --}}
    <span class="reservation-title">
        <div>
            <div class="text-lg font-medium">{{ $reservation->member?->name ?? '-' }}</div>
            @if ($reservation->member?->phone)
                <div class="text-xs text-slate-500">{{ $reservation->member->phone }}</div>
            @endif
        </div>
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
    @unless ($reservation->isCounted())
        <a href="{{ route($area . '.reservations.restore', [$reservation->id, 'back_to_view' => 1]) }}" data-modal class="btn btn-secondary"><i data-lucide="rotate-ccw"></i> Restore</a>
    @endunless
    {{-- a counted subscription period not renewed yet: the next period, pre-filled (renewal_of = this one) --}}
    @if ($reservation->isPeriod() && $reservation->isCounted() && !$reservation->renewedBy)
        <a href="{{ route($area . '.reservations.create', ['renew' => $reservation->id]) }}" data-modal class="btn btn-secondary"><i data-lucide="refresh-cw"></i> Renew</a>
    @endif
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
        <div><dt>Number of People</dt><dd>{{ $reservation->number_of_peoples }}</dd></div>
    </dl>

   <div class="form-section">&nbsp;</div>

    <dl class="detail-list">
        <div><dt>Amount</dt><dd>{{ number_format($reservation->amount, 2) }}</dd></div>
        <div><dt>Discount</dt><dd>{{ number_format($reservation->discount_value, 2) }} ({{ rtrim(rtrim(number_format($reservation->discount_percentage, 2), '0'), '.') }}%)</dd></div>
        <div><dt>Net Amount</dt><dd class="font-medium text-success">{{ number_format($reservation->net_amount, 2) }}</dd></div>
    </dl>

    {{-- a subscription period: the period it renews, its renewal, left (+ undo) --}}
    @if ($reservation->isPeriod() && ($reservation->renewal_of || $reservation->renewedBy || $reservation->left_at))
        <div class="form-section">&nbsp;</div>
        <dl class="detail-list">
            @if ($reservation->renewal_of)
                <div><dt>Renewal Of</dt><dd><a href="{{ route($area . '.reservations.show', $reservation->renewal_of) }}" data-modal class="font-medium">#{{ $reservation->renewal_of }}</a></dd></div>
            @endif
            @if ($renewal = $reservation->renewedBy)
                <div><dt>Renewed By</dt><dd>
                    <a href="{{ route($area . '.reservations.show', $renewal->id) }}" data-modal class="font-medium">#{{ $renewal->id }}</a>
                    → {{ $renewal->realEnd()?->format(\Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT) ?? '-' }}
                </dd></div>
            @endif
            @if ($reservation->left_at)
                <div><dt>Left</dt><dd>
                    {{ $reservation->left_at->format(\Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT) }}
                    {{ $reservation->left_by ? '' : '(auto, old data)' }}
                    @include('reservationmodule::Reservation.partials.left-form', ['undo' => true, 'backToView' => true])
                </dd></div>
            @endif
        </dl>
    @endif

    @include('paymentmodule::Payment.partials.payable-payments', ['payable' => $reservation, 'kind' => 'reservation'])

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
