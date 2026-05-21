<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 32);          // instagram|facebook|twitter|linkedin|tiktok|youtube|threads|pinterest
            $table->string('account_id');            // platform's native user/page ID
            $table->string('account_name');
            $table->string('avatar')->nullable();
            $table->text('access_token');            // encrypted at model level
            $table->text('refresh_token')->nullable();
            $table->json('scopes')->nullable();      // granted OAuth scopes
            $table->timestamp('expires_at')->nullable();
            $table->string('status', 32)->default('active'); // active|expired|revoked|error
            $table->softDeletes();
            $table->timestamps();

            $table->index(['tenant_id', 'platform']);
            $table->unique(['tenant_id', 'platform', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
