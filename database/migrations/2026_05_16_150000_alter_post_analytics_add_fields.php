<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_analytics', function (Blueprint $table) {
            $table->unsignedBigInteger('saves')->default(0)->after('clicks');
            $table->unsignedBigInteger('video_views')->default(0)->after('saves');
            $table->unsignedBigInteger('conversions')->default(0)->after('video_views');
            $table->decimal('engagement_rate', 8, 4)->default(0)->after('conversions');
            $table->decimal('ctr', 8, 4)->default(0)->after('engagement_rate');
            $table->decimal('spend', 12, 2)->default(0)->after('ctr');
            $table->decimal('revenue', 12, 2)->default(0)->after('spend');
            $table->string('platform_post_id', 128)->nullable()->after('platform');
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete()->after('id');
        });

        Schema::table('post_analytics', function (Blueprint $table) {
            $table->index('tenant_id');
            $table->index('fetched_at');
        });
    }

    public function down(): void
    {
        Schema::table('post_analytics', function (Blueprint $table) {
            $table->dropColumn(['saves', 'video_views', 'conversions', 'engagement_rate', 'ctr', 'spend', 'revenue', 'platform_post_id']);
            $table->dropConstrainedForeignId('tenant_id');
        });
    }
};
