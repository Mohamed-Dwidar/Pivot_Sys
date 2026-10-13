@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

{{--
    Restore a not counted reservation (cancelled, pending ...) to the default counted status (docs/reservations-list-plan.md):
    its slot is checked first (same check as a new booking); taken → the reservations holding it (links), no button.
    After restoring: the package is recalculated and the amount due comes back (paid / remaining refreshed).
--}}
@section('title')
    Restore Reservation
@endsection

@section('content')
    <div class="mb-4">
        {{ $reservation->member?->name ?? '-' }} · {{ $reservation->space?->name }} › {{ $reservation->unit?->name }} · {{ $reservation->period }}
        <span class="{{ $reservation->status?->badge_class ?? 'badge badge-status-slate' }} ml-1">{{ $reservation->status?->name ?? '-' }}</span>
    </div>

    @if ($reservation->isCounted())
        <div class="text-slate-500">This reservation is already active.</div>
    @elseif (!$status)
        <div class="text-danger">There is no active counted status to restore to.</div>
    @elseif ($error)
        <div role="alert" class="alert relative border rounded-md px-5 py-4 bg-danger border-danger bg-opacity-20 border-opacity-5 text-danger">
            {!! $error !!}
        </div>
        <div class="mt-3 text-slate-500">It can't be restored while its slot is taken: change the other reservation, or edit this one to another time / unit.</div>
    @else
        <form method="POST" action="{{ route($area . '.reservations.restore.store', $reservation->id) }}" data-ajax data-row="{{ $reservation->id }}">
            @csrf
            @method('PATCH')
            @if (request()->boolean('back_to_view'))
                <input type="hidden" name="back_to_view" value="1">
            @endif
            <div class="mb-4">
                The slot is free. The reservation goes back to <b>{{ $status->name }}</b>: it counts again in availability and its package,
                and its amount is due again.
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i data-lucide="rotate-ccw"></i> Restore</button>
                @if (request()->ajax())
                    <button type="button" data-tw-dismiss="modal" class="btn btn-secondary">Cancel</button>
                @else
                    <a href="{{ route($area . '.reservations.show', $reservation->id) }}" class="btn btn-secondary">Cancel</a>
                @endif
            </div>
        </form>
    @endif
@endsection
