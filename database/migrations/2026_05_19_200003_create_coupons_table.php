<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('coupon_code', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed', 'trial_extension'])->default('percentage');
            // percentage: value as integer (e.g., 20 = 20%), fixed: cents, trial_extension: days
            $table->unsignedInteger('discount_value')->default(0);
            $table->unsignedInteger('usage_limit')->nullable();   // null = unlimited
            $table->unsignedInteger('used_count')->default(0);
            $table->unsignedTinyInteger('per_workspace_limit')->default(1);
            $table->json('applicable_plans')->nullable();         // null = all plans; array of plan slugs
            $table->boolean('first_time_only')->default(false);
            $table->enum('billing_cycles', ['monthly', 'yearly', 'both'])->default('both');
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
