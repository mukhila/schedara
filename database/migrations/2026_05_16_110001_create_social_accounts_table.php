<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old single-table design (platform stored as string) before recreating
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('social_accounts');
        Schema::enableForeignKeyConstraints();

        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_id')->constrained('social_platforms');
            $table->string('platform_user_id')->index();
            $table->string('account_name');
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('avatar')->nullable();
            $table->text('access_token');          // encrypted at app layer
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->json('scopes')->nullable();    // granted scopes
            $table->json('metadata')->nullable();  // platform-specific extra data
            $table->enum('status', ['active', 'expired', 'revoked', 'error'])->default('active');
            $table->timestamp('last_synced_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['tenant_id', 'platform_id', 'platform_user_id'], 'sa_tenant_platform_user_unique');
            $table->index(['tenant_id', 'platform_id', 'status']);
            $table->index(['token_expires_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
