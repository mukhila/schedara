<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analytics_account_id')->constrained('analytics_accounts')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->date('metric_date');

            // Reach & Impressions
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('reach_count')->default(0);

            // Engagement
            $table->unsignedBigInteger('engagement_count')->default(0);
            $table->unsignedBigInteger('likes')->default(0);
            $table->unsignedBigInteger('comments')->default(0);
            $table->unsignedBigInteger('shares')->default(0);
            $table->unsignedBigInteger('saves')->default(0);
            $table->unsignedBigInteger('replies')->default(0);
            $table->unsignedBigInteger('mentions')->default(0);
            $table->unsignedBigInteger('reactions')->default(0);

            // Clicks & Conversions
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('conversions')->default(0);
            $table->unsignedBigInteger('profile_visits')->default(0);
            $table->unsignedBigInteger('website_clicks')->default(0);

            // Followers
            $table->unsignedBigInteger('followers')->default(0);
            $table->unsignedBigInteger('unfollows')->default(0);
            $table->unsignedBigInteger('new_followers')->default(0);

            // Revenue
            $table->decimal('revenue', 12, 2)->default(0);
            $table->decimal('spend', 12, 2)->default(0);
            $table->decimal('engagement_rate', 8, 4)->default(0);

            // Reach breakdown
            $table->unsignedBigInteger('organic_reach')->default(0);
            $table->unsignedBigInteger('paid_reach')->default(0);
            $table->unsignedBigInteger('viral_reach')->default(0);

            $table->timestamps();

            $table->unique(['analytics_account_id', 'metric_date']);
            $table->index(['tenant_id', 'metric_date']);
            $table->index('metric_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_metrics');
    }
};
