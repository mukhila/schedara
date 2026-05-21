<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_file_id')->constrained('media_library')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->unsignedSmallInteger('version');
            $table->string('file_path');
            $table->string('file_url');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->text('change_note')->nullable();
            $table->timestamps();

            $table->unique(['media_file_id', 'version']);
            $table->index('media_file_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_versions');
    }
};
