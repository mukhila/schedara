<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('uuid')->nullable()->unique()->after('id');
            $table->string('type', 32)->default('text')->after('title');        // text|image|video|carousel|reel|shorts
            $table->text('caption')->nullable()->after('content');               // AI-optimised caption (content = raw, caption = final)
            $table->string('timezone', 64)->default('UTC')->after('scheduled_at');
            $table->boolean('is_evergreen')->default(false)->after('timezone');
            $table->boolean('auto_repost')->default(false)->after('is_evergreen');
            $table->unsignedSmallInteger('repost_frequency')->nullable()->after('auto_repost'); // days
            $table->timestamp('next_repost_at')->nullable()->after('repost_frequency');
            $table->decimal('best_time_score', 5, 2)->nullable()->after('next_repost_at');
            $table->json('ai_metadata')->nullable()->after('best_time_score');   // AI caption context, tokens used, etc.
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'type', 'caption', 'timezone', 'is_evergreen', 'auto_repost', 'repost_frequency', 'next_repost_at', 'best_time_score', 'ai_metadata']);
        });
    }
};
