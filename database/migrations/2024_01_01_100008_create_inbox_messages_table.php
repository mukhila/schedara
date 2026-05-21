<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inbox_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('social_account_id')->constrained('social_accounts')->cascadeOnDelete();
            $table->string('platform', 32);
            $table->string('external_id');           // platform's message/comment ID
            $table->string('type', 32);              // comment|dm|mention|reply
            $table->json('from_user');               // {id, name, avatar, username}
            $table->text('content');
            $table->string('sentiment', 16)->nullable(); // positive|neutral|negative
            $table->string('status', 32)->default('unread'); // unread|read|replied|archived|snoozed
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->json('tags')->nullable();
            $table->timestamp('received_at');
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['social_account_id', 'external_id']);
            $table->index(['tenant_id', 'status']);
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbox_messages');
    }
};
