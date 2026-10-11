{{-- the account's screen a shift popup was opened from (?date= = that day screen, ?index=1 = the closings list): the answer goes back to it --}}
@if (request()->filled('date'))
    <input type="hidden" name="date" value="{{ request('date') }}">
@endif
@if (request()->boolean('index'))
    <input type="hidden" name="index" value="1">
@endif
