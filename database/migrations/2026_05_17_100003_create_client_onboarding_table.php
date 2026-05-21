<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_onboarding', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_client_id')->constrained('agency_clients')->cascadeOnDelete();
            $table->string('onboarding_step', 50)->index(); // profile, branding, social, team, content, billing
            $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped'])->default('pending')->index();
            $table->integer('step_order')->default(0);
            $table->json('step_data')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['agency_client_id', 'onboarding_step']);
            $table->index(['agency_client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_onboarding');
    }
};
