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
            $table->string('email')->nullable();
            $table->double('national_number')->nullable();
            $table->string('notes')->nullable();
            $table->string('from_where')->nullable();
            $table->string('push_token')->nullable();
            $table->integer('created_by')->default(1);

            $table->timestamps();
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
