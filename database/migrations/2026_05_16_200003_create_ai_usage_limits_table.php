<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_usage_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('total_tokens_used')->default(0);
            $table->unsignedInteger('monthly_limit')->default(100_000);
            $table->unsignedBigInteger('current_month_usage')->default(0);
            $table->date('reset_date');
            $table->unsignedBigInteger('openai_tokens_used')->default(0);
            $table->unsignedBigInteger('claude_tokens_used')->default(0);
            $table->unsignedBigInteger('gemini_tokens_used')->default(0);
            $table->decimal('total_cost_estimate', 12, 6)->default(0);
            $table->boolean('limit_reached')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_limits');
    }
};
