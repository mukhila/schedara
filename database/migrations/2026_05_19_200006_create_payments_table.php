<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unsignedBigInteger('invoice_id')->nullable()->index();
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->string('payment_gateway', 32);       // stripe|razorpay|paypal|manual
            $table->string('transaction_id')->nullable()->index();
            $table->unsignedBigInteger('amount');        // in minor currency units
            $table->string('currency', 3)->default('usd');
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded', 'cancelled', 'disputed'])
                  ->default('pending');
            $table->json('gateway_response')->nullable();
            $table->string('failure_reason')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'payment_status']);
            $table->index(['payment_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
