<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('category', 64)->default('system')->after('type');
            $table->string('priority', 16)->default('normal')->after('category');
            $table->string('action_url', 512)->nullable()->after('priority');
            $table->index(['user_id', 'tenant_id', 'read_at'], 'notifications_tenant_unread');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex('notifications_tenant_unread');
            $table->dropColumn(['tenant_id', 'category', 'priority', 'action_url']);
        });
    }
};
