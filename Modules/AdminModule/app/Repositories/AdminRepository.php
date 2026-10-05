<?php

namespace Modules\AdminModule\app\Repositories;

use Modules\AdminModule\app\Models\Admin;
use Prettus\Repository\Eloquent\BaseRepository;

class AdminRepository extends BaseRepository
{
    public function model()
    {
        return Admin::class;
    }
}
