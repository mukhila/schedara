<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Enhances the existing subscriptions table with UUID, trial end, pause, and coupon fields.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->timestamp('trial_ends_at')->nullable()->after('cancel_at');
            $table->timestamp('paused_at')->nullable()->after('trial_ends_at');
            $table->unsignedBigInteger('coupon_id')->nullable()->index()->after('paused_at');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'trial_ends_at', 'paused_at', 'coupon_id']);
        });
    }
};
