<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        // the account's expense categories (Settings › Expense Categories); old system "types" keep their ids (to_run)
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->default(0)->index();
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->boolean('is_active')->default(1);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('expense_categories');
    }
};
