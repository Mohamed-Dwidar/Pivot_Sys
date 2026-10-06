<?php

namespace Modules\UnitModule\app\Repositories;

use Modules\UnitModule\app\Models\Unit;
use Prettus\Repository\Eloquent\BaseRepository;

class UnitRepository extends BaseRepository
{
    public function model()
    {
        return Unit::class;
    }

    // units of one space of one account
    public function forSpace($accountId, $spaceId)
    {
        return Unit::with('images', 'color', 'subscriptionType')->where('account_id', $accountId)->where('space_id', $spaceId);
    }
}
