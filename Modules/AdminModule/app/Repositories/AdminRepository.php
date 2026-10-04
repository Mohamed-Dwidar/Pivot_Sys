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
    function getPass($id)
    {
        $pass = Admin::where('id', $id)->pluck('password')->first();
        return $pass;
    }

    public function findAll()
    {
        return Admin::where('id', '!=', 1)->get();
    }

    public function getByIds($ids)
    {
        return Admin::whereIN('id', $ids)->get();
    }

    public function getField($id, $field)
    {
        $admin = Admin::where('id', $id)->first();
        return $admin[$field];
    }
    public function findWith($array_with)
    {
        return Admin::where('id', '!=', 1)->with($array_with)->get();
    }

   

}
