<?php

namespace Modules\UserModule\app\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, SoftDeletes;

    protected $guarded = [];

    public function userable()
    {
        return $this->morphTo();
    }

      
}
