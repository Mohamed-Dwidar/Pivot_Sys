<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->default(1)->index();
            $table->bigInteger('member_id');
            $table->string('name');
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->boolean('is_active')->default(1);
            $table->double('discount_percentage')->default(0);
            $table->double('total_amount')->default(0);
            $table->double('amount')->default(0);
            $table->double('after_discount')->default(0);
            $table->double('remaining')->default(0);
            $table->string('notes')->nullable();

            $table->morphs('creatable');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('packages');
    }
};
