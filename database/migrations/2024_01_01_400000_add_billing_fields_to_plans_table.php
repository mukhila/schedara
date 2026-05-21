<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('stripe_product_id')->nullable()->after('slug');
            $table->string('stripe_monthly_price_id')->nullable()->after('stripe_product_id');
            $table->string('stripe_yearly_price_id')->nullable()->after('stripe_monthly_price_id');
            $table->string('razorpay_monthly_plan_id')->nullable()->after('stripe_yearly_price_id');
            $table->string('razorpay_yearly_plan_id')->nullable()->after('razorpay_monthly_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_product_id',
                'stripe_monthly_price_id',
                'stripe_yearly_price_id',
                'razorpay_monthly_plan_id',
                'razorpay_yearly_plan_id',
            ]);
        });
    }
};
