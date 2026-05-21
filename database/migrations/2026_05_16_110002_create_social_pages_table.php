<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_pages', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('social_account_id')->constrained()->cascadeOnDelete();
            $table->string('page_id')->index();
            $table->string('page_name');
            $table->string('page_type')->default('page'); // page, channel, board, profile
            $table->string('category')->nullable();
            $table->string('avatar')->nullable();
            $table->text('access_token')->nullable(); // page-level token (Facebook pages)
            $table->unsignedBigInteger('followers_count')->default(0);
            $table->json('metadata')->nullable();
            $table->boolean('is_selected')->default(false); // selected for posting
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['social_account_id', 'page_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_pages');
    }
};
