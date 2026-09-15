<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();

            // Polymorphic — can be linked to booking, withdrawal, etc.
            $table->string('loggable_type', 100);
            $table->unsignedBigInteger('loggable_id');

            // Gateway identifiers
            $table->string('gateway_name', 50);          // 'razorpay', 'stripe', etc.
            $table->string('gateway_order_id', 100)->nullable();
            $table->string('gateway_payment_id', 100)->nullable();
            $table->string('gateway_payout_id', 100)->nullable();
            $table->string('gateway_refund_id', 100)->nullable();
            $table->string('gateway_signature', 255)->nullable();

            // Event type: 1=order_created, 2=payment_success, 3=payment_failed,
            //             4=refund_initiated, 5=refund_completed, 6=payout_initiated,
            //             7=payout_completed, 8=payout_failed, 9=webhook_received
            $table->unsignedTinyInteger('event_type');

            // Financial snapshot at the time of this event
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('INR');

            // Status from gateway
            $table->string('gateway_status', 50)->nullable();

            // Full gateway response stored as JSON for audit
            $table->json('gateway_response')->nullable();

            // HTTP metadata for webhooks
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index(['loggable_type', 'loggable_id']);
            $table->index('gateway_order_id');
            $table->index('gateway_payment_id');
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
