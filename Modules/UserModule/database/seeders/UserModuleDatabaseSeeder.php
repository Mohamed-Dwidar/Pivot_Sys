<?php

namespace Modules\UserModule\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\UserModule\app\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserModuleDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Model::unguard();
        // DB::table('users')->truncate();
        // User::create([
        //     'username' => "sysAdmin",
        //     'password' => bcrypt('123456'),
        //     'userable_type' => 'Modules\AdminModule\app\Models\Admin',
        //     'userable_id' => 2,

        // ]);
        // $user->syncRoles(1);


        
    }
}
