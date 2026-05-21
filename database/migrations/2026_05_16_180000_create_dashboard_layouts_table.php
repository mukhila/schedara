<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 128)->default('default');

            // Ordered array of widget keys
            $table->json('widgets_order')->nullable();

            // Array of widget keys that the user has hidden
            $table->json('widgets_hidden')->nullable();

            // Per-widget settings (date range overrides, etc.)
            $table->json('widgets_config')->nullable();

            $table->boolean('is_default')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'tenant_id', 'name']);
            $table->index(['user_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_layouts');
    }
};
