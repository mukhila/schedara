<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('template_name', 100);
            $table->string('type', 80)->index();                            // e.g. post.approved
            $table->string('channel', 20);                                  // email|push|whatsapp|slack|sms
            $table->string('subject', 200)->nullable();                     // for email
            $table->text('message_template');                               // body with {{variables}}
            $table->json('variables')->nullable();                          // declared variable names
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'type', 'channel']);
            $table->index(['channel', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
