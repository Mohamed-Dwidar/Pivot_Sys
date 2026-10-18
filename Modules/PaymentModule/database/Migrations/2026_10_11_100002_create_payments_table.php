<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            // (account_id, deleted_at) below = the account's lists / counts (2026-10-18; the plain account_id index was redundant)
            $table->bigInteger('account_id');
            // per account: MAX + 1 (PaymentService::nextNumber())
            $table->unsignedInteger('payment_no');

            // what is paid: a reservation / a package; none (null, 0) = other income (income_category_id)
            $table->string('payable_type')->nullable();
            $table->bigInteger('payable_id')->default(0);
            $table->bigInteger('income_category_id')->default(0);
            $table->bigInteger('member_id')->default(0)->index();

            // the amount is always positive, the type gives the sign (refund = money back)
            $table->enum('type', ['payment', 'refund', 'withholding'])->default('payment');
            $table->decimal('amount', 12, 2);
            // 0 for withholding (no money received, a certificate instead)
            $table->bigInteger('payment_method_id')->default(0);
            $table->bigInteger('withholding_certificate_id')->default(0);
            // a refund: the payment it gives money back for (0 = not a refund, or an imported refund with no link)
            $table->bigInteger('refund_of')->default(0)->index();
            // the collector's shift closing (0 = their open shift; the account's own payments too)
            // indexed (2026-10-18): the shift lists look payments up by closing
            $table->bigInteger('shift_closing_id')->default(0)->index();
            $table->date('paid_at')->index();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            // a refund: why the money was given back (required)
            $table->string('reason')->nullable();

            $table->string('delete_reason')->nullable();
            $table->bigInteger('deleted_by')->default(0);

            $table->morphs('creatable');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['account_id', 'payment_no']);
            $table->index(['payable_type', 'payable_id']);
            // an employee's open period / a shift closing's payments
            $table->index(['account_id', 'creatable_id', 'shift_closing_id']);
            $table->index(['account_id', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('payments');
    }
};
