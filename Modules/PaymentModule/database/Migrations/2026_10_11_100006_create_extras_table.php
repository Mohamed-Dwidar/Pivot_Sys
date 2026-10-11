<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        // amounts added to what is due (a package now, a reservation later): due = net + Σ extras
        Schema::create('extras', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->index();
            $table->string('extrable_type');
            $table->bigInteger('extrable_id');
            // extra = a real sale (copies, buffet ...): revenue, income_category_id, invoiceable later (phase 2);
            // adjustment = a balance move (carry-overs between packages ...): not revenue, never invoiced, can be negative
            $table->enum('type', ['extra', 'adjustment'])->default('extra');
            $table->bigInteger('income_category_id')->default(0);
            $table->string('item');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            // quantity × unit_price, signed
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('date');
            $table->text('notes')->nullable();

            $table->morphs('creatable');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['extrable_type', 'extrable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('extras');
    }
};
