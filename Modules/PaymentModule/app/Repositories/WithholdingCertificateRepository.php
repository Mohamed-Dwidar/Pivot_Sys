<?php

namespace Modules\PaymentModule\app\Repositories;

use Modules\PaymentModule\app\Models\WithholdingCertificate;
use Prettus\Repository\Eloquent\BaseRepository;

class WithholdingCertificateRepository extends BaseRepository
{
    public function model()
    {
        return WithholdingCertificate::class;
    }

    // certificates of one account only (+ the total of their withholding payments: payments_sum_amount)
    public function forAccount($accountId)
    {
        return WithholdingCertificate::with('company', 'member')->withSum('payments', 'amount')->withCount('payments')->where('account_id', $accountId);
    }
}
