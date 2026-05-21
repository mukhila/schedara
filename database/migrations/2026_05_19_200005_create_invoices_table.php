<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Enhances the existing invoices table with invoice number, tax, totals, and PDF fields.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->string('invoice_number', 30)->nullable()->unique()->after('uuid');
            $table->unsignedBigInteger('tax')->default(0)->after('amount');
            $table->unsignedBigInteger('discount')->default(0)->after('tax');
            $table->unsignedBigInteger('total')->default(0)->after('discount');
            $table->string('tax_rate', 10)->nullable()->after('total');
            $table->string('tax_label', 20)->nullable()->after('tax_rate');
            $table->json('line_items')->nullable()->after('tax_label');
            $table->json('billing_address')->nullable()->after('line_items');
            $table->text('notes')->nullable()->after('billing_address');
            $table->string('invoice_pdf')->nullable()->after('notes');      // local storage path
            $table->timestamp('due_date')->nullable()->after('invoice_pdf');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'uuid', 'invoice_number', 'tax', 'discount', 'total',
                'tax_rate', 'tax_label', 'line_items', 'billing_address',
                'notes', 'invoice_pdf', 'due_date',
            ]);
        });
    }
};
