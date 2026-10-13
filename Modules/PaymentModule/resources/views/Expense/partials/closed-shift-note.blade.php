{{-- an expense of a CLOSED shift ($closedShift) in the edit / delete popups: logged on that shift with the reason, totals recalculated --}}
@if (!empty($closedShift))
    <div class="mb-4 text-warning">
        This expense is in the closed shift of {{ $closedShift->user?->userable?->displayName() ?? '-' }}
        (closed {{ $closedShift->closed_at?->format('d-m-Y h:i A') }}). The change is logged on the shift with your reason and its totals are calculated again.
    </div>
@endif
