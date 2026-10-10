<?php

namespace Modules\PaymentModule\app\Repositories;

use Modules\PaymentModule\app\Models\IncomeCategory;
use Prettus\Repository\Eloquent\BaseRepository;

class IncomeCategoryRepository extends BaseRepository
{
    public function model()
    {
        return IncomeCategory::class;
    }

    // income categories of one account only
    public function forAccount($accountId)
    {
        return IncomeCategory::withCount('payments')->where('account_id', $accountId);
    }
}
