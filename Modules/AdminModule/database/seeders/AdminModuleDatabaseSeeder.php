<?php

namespace Modules\AdminModule\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\AdminModule\app\Models\Admin;

class AdminModuleDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        DB::table('admins')->truncate();
        Admin::create([
            'name' => "Admin",
            'email' => 'admin@admin.com',
            'password' => bcrypt('123456'),
        ]);
        // $user->syncRoles(1);

       
        // $this->call(\Modules\AdminModule\Database\Seeders\PermissionAdminModuleDatabaseSeeder::class);


        // $this->call("OthersTableSeeder");
    }
}
