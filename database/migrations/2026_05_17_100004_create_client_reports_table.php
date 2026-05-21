<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('client_workspace_id')->constrained('client_workspaces')->cascadeOnDelete();
            $table->string('report_name');
            $table->string('report_type', 50)->index(); // social, engagement, roi, campaign, seo, ai_insights
            $table->enum('format', ['pdf', 'excel', 'csv'])->default('pdf');
            $table->string('file_url')->nullable();
            $table->string('file_path')->nullable();
            $table->json('report_config')->nullable();
            $table->json('report_data')->nullable();
            $table->enum('status', ['pending', 'generating', 'ready', 'failed'])->default('pending')->index();
            $table->foreignId('generated_by')->constrained('users');
            $table->timestamp('generated_at')->nullable();
            $table->boolean('is_scheduled')->default(false);
            $table->string('schedule_cron')->nullable();
            $table->boolean('email_delivery')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_workspace_id', 'status']);
            $table->index(['client_workspace_id', 'report_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_reports');
    }
};
