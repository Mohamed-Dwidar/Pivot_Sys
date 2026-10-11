<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        // the shift history: closings (by the employee / the account), reopen / re-close, and the reminder answers
        Schema::create('shift_events', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->index();
            // users.id of the employee whose shift it is
            $table->bigInteger('user_id')->index();
            // 0 = the open period (reminder answers)
            $table->bigInteger('shift_closing_id')->default(0)->index();
            // users.id of who did it (the employee or the account)
            $table->bigInteger('by_user_id');
            $table->enum('action', ['close', 'close_on_behalf', 'reopen', 'reclose', 'logout_closed', 'logout_still_working', 'still_working']);
            // required on reopen / still_working
            $table->string('reason')->nullable();
            // the closing's totals at that moment (close / reopen / reclose)
            $table->json('totals')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('shift_events');
    }
};
