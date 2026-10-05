<?php

namespace Modules\UserModule\app\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = ['email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    public function userable(): MorphTo
    {
        return $this->morphTo();
    }
}
