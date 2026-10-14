<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // The login (email / password) lives in users (userable morph).
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('phone', 30);
            $table->string('address')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('logo')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('approved_at')->nullable();
            // Settings › General: withholding tax (off = no new withholding, existing kept) / VAT (Phase 2: off = prices tax free)
            $table->boolean('withholding_enabled')->default(true);
            $table->boolean('vat_enabled')->default(true);
            // Settings › General, the Today page: the timeline hours (to > 24 = the next morning, max 30) and the renewals window (days)
            $table->unsignedTinyInteger('today_hours_from')->default(8);
            $table->unsignedTinyInteger('today_hours_to')->default(24);
            $table->unsignedTinyInteger('renewal_days')->default(7);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
