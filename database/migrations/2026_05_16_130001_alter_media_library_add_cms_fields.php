<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_library', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->string('original_name')->nullable()->after('name');
            $table->string('disk', 20)->default('local')->after('s3_key');
            $table->string('thumbnail_path')->nullable()->after('disk');
            $table->string('thumbnail_url')->nullable()->after('thumbnail_path');
            $table->string('mime_type', 100)->nullable()->after('thumbnail_url');
            $table->string('extension', 20)->nullable()->after('mime_type');
            $table->unsignedInteger('duration')->nullable()->after('height'); // seconds
            $table->json('metadata')->nullable()->after('duration');
            $table->string('optimization_status', 20)->default('pending')->after('metadata');
            $table->string('compression_status', 20)->default('na')->after('optimization_status');
            $table->string('approval_status', 20)->default('draft')->after('compression_status');
            $table->unsignedSmallInteger('version')->default(1)->after('approval_status');
            $table->boolean('is_favorite')->default(false)->after('version');
            $table->string('share_token', 64)->nullable()->unique()->after('is_favorite');
            $table->timestamp('share_expires_at')->nullable()->after('share_token');

            // Re-associate folder_id → media_folders (drop old free-floating reference)
            // We drop the old index first, then add a proper FK
            $table->dropIndex(['folder_id']);
        });

        // Now add the FK to media_folders
        Schema::table('media_library', function (Blueprint $table) {
            $table->foreignId('folder_id')->nullable()->change();
            $table->foreign('folder_id')->references('id')->on('media_folders')->nullOnDelete();
            $table->index('approval_status');
            $table->index('optimization_status');
        });
    }

    public function down(): void
    {
        Schema::table('media_library', function (Blueprint $table) {
            $table->dropForeign(['folder_id']);
            $table->dropColumn([
                'uuid', 'original_name', 'disk', 'thumbnail_path', 'thumbnail_url',
                'mime_type', 'extension', 'duration', 'metadata',
                'optimization_status', 'compression_status', 'approval_status',
                'version', 'is_favorite', 'share_token', 'share_expires_at',
            ]);
            $table->dropIndex(['approval_status']);
            $table->dropIndex(['optimization_status']);
            $table->index('folder_id');
        });
    }
};
