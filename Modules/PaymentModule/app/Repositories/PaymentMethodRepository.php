<?php

namespace Modules\PaymentModule\app\Repositories;

use Modules\PaymentModule\app\Models\PaymentMethod;
use Prettus\Repository\Eloquent\BaseRepository;

class PaymentMethodRepository extends BaseRepository
{
    public function model()
    {
        return PaymentMethod::class;
    }

    // payment methods of one account only
    public function forAccount($accountId)
    {
        return PaymentMethod::withCount('payments')->where('account_id', $accountId);
    }
}
