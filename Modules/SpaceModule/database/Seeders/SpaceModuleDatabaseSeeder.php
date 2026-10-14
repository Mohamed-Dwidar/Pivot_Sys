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
            array("id" => "1", "name" => "day use", "booking_mode" => 'full_day', "auto_renew" => 0, "can_repeat" => 0, 'show_home' => 0),
            array("id" => "2", "name" => "subscriptions", "booking_mode" => 'period', "auto_renew" => 1, "can_repeat" => 0, 'show_home' => 0),
            array("id" => "3", "name" => "meeting", "booking_mode" => 'hourly', "auto_renew" => 0, "can_repeat" => 1, 'show_home' => 0),
        );
        SubscriptionType::insert($subscriptions);
    }
}
