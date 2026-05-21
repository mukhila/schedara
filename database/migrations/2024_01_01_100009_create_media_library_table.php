<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_library', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('type', 32);       // image|video|gif|document
            $table->string('url');            // public CDN URL
            $table->string('s3_key');         // raw S3 object key
            $table->unsignedBigInteger('size')->default(0); // bytes
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->string('alt_text')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('folder_id')->nullable(); // self-referencing folder tree
            $table->softDeletes();
            $table->timestamps();

            $table->index(['tenant_id', 'type']);
            $table->index('folder_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_library');
    }
};
