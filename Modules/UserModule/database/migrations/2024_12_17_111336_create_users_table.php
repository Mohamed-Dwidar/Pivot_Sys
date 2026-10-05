<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Login data for every non-admin user type; userable is the owner (Account, ...).
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->morphs('userable');
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['userable_type', 'userable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
