<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_analytics', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete()->after('id');
            $table->unsignedBigInteger('likes')->default(0)->after('impressions');
            $table->unsignedBigInteger('comments')->default(0)->after('likes');
            $table->unsignedBigInteger('shares')->default(0)->after('comments');
            $table->unsignedBigInteger('clicks')->default(0)->after('shares');
            $table->unsignedBigInteger('website_clicks')->default(0)->after('clicks');
            $table->unsignedBigInteger('unfollows')->default(0)->after('following');
            $table->decimal('engagement_rate', 8, 4)->default(0)->after('website_clicks');
            $table->decimal('revenue', 12, 2)->default(0)->after('engagement_rate');
        });

        Schema::table('account_analytics', function (Blueprint $table) {
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('account_analytics', function (Blueprint $table) {
            $table->dropColumn(['likes', 'comments', 'shares', 'clicks', 'website_clicks', 'unfollows', 'engagement_rate', 'revenue']);
            $table->dropConstrainedForeignId('tenant_id');
        });
    }
};
