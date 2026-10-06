@if ($unit->images->isNotEmpty())
    <img src="{{ $unit->images->first()->url }}" alt="{{ $unit->name }}" class="table-thumb" data-action="zoom">
@else
    <div class="table-thumb table-thumb--empty"><i data-lucide="image"></i></div>
@endif
