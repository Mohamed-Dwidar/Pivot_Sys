<?php

namespace Modules\PaymentModule\app\Repositories;

use Modules\PaymentModule\app\Models\Expense;
use Prettus\Repository\Eloquent\BaseRepository;

class ExpenseRepository extends BaseRepository
{
    public function model()
    {
        return Expense::class;
    }

    // expenses of one account only
    public function forAccount($accountId)
    {
        return Expense::with('expenseCategory', 'paymentMethod')->where('account_id', $accountId);
    }
}
