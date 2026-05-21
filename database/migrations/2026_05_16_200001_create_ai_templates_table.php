<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('template_name', 128);
            $table->enum('template_type', [
                'caption', 'hashtag', 'content_ideas', 'seo', 'ad_copy', 'response', 'campaign', 'custom',
            ]);
            $table->text('description')->nullable();
            $table->text('prompt_template');        // Supports {variable} placeholders
            $table->json('variables')->nullable();  // ['topic','tone','platform']
            $table->string('ai_provider', 32)->nullable();
            $table->string('ai_model', 64)->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_system')->default(false);
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'template_type']);
            $table->index(['tenant_id', 'is_public']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_templates');
    }
};
