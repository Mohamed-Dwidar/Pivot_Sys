<?php

namespace Modules\SpaceModule\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Modules\SpaceModule\app\Models\SubscriptionType;

class SpaceModuleDatabaseSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        Model::unguard();

        DB::table("subscription_types")->truncate();
        $subscriptions = array(
            array("id" => "1", "name" => "day use", "full_day" => 1, "is_continue" => 0, "is_repeat" => 0, 'show_home' => 0),
            array("id" => "2", "name" => "subscriptions", "full_day" => 0, "is_continue" => 1, "is_repeat" => 0, 'show_home' => 0),
            array("id" => "3", "name" => "meeting", "full_day" => 0, "is_continue" => 0, "is_repeat" => 1, 'show_home' => 0),
        );
        SubscriptionType::insert($subscriptions);
    }
}
