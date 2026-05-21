<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('tag_name', 100);
            $table->string('slug', 100);
            $table->string('color', 7)->default('#6366F1');
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'usage_count']);
        });

        Schema::create('media_file_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_file_id')->constrained('media_library')->cascadeOnDelete();
            $table->foreignId('media_tag_id')->constrained('media_tags')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['media_file_id', 'media_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_file_tags');
        Schema::dropIfExists('media_tags');
    }
};
