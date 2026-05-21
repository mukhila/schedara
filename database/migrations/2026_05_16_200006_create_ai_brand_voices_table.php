<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_brand_voices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name', 128);
            $table->text('description')->nullable();
            $table->string('industry', 128)->nullable();
            $table->json('tone_attributes');           // ['professional', 'friendly', 'witty']
            $table->json('brand_keywords')->nullable();
            $table->text('example_content')->nullable();
            $table->text('custom_instructions')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_brand_voices');
    }
};
