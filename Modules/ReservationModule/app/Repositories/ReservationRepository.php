<?php

namespace Modules\ReservationModule\app\Repositories;

use Modules\ReservationModule\app\Models\Reservation;
use Prettus\Repository\Eloquent\BaseRepository;

class ReservationRepository extends BaseRepository
{
    public function model()
    {
        return Reservation::class;
    }

    // reservations of one account only
    public function forAccount($accountId)
    {
        return Reservation::with('member', 'space', 'unit.color', 'plan', 'package', 'status', 'subscriptionType')->where('account_id', $accountId);
    }
}
