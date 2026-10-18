<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->default(0);
            $table->string('name');
            $table->integer('company_id')->default(0);
            $table->integer('job_id')->default(0);
            $table->string('phone');
            // the phone as compared (Member::phoneKey(): digits, an Egyptian mobile → its 10 digits); NULL on a merged member (2026-10-18)
            $table->string('phone_key', 20)->nullable();
            $table->string('email')->nullable();
            $table->double('national_number')->nullable();
            $table->string('notes')->nullable();
            $table->string('from_where')->nullable();
            $table->string('push_token')->nullable();
            $table->integer('created_by')->default(1);
            // members:merge-duplicates: the member this one was merged into (then soft deleted)
            $table->unsignedBigInteger('merged_into')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // one phone per member in an account (merged members have phone_key NULL)
            $table->unique(['account_id', 'phone_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
