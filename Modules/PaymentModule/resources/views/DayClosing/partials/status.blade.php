{{-- closed (locked) / reopened (+ who / why on hover) --}}
@if ($closing->status === 'closed')
    <span class="badge badge-status-success"><i data-lucide="lock" class="inline h-3 w-3"></i> Closed</span>
@else
    <span class="badge badge-status-warning" title="{{ $closing->reopen_reason }}">Reopened</span>
@endif
