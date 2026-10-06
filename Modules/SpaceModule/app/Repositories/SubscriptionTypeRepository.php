<?php

namespace Modules\SpaceModule\app\Repositories;

use Modules\SpaceModule\app\Models\SubscriptionType;
use Prettus\Repository\Eloquent\BaseRepository;

class SubscriptionTypeRepository extends BaseRepository
{
    public function model()
    {
        return SubscriptionType::class;
    }

    // subscription types of one account only
    public function forAccount($accountId)
    {
        return SubscriptionType::withCount('spaces')->where('account_id', $accountId);
    }
}
