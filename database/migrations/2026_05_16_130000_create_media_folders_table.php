<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('media_folders')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('path');                     // full path: /Design/Banners
            $table->string('color', 7)->default('#6366F1');
            $table->boolean('is_shared')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['tenant_id', 'parent_id', 'slug']);
            $table->index(['tenant_id', 'parent_id']);
            $table->index(['tenant_id', 'path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_folders');
    }
};
