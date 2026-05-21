<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_media', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_library_id')->nullable()->constrained('media_library')->nullOnDelete();
            $table->enum('media_type', ['image', 'video', 'gif', 'audio'])->default('image');
            $table->string('disk', 20)->default('local');      // local|s3
            $table->string('file_path');                        // relative path on disk
            $table->string('file_url')->nullable();             // public/signed URL
            $table->string('thumbnail_path')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0); // bytes
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->decimal('duration', 8, 2)->nullable();      // seconds (video/audio)
            $table->boolean('is_watermarked')->default(false);
            $table->string('watermark_path')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->enum('processing_status', ['pending', 'processing', 'done', 'failed'])->default('done');
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['post_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_media');
    }
};
