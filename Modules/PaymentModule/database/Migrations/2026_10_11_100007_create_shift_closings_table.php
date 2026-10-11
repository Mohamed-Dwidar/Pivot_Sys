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
            // users.id of the employee who collected the payments
            $table->bigInteger('user_id')->index();
            // closed = its payments are locked; reopened = the account opened it again (its payments stay in it)
            $table->enum('status', ['closed', 'reopened'])->default('closed');
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

            // the employee, or the account (closed on their behalf)
            $table->bigInteger('closed_by');
            $table->dateTime('closed_at');
            $table->bigInteger('reopened_by')->default(0);
            $table->dateTime('reopened_at')->nullable();
            $table->string('reopen_reason')->nullable();

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
