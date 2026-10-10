<?php

namespace Modules\PaymentModule\app\Contracts;

/**
 * Something a member pays for (a reservation, a package): payments.payable morph.
 * The model also uses Modules\PaymentModule\app\Models\Concerns\HasPayments (paid_amount / remaining columns).
 */
interface Payable
{
    // the amount due (what the member pays)
    public function payableAmount(): float;

    // the member who pays
    public function payableMemberId(): int;

    // false when it is paid through something else (a reservation inside a package)
    public function canTakePayments(): bool;
}
