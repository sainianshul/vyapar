<?php
$file = __DIR__ . '/app/Services/PaymentService.php';
$content = file_get_contents($file);

// Replace initiatePayment
$content = preg_replace(
    '/public function initiatePayment\(Booking \$booking, int \$userId\): array\s*\{.*?(?=public function confirmGatewayPayment)/s',
    'public function initiatePayment(Booking $booking, int $userId): array
    {
        if ($booking->user_id !== $userId) {
            throw new PaymentFailedException(\'Unauthorized.\', 403);
        }

        if ($booking->status !== Booking::STATUS_PENDING_PAYMENT) {
            throw new InvalidBookingStateException(\'This booking is not awaiting payment.\', 409);
        }

        $totalAmount = (float) $booking->total_amount;

        return $this->createGatewayOrder($booking, $totalAmount);
    }

    /**
     * ',
    $content
);

// Replace confirmGatewayPayment wallet debit logic
$content = preg_replace(
    '/\/\/ Debit wallet portion if any.*?\/\/ Determine payment method/s',
    '// Determine payment method',
    $content
);

// Update payment method assignment in confirmGatewayPayment
$content = preg_replace(
    '/\$paymentMethod = \$walletUsed > 0[^;]+;/s',
    '$paymentMethod = Booking::PAY_METHOD_GATEWAY;',
    $content
);

// Remove confirmWithWalletOnly
$content = preg_replace(
    '/\/\*\*.*?Confirm booking using wallet balance only.*?private function confirmWithWalletOnly.*?\}\s*\/\*\*/s',
    '/**',
    $content
);

// Update createGatewayOrder signature and body
$content = preg_replace(
    '/private function createGatewayOrder\(Booking \$booking, float \$walletDeduction, float \$gatewayAmount\): array/s',
    'private function createGatewayOrder(Booking $booking, float $gatewayAmount): array',
    $content
);

$content = preg_replace(
    '/\'wallet_amount_used\'\s*=>\s*\$walletDeduction,/s',
    '\'wallet_amount_used\' => 0,',
    $content
);

$content = preg_replace(
    '/\'payment_method\'\s*=>\s*\$walletDeduction > 0 \? \'wallet\+gateway\' : \'gateway\',/s',
    '\'payment_method\' => \'gateway\',',
    $content
);

file_put_contents($file, $content);
echo "PaymentService updated.\n";
