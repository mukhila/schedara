<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('action', 100)->index();      // e.g. subscription.created, payment.failed
            $table->string('gateway', 32)->nullable();   // stripe|razorpay|paypal
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->enum('status', ['success', 'failed', 'pending'])->default('success');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'action']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_logs');
    }
};
