<?php

namespace Modules\UserModule\app\Repositories;
use Modules\UserModule\app\Models\User;
use Prettus\Repository\Eloquent\BaseRepository;

class UserRepository  extends BaseRepository
{
    function model()
    {
        return User::class;
    }

    function filter($request)
    {
        return User::filter($request);
    }

    public function getPassword($id)
    {
        return User::where('id', $id)->pluck('password')->first();
    }



}
