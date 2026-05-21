<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hashtags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('hashtag', 100);                     // without # prefix
            $table->string('group_name', 80)->nullable();       // for saved groups
            $table->unsignedInteger('usage_count')->default(0);
            $table->decimal('avg_engagement', 8, 4)->nullable();
            $table->boolean('is_trending')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'hashtag']);
            $table->index(['tenant_id', 'group_name']);
            $table->index(['tenant_id', 'usage_count']);
        });

        Schema::create('post_hashtags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hashtag_id')->constrained()->cascadeOnDelete();
            $table->unique(['post_id', 'hashtag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_hashtags');
        Schema::dropIfExists('hashtags');
    }
};
