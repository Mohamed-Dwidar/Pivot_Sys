<?php

namespace Modules\SpaceModule\app\Repositories;

use Modules\SpaceModule\app\Models\Space;
use Prettus\Repository\Eloquent\BaseRepository;

class SpaceRepository extends BaseRepository
{
    public function model()
    {
        return Space::class;
    }

    // spaces of one account only
    public function forAccount($accountId)
    {
        return Space::with('images', 'subscriptionTypes')->withCount('units')->where('account_id', $accountId);
    }
}
