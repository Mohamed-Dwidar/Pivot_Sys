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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->default(1);
            $table->bigInteger('space_id');
            $table->integer('subscription_type_id')->default(0);
            $table->string('name_ar');
            $table->string('name_en');
            $table->integer('color_id')->default(1);
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->text('notes_ar')->nullable();
            $table->text('notes_en')->nullable();
            $table->string('capacity')->nullable();
            $table->integer('concurrent_usage')->default(1);
            $table->string('lease_period')->nullable();
            $table->boolean('is_active')->default(1);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('units');
    }
};
