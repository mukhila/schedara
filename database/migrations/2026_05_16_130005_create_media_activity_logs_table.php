<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_file_id')->nullable()->constrained('media_library')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60);            // uploaded|optimized|compressed|approved|rejected|deleted|tagged|moved
            $table->string('platform')->nullable();
            $table->json('response')->nullable();
            $table->string('status', 20)->default('success');
            $table->text('message')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['media_file_id', 'action']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_activity_logs');
    }
};
