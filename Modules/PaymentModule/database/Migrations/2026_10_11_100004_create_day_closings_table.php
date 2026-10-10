<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('day_closings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id');
            // one closing per account per day: payments dated on a closed day are locked
            $table->date('date');
            $table->enum('status', ['closed', 'reopened'])->default('closed');
            // payments - refunds (withholding excluded)
            $table->decimal('cash_total', 12, 2)->default(0);
            $table->decimal('withholding_total', 12, 2)->default(0);
            // {employees: {user_id: {name, methods: {method_id: {payments, refunds, net}}, net}}, methods: {method_id: net}}
            $table->json('totals')->nullable();

            $table->bigInteger('closed_by');
            $table->dateTime('closed_at');
            $table->bigInteger('reopened_by')->default(0);
            $table->dateTime('reopened_at')->nullable();
            $table->string('reopen_reason')->nullable();

            $table->timestamps();

            $table->unique(['account_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('day_closings');
    }
};
