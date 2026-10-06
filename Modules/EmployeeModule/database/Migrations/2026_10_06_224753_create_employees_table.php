<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->index();
            $table->string('name')->nullable();
            $table->bigInteger('position_id')->default(0);
            $table->bigInteger('shift_id')->default(0);
            $table->string('phone')->nullable();
            $table->string('another_phone')->nullable();
            $table->enum('gender', ['male', 'female'])->default('male');
            $table->date('birth_date')->nullable();
            $table->string('address')->nullable();
            $table->string('image')->nullable();
            $table->double('salary')->nullable();
            $table->string('educational_qualification')->nullable();
            $table->date('leave_date')->nullable();
            $table->date('join_date')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('employees');
    }
};
