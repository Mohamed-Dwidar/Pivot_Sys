{{-- the Daily Summary day a shift popup was opened from (?date=): the answer goes back to it --}}
@if (request()->filled('date'))
    <input type="hidden" name="date" value="{{ request('date') }}">
@endif
