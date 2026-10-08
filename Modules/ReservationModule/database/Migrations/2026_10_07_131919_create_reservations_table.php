<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->default(1)->index();
            $table->bigInteger('member_id')->index();
            $table->bigInteger('space_id')->index();
            $table->bigInteger('unit_id')->index();
            $table->bigInteger('plan_id');
            $table->bigInteger('package_id')->default(0);
            $table->integer('subscription_type_id')->default(0);

            $table->bigInteger('reservation_status_id')->default(1);

            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->integer('subscription_day')->default(0);
            $table->boolean('is_continue')->default(0);

            $table->boolean('all_day')->default(0);
            $table->bigInteger('repeat_id')->default(0);
            $table->boolean('is_repeat')->default(0);
            $table->enum('repeat_frequency', ['daily', 'weekly', 'monthly', 'yearly'])->nullable();
            $table->unsignedSmallInteger('repeat_interval')->default(0);

            $table->integer('number_of_peoples')->default(1);

            $table->double('amount')->default(0);
            $table->double('discount_percentage')->default(0);
            $table->double('discount_value')->default(0);
            $table->double('net_amount')->default(0);

            $table->text('notes')->nullable();

            $table->morphs('creatable');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('reservations');
    }
};
