<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_request_id')->constrained('ai_requests')->cascadeOnDelete();
            $table->string('action', 64);               // attempt | failover | completed | failed | limit_reached
            $table->string('ai_provider', 32)->nullable();
            $table->string('ai_model', 64)->nullable();
            $table->longText('response')->nullable();   // raw response or error message
            $table->enum('status', ['success', 'error', 'skipped'])->default('success');
            $table->unsignedInteger('tokens_used')->default(0);
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['ai_request_id', 'action']);
            $table->index(['ai_provider', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_logs');
    }
};
