<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('wallet_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedBigInteger('booking_id')->nullable();
            $table->foreign('booking_id')->references('id')->on('bookings')->nullOnDelete();

            // 1=credit, 2=debit
            $table->unsignedTinyInteger('type');

            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2);

            // Integer constant — see WalletTransaction model for list
            $table->unsignedTinyInteger('reason');

            $table->text('description')->nullable();

            // UUID for payment gateway tracking
            $table->uuid('reference_id');

            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index(['wallet_id', 'type']);
            $table->index(['wallet_id', 'reason']);
            $table->index('booking_id');
            $table->index('reference_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
