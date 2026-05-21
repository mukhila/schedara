<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->nullable()->constrained('notifications')->nullOnDelete();
            $table->string('channel', 20)->index();                          // email|push|whatsapp|slack|sms
            $table->string('recipient', 255)->nullable();                    // email address / phone / token
            $table->string('provider', 50)->nullable();                      // sendgrid|fcm|twilio|slack|vonage
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->string('delivery_status', 20)->default('pending')->index(); // pending|sent|delivered|failed|bounced
            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('attempts')->default(1);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['channel', 'delivery_status']);
            $table->index(['delivery_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
