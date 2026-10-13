<?php

namespace Modules\PaymentModule\app\Repositories;

use Modules\PaymentModule\app\Models\ExpenseCategory;
use Prettus\Repository\Eloquent\BaseRepository;

class ExpenseCategoryRepository extends BaseRepository
{
    public function model()
    {
        return ExpenseCategory::class;
    }

    // expense categories of one account only
    public function forAccount($accountId)
    {
        return ExpenseCategory::withCount('expenses')->where('account_id', $accountId);
    }
}
