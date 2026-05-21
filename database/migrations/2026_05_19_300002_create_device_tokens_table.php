<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_type', 20)->default('web');               // web|android|ios
            $table->string('fcm_token', 512)->unique();
            $table->string('browser', 80)->nullable();                       // Chrome|Firefox|Safari
            $table->string('platform', 50)->nullable();                      // Windows|macOS|Android|iOS
            $table->timestamp('last_active_at')->nullable()->index();
            $table->timestamps();

            $table->index(['user_id', 'device_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
