<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_platform_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 32);
            $table->text('content_override')->nullable();  // platform-specific caption
            $table->json('media_override')->nullable();    // platform-specific media subset/order
            $table->text('first_comment')->nullable();     // IG/LinkedIn first comment (hashtags etc.)
            $table->string('status', 32)->default('pending'); // pending|published|failed
            $table->string('platform_post_id')->nullable(); // returned ID after publishing
            $table->timestamps();

            $table->unique(['post_id', 'platform']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_platform_configs');
    }
};
