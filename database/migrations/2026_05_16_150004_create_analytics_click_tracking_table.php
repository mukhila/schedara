<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_click_tracking', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->references('id')->on('analytics_campaigns')->nullOnDelete();
            $table->string('platform', 32)->nullable();
            $table->string('url', 2048);
            $table->string('short_code', 32)->unique()->nullable();
            $table->string('utm_source', 128)->nullable();
            $table->string('utm_medium', 128)->nullable();
            $table->string('utm_campaign', 128)->nullable();
            $table->string('utm_content', 128)->nullable();
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('unique_clicks')->default(0);
            $table->unsignedBigInteger('conversions')->default(0);
            $table->decimal('revenue', 12, 2)->default(0);
            $table->string('device', 32)->nullable();   // mobile, desktop, tablet
            $table->string('country', 8)->nullable();
            $table->string('referrer', 512)->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'platform']);
            $table->index(['tenant_id', 'clicked_at']);
            $table->index('short_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_click_tracking');
    }
};
