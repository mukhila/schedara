<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slack_integrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('webhook_url', 512);
            $table->string('channel_name', 100)->default('#general');
            $table->string('workspace_name', 100)->nullable();
            $table->string('team_id', 50)->nullable();
            $table->string('status', 20)->default('active')->index();        // active|inactive
            $table->timestamps();
            $table->softDeletes();

            $table->unique('tenant_id');                                     // one Slack per workspace
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slack_integrations');
    }
};
