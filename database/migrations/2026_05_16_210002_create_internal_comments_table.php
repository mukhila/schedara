<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_comments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('post_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('collaboration_tasks')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('internal_comments')->nullOnDelete();
            $table->text('comment');
            $table->json('attachments')->nullable();
            $table->json('mentions')->nullable();
            $table->json('reactions')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'post_id']);
            $table->index(['tenant_id', 'task_id']);
            $table->index(['parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_comments');
    }
};
