<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->timestamp('start_at');
            $table->timestamp('end_at')->nullable();
            $table->string('color', 10)->default('#65a1d8');
            $table->json('platforms')->nullable();
            $table->string('status', 32)->default('scheduled');
            $table->boolean('all_day')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'start_at']);
            $table->index('post_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
