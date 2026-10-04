<?php

namespace Modules\AdminModule\app\Models;
// use Laravel\Passport\HasApiTokens;
use Modules\UserModule\app\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{

    use HasFactory ; //HasApiTokens  ;
    
    protected $guard = 'admin';

    protected $fillable = ['name','email','password'];
    public function user()
    {
        return $this->morphMany(User::class,'userable');
    }
    
}
