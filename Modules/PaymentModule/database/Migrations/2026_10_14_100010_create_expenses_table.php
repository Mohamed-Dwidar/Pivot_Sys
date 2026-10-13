<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        // money paid out from a drawer: it joins the open shift of whoever records it (docs/payments-plan.md "Expenses")
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->index();
            // per account: MAX + 1 (ExpenseService::nextNumber(), from config paymentmodule.number_start); imported = old id
            $table->unsignedInteger('expense_no');
            $table->bigInteger('expense_category_id')->default(0)->index();
            $table->string('item');
            // > 0 in the app; 2 imported old rows are negative (money returned)
            $table->decimal('amount', 12, 2);
            // 0 = no method (imported old method 5 "withholding notice")
            $table->bigInteger('payment_method_id')->default(0);
            $table->date('spent_at');
            $table->text('notes')->nullable();
            // a receipt (image / PDF) on the private disk expenses/{account_id}/
            $table->string('attachment')->nullable();
            // the recorder's shift closing (0 = their open shift)
            $table->bigInteger('shift_closing_id')->default(0);

            $table->string('delete_reason')->nullable();
            $table->bigInteger('deleted_by')->default(0);

            $table->morphs('creatable');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['account_id', 'expense_no']);
            $table->index(['account_id', 'spent_at']);
            // a login's open shift / a shift closing's expenses
            $table->index(['account_id', 'creatable_id', 'shift_closing_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('expenses');
    }
};
