<?php

namespace Modules\ReservationModule\app\Repositories;

use Modules\ReservationModule\app\Models\ReservationStatus;
use Prettus\Repository\Eloquent\BaseRepository;

class ReservationStatusRepository extends BaseRepository
{
    public function model()
    {
        return ReservationStatus::class;
    }

    // statuses of one account only
    public function forAccount($accountId)
    {
        return ReservationStatus::withCount('reservations')->where('account_id', $accountId);
    }
}
