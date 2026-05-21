<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Enhances the existing plans table with fields for trial, popularity, currency, and PayPal IDs.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->unsignedTinyInteger('trial_days')->default(14)->after('is_active');
            $table->boolean('is_popular')->default(false)->after('trial_days');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('is_popular');
            $table->string('currency', 3)->default('USD')->after('sort_order');
            $table->string('paypal_monthly_plan_id')->nullable()->after('razorpay_yearly_plan_id');
            $table->string('paypal_yearly_plan_id')->nullable()->after('paypal_monthly_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'description', 'trial_days', 'is_popular', 'sort_order',
                'currency', 'paypal_monthly_plan_id', 'paypal_yearly_plan_id',
            ]);
        });
    }
};
