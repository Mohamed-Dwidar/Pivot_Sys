<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        // the full history of a day closing: every close / reopen (who, why, the totals at that moment)
        Schema::create('day_closing_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->index();
            $table->bigInteger('day_closing_id')->index();
            $table->enum('action', ['close', 'reopen']);
            // users.id
            $table->bigInteger('user_id');
            // required on reopen
            $table->string('reason')->nullable();
            // close: the totals saved; reopen: the totals that were closed (DayClosingService::summary())
            $table->decimal('cash_total', 12, 2)->default(0);
            $table->decimal('withholding_total', 12, 2)->default(0);
            $table->json('totals')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('day_closing_logs');
    }
};
