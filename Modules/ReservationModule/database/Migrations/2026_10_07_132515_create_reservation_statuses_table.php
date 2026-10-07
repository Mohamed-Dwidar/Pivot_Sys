<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservation_statuses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->default(0)->index();
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->boolean('is_active')->default(1);
            $table->boolean('is_default')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_statuses');
    }
};
