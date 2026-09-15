<?php
$file = __DIR__ . '/app/Services/CancellationService.php';
$content = file_get_contents($file);

// Inject PaymentGatewayInterface
if (!str_contains($content, 'use App\Contracts\PaymentGatewayInterface;')) {
    $content = str_replace(
        'use App\Models\WalletTransaction;',
        "use App\Contracts\PaymentGatewayInterface;\nuse App\Models\WalletTransaction;",
        $content
    );
}

$content = preg_replace(
    '/protected WalletService \$walletService;/',
    "protected WalletService \$walletService;\n    protected PaymentGatewayInterface \$gateway;",
    $content
);

$content = preg_replace(
    '/public function __construct\(WalletService \$walletService\)\s*\{\s*\$this->walletService = \$walletService;\s*\}/',
    'public function __construct(WalletService $walletService, PaymentGatewayInterface $gateway)
    {
        $this->walletService = $walletService;
        $this->gateway = $gateway;
    }',
    $content
);

// Replace User cancellation refund
$content = preg_replace(
    '/\/\/\s*Process refund based on user\'s chosen mode.*?\/\/ For REFUND_TO_BANK: amount is recorded, admin processes the bank transfer manually\s*\}/s',
    '// Process gateway refund for patient
            if ($refundAmount > 0) {
                if ($booking->gateway_payment_id) {
                    $amountInPaise = (int) round($refundAmount * 100);
                    try {
                        $this->gateway->createRefund($booking->gateway_payment_id, $amountInPaise, "User cancelled booking " . $booking->reference_id);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error("Gateway refund failed during user cancellation", [
                            "booking_id" => $booking->id,
                            "error" => $e->getMessage()
                        ]);
                        // Proceeding with cancellation even if refund fails (can be handled manually or retried)
                    }
                }
            }',
    $content
);

// Replace Nurse cancellation refund
$content = preg_replace(
    '/if \(\$refundAmount > 0\) \{\s*\$this->walletService->credit\(\s*\$booking->user_id,\s*\$refundAmount,\s*WalletTransaction::REASON_CANCELLATION_REFUND,\s*"Full refund — nurse cancelled booking \{\$booking->reference_id\}",\s*\$booking->id\s*\);\s*\}/s',
    'if ($refundAmount > 0) {
                    if ($booking->gateway_payment_id) {
                        $amountInPaise = (int) round($refundAmount * 100);
                        try {
                            $this->gateway->createRefund($booking->gateway_payment_id, $amountInPaise, "Nurse cancelled booking " . $booking->reference_id);
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error("Gateway refund failed during nurse cancellation", [
                                "booking_id" => $booking->id,
                                "error" => $e->getMessage()
                            ]);
                        }
                    }
                }',
    $content
);

// Remove $refundMode from performCancel signature
$content = preg_replace(
    '/int \$refundMode = Booking::REFUND_TO_WALLET/',
    '',
    $content
);

// Remove refund_mode from array return
$content = preg_replace(
    '/\'refund_mode\' => \$refundMode,\s*\'refund_mode_name\' => Booking::getRefundModeList\(\)\[\$refundMode\] \?\? \'Unknown\',/',
    '',
    $content
);

file_put_contents($file, $content);
echo "CancellationService updated.\n";
