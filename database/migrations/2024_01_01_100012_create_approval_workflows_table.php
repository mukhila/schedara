<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            /*
             * stages JSON structure:
             * [
             *   {"stage": 1, "name": "Copy review", "approver_id": 5,
             *    "approved_at": null, "comment": null},
             *   {"stage": 2, "name": "Brand sign-off", "approver_id": 12, ...}
             * ]
             */
            $table->json('stages');
            $table->unsignedTinyInteger('current_stage')->default(1);
            $table->string('status', 32)->default('pending');
            // pending|in_review|approved|rejected|cancelled
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index('post_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_workflows');
    }
};
