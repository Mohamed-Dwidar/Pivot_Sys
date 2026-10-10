<?php

namespace Modules\PaymentModule\app\Repositories;

use Modules\PaymentModule\app\Models\Payment;
use Prettus\Repository\Eloquent\BaseRepository;

class PaymentRepository extends BaseRepository
{
    public function model()
    {
        return Payment::class;
    }

    // payments of one account only
    public function forAccount($accountId)
    {
        return Payment::with('member', 'paymentMethod', 'incomeCategory', 'payable')->where('account_id', $accountId);
    }
}
