<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_workspaces', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('agency_client_id')->constrained('agency_clients')->cascadeOnDelete();
            $table->string('workspace_name');
            $table->string('domain')->nullable()->unique()->index();
            $table->json('branding_settings')->nullable();
            $table->json('settings')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['agency_client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_workspaces');
    }
};
