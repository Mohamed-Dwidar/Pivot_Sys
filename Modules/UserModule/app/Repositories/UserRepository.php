<?php

namespace Modules\UserModule\app\Repositories;

use Modules\UserModule\app\Models\User;
use Prettus\Repository\Eloquent\BaseRepository;

class UserRepository extends BaseRepository
{
    public function model()
    {
        return User::class;
    }
}
