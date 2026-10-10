<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('withholding_certificates', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->index();
            $table->string('certificate_no');
            $table->date('issued_at');
            $table->bigInteger('company_id')->default(0);
            $table->bigInteger('member_id')->default(0);
            $table->string('payer_name');
            $table->string('payer_tax_id')->nullable();
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->decimal('amount', 12, 2);
            // a file on the private disk
            $table->string('attachment')->nullable();
            $table->text('notes')->nullable();

            $table->morphs('creatable');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('withholding_certificates');
    }
};
