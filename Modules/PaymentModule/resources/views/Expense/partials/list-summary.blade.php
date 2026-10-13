{{-- the expenses list summary line for the current filters: "Total: <amount> — <method> <amount> · …" ($summary from ExpenseActions) --}}
<span class="font-medium">Total: <span class="text-danger">{{ number_format($summary['total'], 2) }}</span></span>
@if ($summary['methods'])
    <span class="text-slate-500"> — {{ collect($summary['methods'])->map(fn ($amount, $name) => ($name === \Modules\PaymentModule\app\Models\Expense::NO_METHOD ? $name : Str::humanize($name)) . ' ' . number_format($amount, 2))->implode(' · ') }}</span>
@endif
