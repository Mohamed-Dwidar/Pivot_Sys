<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->index();
            $table->string('name')->nullable();
            $table->integer('capacity')->nullable();
            $table->string('lease_period')->nullable();
            $table->text('facilities')->nullable();
            $table->double('amount')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('plans');
    }
};
