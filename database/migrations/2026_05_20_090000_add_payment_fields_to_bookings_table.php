<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Payment method: 1=gateway, 2=wallet, 3=wallet+gateway
            $table->unsignedTinyInteger('payment_method')->nullable()->after('payment_status');

            // Gateway tracking
            $table->string('gateway_order_id', 100)->nullable()->after('payment_method');
            $table->string('gateway_payment_id', 100)->nullable()->after('gateway_order_id');

            // Split tracking — every penny accounted for
            $table->decimal('wallet_amount_used', 12, 2)->default(0)->after('gateway_payment_id');
            $table->decimal('gateway_amount', 12, 2)->default(0)->after('wallet_amount_used');

            // Refund tracking
            $table->decimal('refund_amount', 12, 2)->default(0)->after('gateway_amount');
            $table->decimal('nurse_payout_amount', 12, 2)->default(0)->after('refund_amount');

            // Indexes
            $table->index('gateway_order_id');
            $table->index('gateway_payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['gateway_order_id']);
            $table->dropIndex(['gateway_payment_id']);
            $table->dropColumn([
                'payment_method',
                'gateway_order_id',
                'gateway_payment_id',
                'wallet_amount_used',
                'gateway_amount',
                'refund_amount',
                'nurse_payout_amount',
            ]);
        });
    }
};
