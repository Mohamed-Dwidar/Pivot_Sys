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
            // indexed (2026-10-15): package totals / lists / options look reservations up by package
            $table->bigInteger('package_id')->default(0)->index();
            $table->integer('subscription_type_id')->default(0);

            $table->bigInteger('reservation_status_id')->default(1);

            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->integer('subscription_day')->default(0);

            $table->boolean('all_day')->default(0);
            $table->bigInteger('repeat_id')->default(0)->index();
            // the period this reservation renews (Renew / repeat copies of a period type); at most one counted renewal per period
            $table->unsignedBigInteger('renewal_of')->nullable()->index();
            // a period marked "left" (the member did not renew): off the Schedule page's Not renewed list; left_by 0 = auto (to_run cutoff)
            $table->dateTime('left_at')->nullable();
            $table->bigInteger('left_by')->default(0);
            $table->boolean('is_repeat')->default(0);
            $table->enum('repeat_frequency', ['daily', 'weekly', 'monthly', 'yearly'])->nullable();
            $table->unsignedSmallInteger('repeat_interval')->default(0);

            $table->integer('number_of_peoples')->default(1);

            $table->decimal('amount', 12, 2)->default(0);
            $table->double('discount_percentage')->default(0);
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);
            // PaymentService::refresh(): payments + withholding - refunds; remaining = net_amount - paid_amount (< 0 = credit)
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('remaining', 12, 2)->default(0);

            $table->text('notes')->nullable();

            $table->morphs('creatable');
            $table->timestamps();
            $table->softDeletes();

            // the list tabs / subscriptions (range comparisons on the dates, docs/reservations-list-plan.md)
            $table->index(['account_id', 'start_at']);
            $table->index(['account_id', 'end_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('reservations');
    }
};
