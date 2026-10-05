<?php

namespace Modules\CompanyModule\app\Repositories;

use Modules\CompanyModule\app\Models\Company;
use Prettus\Repository\Eloquent\BaseRepository;

class CompanyRepository extends BaseRepository
{
    public function model()
    {
        return Company::class;
    }

    // companies of one account only
    public function forAccount($accountId)
    {
        return Company::where('account_id', $accountId);
    }
}
