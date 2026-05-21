<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('type', 32)->default('custom'); // custom, engagement, follower, campaign, roi, demographic
            $table->string('status', 32)->default('pending'); // pending, processing, ready, failed
            $table->date('date_from');
            $table->date('date_to');
            $table->json('filters')->nullable();             // platforms, accounts, campaigns
            $table->json('metrics')->nullable();             // selected metrics
            $table->string('format', 16)->default('pdf');   // pdf, csv, xlsx
            $table->string('file_path', 512)->nullable();
            $table->string('file_url', 512)->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('summary')->nullable();             // cached summary stats
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_reports');
    }
};
