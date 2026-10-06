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
            $table->boolean('is_repeat')->default(0);
            $table->boolean('is_continue')->default(0);
            $table->boolean('full_day')->default(0);
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
