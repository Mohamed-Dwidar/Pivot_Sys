<?php

namespace Modules\MemberModule\app\Repositories;

use Modules\MemberModule\app\Models\Member;
use Prettus\Repository\Eloquent\BaseRepository;

class MemberRepository extends BaseRepository
{
    public function model()
    {
        return Member::class;
    }

    // members of one account only
    public function forAccount($accountId)
    {
        return Member::with('company', 'job')->where('account_id', $accountId);
    }
}
