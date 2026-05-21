<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_integrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('provider_name', 60)->unique();           // openai|stripe|razorpay|twilio|fcm|meta|google|slack
            $table->string('display_name', 100);
            $table->text('api_key')->nullable();                     // stored encrypted
            $table->text('api_secret')->nullable();                  // stored encrypted
            $table->string('environment', 20)->default('production'); // sandbox|production
            $table->string('status', 20)->default('inactive')->index(); // active|inactive|error
            $table->unsignedBigInteger('usage_limit')->nullable();
            $table->unsignedBigInteger('current_usage')->default(0);
            $table->unsignedBigInteger('monthly_cost_cents')->default(0);
            $table->timestamp('last_checked_at')->nullable();
            $table->text('last_error')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_integrations');
    }
};
