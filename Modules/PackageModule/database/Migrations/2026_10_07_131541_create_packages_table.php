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
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('after_discount', 12, 2)->default(0);
            // PaymentService::refresh(): payments + withholding - refunds; remaining = after_discount - paid_amount (< 0 = credit)
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('remaining', 12, 2)->default(0);
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
