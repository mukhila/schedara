<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agency_clients', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('agency_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('client_name');
            $table->string('company_name')->nullable();
            $table->string('email')->index();
            $table->string('phone', 30)->nullable();
            $table->string('website')->nullable();
            $table->string('industry', 100)->nullable();
            $table->string('logo')->nullable();
            $table->string('timezone', 64)->default('UTC');
            $table->enum('status', ['active', 'inactive', 'onboarding', 'suspended', 'churned'])
                  ->default('onboarding')->index();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['agency_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_clients');
    }
};
