<?php

namespace Modules\PackageModule\app\Repositories;

use Modules\PackageModule\app\Models\Package;
use Prettus\Repository\Eloquent\BaseRepository;

class PackageRepository extends BaseRepository
{
    public function model()
    {
        return Package::class;
    }

    // packages of one account only
    public function forAccount($accountId)
    {
        return Package::with('member')->withCount('reservations')->where('account_id', $accountId);
    }
}
