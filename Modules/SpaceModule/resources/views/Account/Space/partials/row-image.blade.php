@if ($space->images->isNotEmpty())
    <img src="{{ $space->images->first()->url }}" alt="{{ $space->name }}" class="table-thumb" data-action="zoom">
@else
    <div class="table-thumb table-thumb--empty"><i data-lucide="image"></i></div>
@endif
