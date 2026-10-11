@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

@section('title')
    Delete Payment #{{ $payment->payment_no }}
@endsection

{{-- the owner only: soft delete, the reason + who deleted it are kept (audit report) --}}
@section('content')
    <form method="POST" action="{{ route('account.payments.destroy', $payment->id) }}" data-ajax data-row="{{ $payment->id }}"
        data-confirm="Payment #{{ $payment->payment_no }} will be deleted, the paid / remaining of what it pays are calculated again."
        data-confirm-title="Delete this payment?"
        data-confirm-button="Delete"
        data-confirm-variant="danger">
        @csrf
        @method('DELETE')

        @include('paymentmodule::Payment.partials.payment-summary', ['payableName' => null])

        <div class="mt-6">
            @include('layoutmodule::partials.field', ['name' => 'delete_reason', 'label' => 'Reason', 'type' => 'textarea', 'required' => true,
                'attrs' => 'maxlength="255"', 'hint' => 'Why the payment is deleted (kept with it).'])
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-danger"><i data-lucide="trash-2"></i> Delete</button>
            @if (request()->ajax())
                <button type="button" data-tw-dismiss="modal" class="btn btn-secondary">Cancel</button>
            @else
                <a href="{{ route('account.payments.show', $payment->id) }}" class="btn btn-secondary">Cancel</a>
            @endif
        </div>
    </form>
@endsection
