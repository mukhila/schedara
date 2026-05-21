<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_tracking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            // feature_name: social_accounts | scheduled_posts | team_members | storage_mb | ai_credits | analytics_reports
            $table->string('feature_name', 60)->index();
            $table->unsignedBigInteger('current_usage')->default(0);
            $table->unsignedBigInteger('usage_limit')->default(0);  // 0 = unlimited
            $table->timestamp('reset_date')->nullable();            // when monthly usage resets
            $table->timestamps();

            $table->unique(['tenant_id', 'feature_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_tracking');
    }
};
