{{-- the payments list Member cell: name (opens the payment), the payment no. (small) --}}
@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
<a href="{{ route($area . '.payments.show', $payment->id) }}" data-modal class="font-medium">{{ $payment->member?->name ?? '-' }}</a>
<div class="text-xs text-slate-500">#{{ $payment->payment_no }}</div>
