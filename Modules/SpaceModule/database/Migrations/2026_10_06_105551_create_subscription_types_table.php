<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_types', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->default(1);
            $table->string('name')->nullable();
            $table->boolean('show_home')->default(0);
            // the Schedule page section of the type: hourly = timeline, full_day = the day's list, period = counts + renewals (replaces full_day)
            $table->enum('booking_mode', ['hourly', 'full_day', 'period'])->default('hourly');
            // the order of the sections on the Schedule page
            $table->unsignedSmallInteger('home_order')->default(0);
            $table->boolean('can_repeat')->default(0);
            $table->boolean('auto_renew')->default(0);
            // a contract (monthly subscription): collapsed on the reservations daily tabs, its own Subscriptions tab
            $table->boolean('is_contract')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('space_images');
    }
};
