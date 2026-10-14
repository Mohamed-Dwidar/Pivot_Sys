{{--
    One cell of the All / Cash Out list (PaymentActions::cashRow()): $cell = type | party | what | amount, $kind = payment | expense,
    $item = the Payment / Expense. Cash In (payment, withholding) green +; Cash Out (refund, expense) red −.
--}}
@php
    $area = request()->routeIs('employee.*') ? 'employee' : 'account';
    $isPayment = $kind === 'payment';
    $showUrl = route($area . ($isPayment ? '.payments.show' : '.expenses.show'), $item->id);
@endphp
@switch($cell)
    @case('type')
        {{-- the kind (opens the record) + its no. --}}
        @if (!$isPayment)
            <a href="{{ $showUrl }}" data-modal class="badge badge-status-danger">Cash Out</a>
        @elseif ($item->type === 'refund')
            <a href="{{ $showUrl }}" data-modal class="badge badge-status-danger">Refund</a>
        @elseif ($item->type === 'withholding')
            <a href="{{ $showUrl }}" data-modal class="badge badge-status-info">Withholding</a>
        @else
            <a href="{{ $showUrl }}" data-modal class="badge badge-status-success">Cash In</a>
        @endif
        <div class="text-xs text-slate-500">#{{ $isPayment ? $item->payment_no : $item->expense_no }}</div>
        @break

    @case('party')
        {{-- the member (payments) / the category (expenses) --}}
        @if ($isPayment)
            <a href="{{ $showUrl }}" data-modal class="font-medium">{{ $item->member?->name ?? '-' }}</a>
        @else
            <a href="{{ $showUrl }}" data-modal class="font-medium">{{ $item->expenseCategory?->name ?? '-' }}</a>
        @endif
        @break

    @case('what')
        {{-- what it is for (payments, like the Cash In list) / the item (expenses) --}}
        @if ($isPayment)
            @include('paymentmodule::Payment.partials.row-source', ['payment' => $item])
        @else
            {{ $item->item }}
            @if ($item->attachment)
                <i data-lucide="paperclip" class="inline h-3 w-3" title="Receipt attached"></i>
            @endif
        @endif
        @break

    @case('amount')
        @php($out = !$isPayment || $item->type === 'refund')
        {{-- an imported negative expense = money returned: in --}}
        @if (!$isPayment && $item->amount < 0)
            <span class="whitespace-nowrap font-bold text-success">+{{ number_format(-$item->amount, 2) }}</span>
            <span class="badge badge-status-success badge-small">Return</span>
        @else
            <span class="whitespace-nowrap font-bold {{ $out ? 'text-danger' : 'text-success' }}">{{ $out ? '−' : '+' }}{{ number_format($item->amount, 2) }}</span>
        @endif
        @break
@endswitch
