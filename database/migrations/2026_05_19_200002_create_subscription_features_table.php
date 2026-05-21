<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_features', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id')->index();
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');
            $table->string('feature_name', 80);      // e.g. "social_accounts"
            $table->string('feature_value', 40);     // e.g. "10", "unlimited", "true"
            $table->string('feature_label', 120)->nullable();
            $table->boolean('is_highlighted')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['plan_id', 'feature_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_features');
    }
};
