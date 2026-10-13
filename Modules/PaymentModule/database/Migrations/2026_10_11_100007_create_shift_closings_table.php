<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        // an employee's closed shift: the payments they added since their last closing (payments.shift_closing_id), any time of day
        Schema::create('shift_closings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->index();
            // users.id of who collected the payments (an employee, or the account itself)
            $table->bigInteger('user_id')->index();
            // created_at of its first / last payment (the shift's span)
            $table->dateTime('first_payment_at')->nullable();
            $table->dateTime('last_payment_at')->nullable();
            $table->unsignedInteger('count')->default(0);
            // payments - refunds (withholding excluded)
            $table->decimal('cash_total', 12, 2)->default(0);
            $table->decimal('withholding_total', 12, 2)->default(0);
            // ShiftClosingService::summary(): {methods: {method_id: {name, payments, refunds, net}}, withholding: {count, total}, cash_total, count}
            $table->json('totals')->nullable();
            $table->string('notes')->nullable();

            // the collector, or the account (closed on their behalf)
            $table->bigInteger('closed_by');
            $table->dateTime('closed_at');
            // the account changed / deleted / refunded one of its payments after closing (badge "Edited after closing")
            $table->dateTime('edited_at')->nullable();
            $table->unsignedInteger('edit_count')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('shift_closings');
    }
};
