<?php

namespace Modules\PaymentModule\app\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\PaymentModule\app\Models\Payment;

/**
 * The payments of a Payable model + its payment status.
 * The table has paid_amount / remaining (kept by PaymentService::refresh()); remaining < 0 = member credit.
 */
trait HasPayments
{
    const PAYMENT_STATUSES = [
        'unpaid' => 'Unpaid',
        'partial' => 'Partially paid',
        'paid' => 'Paid',
        'credit' => 'Credit',
    ];

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    // paid now, from its payments: payments + withholding - refunds (the stored paid_amount is this value after a refresh)
    public function paymentsTotal(): float
    {
        return $this->exists ? Payment::paidTotal($this->payments()) : 0;
    }

    // unpaid / partial / paid / credit (from the stored paid_amount / remaining)
    public function getPaymentStatusAttribute(): string
    {
        $remaining = round((float) $this->remaining, 2);
        if ($remaining < 0) {
            return 'credit';
        }
        if ($remaining == 0) {
            return 'paid';
        }

        return round((float) $this->paid_amount, 2) > 0 ? 'partial' : 'unpaid';
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status];
    }

    // the payment status badge (theme palette, custom.css badge-status-*)
    public function getPaymentStatusBadgeClassAttribute(): string
    {
        return ['unpaid' => 'badge-status-danger', 'partial' => 'badge-status-warning', 'paid' => 'badge-status-success', 'credit' => 'badge-status-info'][$this->payment_status];
    }
}
