<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('white_label_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_workspace_id')->unique()->constrained('client_workspaces')->cascadeOnDelete();
            $table->string('brand_name')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('primary_color', 20)->default('#6366F1');
            $table->string('secondary_color', 20)->default('#8B5CF6');
            $table->string('accent_color', 20)->default('#EC4899');
            $table->string('custom_domain')->nullable()->unique()->index();
            $table->boolean('domain_verified')->default(false);
            $table->json('email_settings')->nullable();
            $table->string('login_background')->nullable();
            $table->string('support_email')->nullable();
            $table->string('support_url')->nullable();
            $table->boolean('hide_saas_branding')->default(false);
            $table->json('custom_css')->nullable();
            $table->json('social_links')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('white_label_settings');
    }
};
