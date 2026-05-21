<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('ai_provider', 32);          // openai | claude | gemini
            $table->string('ai_model', 64);
            $table->string('request_type', 64);         // caption | hashtag | seo | ad_copy | ...
            $table->text('prompt');
            $table->text('system_prompt')->nullable();
            $table->longText('response')->nullable();
            $table->unsignedInteger('tokens_input')->default(0);
            $table->unsignedInteger('tokens_output')->default(0);
            $table->unsignedInteger('tokens_used')->default(0);
            $table->decimal('cost_estimate', 10, 6)->default(0);
            $table->unsignedInteger('processing_time_ms')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'user_id', 'request_type']);
            $table->index(['tenant_id', 'status', 'created_at']);
            $table->index('uuid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_requests');
    }
};
