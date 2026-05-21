<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');              // connected, disconnected, token_refreshed, synced, error
            $table->string('platform')->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->enum('status', ['success', 'failure', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['social_account_id', 'action', 'created_at']);
            $table->index(['platform', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_logs');
    }
};
