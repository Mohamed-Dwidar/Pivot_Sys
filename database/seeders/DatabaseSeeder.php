<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AdminModule\Database\Seeders\AdminModuleDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminModuleDatabaseSeeder::class);
    }
}
