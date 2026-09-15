<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('amount', 12, 2);

            // Status: 0=pending, 1=processing, 2=completed, 3=failed, 4=rejected
            $table->unsignedTinyInteger('status')->default(0);

            // Bank details — snapshot at the time of request
            $table->string('bank_account_name', 150);
            $table->string('bank_account_number', 50);
            $table->string('bank_ifsc', 20);

            // Gateway payout tracking
            $table->string('gateway_payout_id', 100)->nullable();

            // Admin/system
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->text('failure_reason')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index('status');
            $table->index('gateway_payout_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
