<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('price_monthly')->default(0); // in cents
            $table->unsignedInteger('price_yearly')->default(0);  // in cents
            $table->json('features')->nullable();  // {"ai_captions": true, "inbox": true, ...}
            $table->json('limits')->nullable();    // {"posts_per_month": 100, "channels": 5, ...}
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
