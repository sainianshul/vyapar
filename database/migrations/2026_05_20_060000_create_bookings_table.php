<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->string('reference_id', 20)->unique();

            $table->foreignId('care_request_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('bid_id')
                ->constrained('request_bids')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedBigInteger('nurse_id');
            $table->foreign('nurse_id')->references('id')->on('nurse_profiles')->cascadeOnDelete();

            // Financials — snapshot from the selected bid
            $table->decimal('nurse_amount', 10, 2);
            $table->decimal('commission_amount', 10, 2);
            $table->decimal('total_amount', 10, 2);

            // Schedule — snapshot from the care request
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Session tracking
            $table->unsignedInteger('total_sessions')->default(1);
            $table->unsignedInteger('completed_sessions')->default(0);

            // Status: 0=pending_payment, 1=confirmed, 2=active, 3=completed, 4=cancelled
            $table->unsignedTinyInteger('status')->default(0);

            // Payment: 0=unpaid, 1=paid, 2=refund_initiated, 3=refunded
            $table->unsignedTinyInteger('payment_status')->default(0);

            // Cancellation tracking
            $table->unsignedTinyInteger('cancelled_by')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            // Extension link
            $table->unsignedBigInteger('parent_booking_id')->nullable();
            $table->foreign('parent_booking_id')->references('id')->on('bookings')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('payment_status');
            $table->index(['user_id', 'status']);
            $table->index(['nurse_id', 'status']);
            $table->index(['care_request_id']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
