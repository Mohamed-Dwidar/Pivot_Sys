{{-- a closed shift: Closed, + "Imported" for the old system's closing of a login (to_run 3.15; only the account sees it), + "Edited after closing" when a payment of it was changed / deleted / refunded later (how many, last when) --}}
<span class="badge badge-status-success"><i data-lucide="lock" class="inline h-3 w-3"></i> Closed</span>
@if ($closing->is_imported)
    <span class="badge badge-status-slate" title="Imported from the old system">Imported</span>
@endif
@if ($closing->isEdited())
    <span class="badge badge-status-warning" title="{{ $closing->edit_count }} change(s), last {{ $closing->edited_at?->format('d-m-Y h:i A') }}">Edited after closing</span>
@endif
