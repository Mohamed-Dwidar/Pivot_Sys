{{--
    A payment of a CLOSED shift ($closedShift, PaymentService::closedShiftOf()) in the edit / delete / refund popups:
    the change is logged on that shift with the reason, the shift is marked "Edited after closing".
    $refund: a refund leaves the shift's totals as they are (the refund goes into the refunder's own open shift).
--}}
@if (!empty($closedShift))
    <div class="mt-4 text-warning">
        This payment is in the closed shift of {{ $closedShift->user?->userable?->displayName() ?? '-' }}
        (closed {{ $closedShift->closed_at?->format('d-m-Y h:i A') }}).
        @if (!empty($refund))
            The shift gets a log entry with your reason; its totals don't change.
        @else
            The change is logged on the shift with your reason and its totals are calculated again.
        @endif
    </div>
@endif
