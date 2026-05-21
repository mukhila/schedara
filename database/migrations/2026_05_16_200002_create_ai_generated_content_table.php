<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_generated_content', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_request_id')->nullable()->constrained('ai_requests')->nullOnDelete();
            $table->string('content_type', 64);        // caption | hashtag | ad_copy | seo | ...
            $table->string('platform', 32)->nullable(); // instagram | facebook | ...
            $table->string('title', 255)->nullable();
            $table->longText('generated_content');
            $table->json('variations')->nullable();    // Array of alternative outputs
            $table->json('metadata')->nullable();      // tone, style, keywords, seo_score, etc.
            $table->boolean('is_saved')->default(false);
            $table->boolean('is_used')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'user_id', 'content_type']);
            $table->index(['tenant_id', 'is_saved', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generated_content');
    }
};
