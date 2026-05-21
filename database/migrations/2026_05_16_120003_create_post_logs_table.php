<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 60);                       // scheduled, published, failed, edited, duplicated, reposted
            $table->string('platform', 32)->nullable();
            $table->json('response')->nullable();               // API response from platform
            $table->enum('status', ['success', 'failure', 'pending'])->default('success');
            $table->text('message')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['post_id', 'action']);
            $table->index(['post_id', 'created_at']);
        });

        Schema::table('post_platform_configs', function (Blueprint $table) {
            $table->foreignId('social_account_id')->nullable()->after('post_id')->constrained('social_accounts')->nullOnDelete();
            $table->json('response_data')->nullable()->after('platform_post_id');
            $table->timestamp('published_at')->nullable()->after('response_data');
        });
    }

    public function down(): void
    {
        Schema::table('post_platform_configs', function (Blueprint $table) {
            $table->dropForeign(['social_account_id']);
            $table->dropColumn(['social_account_id', 'response_data', 'published_at']);
        });
        Schema::dropIfExists('post_logs');
    }
};
