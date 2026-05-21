<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_demographics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('social_account_id')->constrained('social_accounts')->cascadeOnDelete();
            $table->string('platform', 32);
            $table->date('date');
            $table->string('dimension', 32);       // age, gender, country, city, language, device
            $table->string('dimension_value', 128); // 18-24, male, US, New York, en, mobile
            $table->unsignedBigInteger('count')->default(0);
            $table->decimal('percentage', 6, 2)->default(0);
            $table->timestamps();

            $table->unique(['social_account_id', 'date', 'dimension', 'dimension_value'], 'analytics_demo_unique');
            $table->index(['tenant_id', 'platform', 'date']);
            $table->index(['tenant_id', 'dimension']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_demographics');
    }
};
