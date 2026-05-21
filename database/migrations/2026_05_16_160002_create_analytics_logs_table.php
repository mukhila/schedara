<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('action', 128);                // sync_metrics, generate_report, export_csv, etc.
            $table->string('platform', 32)->nullable();
            $table->string('status', 32)->default('success'); // success, error, warning
            $table->json('response')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('duration_ms')->nullable(); // execution time
            $table->timestamps();

            $table->index(['tenant_id', 'action']);
            $table->index(['tenant_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_logs');
    }
};
