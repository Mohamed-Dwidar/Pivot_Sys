{{-- the expenses list Amount cell: money paid out (bold red); an imported negative row = money returned (green "return") --}}
@if ($expense->amount < 0)
    <span class="whitespace-nowrap font-bold text-success">+{{ number_format(-$expense->amount, 2) }}</span>
    <span class="badge badge-status-success badge-small">Return</span>
@else
    <span class="whitespace-nowrap font-bold text-danger">{{ number_format($expense->amount, 2) }}</span>
@endif
