{{--
    The tabs of a DataTables filters form (hidden input "tab", datatables.js [data-filter-tab]); the default tab sends ''.
    @include('layoutmodule::partials.filter-tabs', ['tabs' => Model::TABS, 'default' => 'today', 'dates' => Model::tabDates(),
        'counts' => [...], 'counter' => 'reservations-'])
    dates (optional): the date field of each tab (partials.date-range-filter, custom.js): select = its days "Y-m-d[,Y-m-d]", label = text only.
    counts / counter (optional): a count after the tab name ([data-counter="{counter}{tab}"], updated by the json "counts").
--}}
<input type="hidden" name="tab" value="">
<div class="status-tabs filter-bar__tabs">
    @foreach ($tabs as $value => $label)
        <button type="button" data-filter-tab data-name="tab" data-value="{{ $value === $default ? '' : $value }}" class="{{ $value === $default ? 'active' : '' }}"
            data-date-select="{{ $dates[$value]['select'] ?? '' }}" data-date-label="{{ $dates[$value]['label'] ?? '' }}" data-tab="{{ $value }}">
            {{ $label }}
            @if (isset($counter, $counts[$counter . $value]))
                (<span data-counter="{{ $counter . $value }}">{{ $counts[$counter . $value] }}</span>)
            @endif
        </button>
    @endforeach
</div>
