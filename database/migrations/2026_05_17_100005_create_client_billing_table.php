<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_billing', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('agency_client_id')->constrained('agency_clients')->cascadeOnDelete();
            $table->string('invoice_number', 50)->unique()->index();
            $table->string('subscription_plan', 100)->nullable();
            $table->string('provider', 20)->default('stripe')->index(); // stripe, razorpay, paypal, manual
            $table->string('provider_invoice_id')->nullable()->index();
            $table->unsignedBigInteger('amount')->default(0); // in minor units (cents/paise)
            $table->unsignedBigInteger('tax')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->string('currency', 3)->default('USD');
            $table->enum('payment_status', ['draft', 'open', 'paid', 'void', 'uncollectible', 'overdue'])
                  ->default('draft')->index();
            $table->date('due_date')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->json('line_items')->nullable();
            $table->json('meta')->nullable();
            $table->text('notes')->nullable();
            $table->string('pdf_url')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['agency_client_id', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_billing');
    }
};
