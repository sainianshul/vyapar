<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Exceptions\Wallet\InvalidWalletAmountException;
use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Exceptions\Wallet\PendingWithdrawalExistsException;
use App\Exceptions\Wallet\WithdrawalNotFoundException;
use App\Exceptions\Wallet\InvalidWithdrawalStateException;
use App\Exceptions\Wallet\WithdrawalFailedException;
use App\Models\PaymentLog;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Handles nurse withdrawal requests (wallet → bank account).
 *
 * Flow:
 *   1. Nurse requests withdrawal → validated, withdrawal_request created (pending)
 *   2. Admin approves → gateway payout API called → money sent to bank
 *   3. On success → nurse wallet debited, withdrawal marked complete
 *
 * Rules:
 *   - Minimum withdrawal amount is configurable
 *   - No duplicate pending requests
 *   - Wallet debited ONLY after payout is confirmed
 *   - Every payout is logged in payment_logs
 */
class WithdrawalService
{
    protected WalletService $walletService;
    protected PaymentGatewayInterface $gateway;

    public function __construct(WalletService $walletService, PaymentGatewayInterface $gateway)
    {
        $this->walletService = $walletService;
        $this->gateway = $gateway;
    }

    /**
     * Nurse requests a withdrawal.
     *
     * @throws WalletException
     */
    public function requestWithdrawal(int $userId, float $amount, array $bankDetails): WithdrawalRequest
    {
        $minAmount = (float) config('care.min_withdrawal_amount', 100);

        if ($amount < $minAmount) {
            throw new InvalidWalletAmountException("Minimum withdrawal amount is ₹{$minAmount}.", 422);
        }

        // Check balance
        $balance = $this->walletService->getBalance($userId);

        if ($balance < $amount) {
            throw new InsufficientBalanceException('Insufficient wallet balance for this withdrawal.', 422);
        }

        // Block duplicate pending requests
        $hasPending = WithdrawalRequest::where('user_id', $userId)
            ->whereIn('status', [
                WithdrawalRequest::STATUS_PENDING,
                WithdrawalRequest::STATUS_PROCESSING,
            ])
            ->exists();

        if ($hasPending) {
            throw new PendingWithdrawalExistsException('You already have a pending withdrawal request.', 409);
        }

        return WithdrawalRequest::create([
            'user_id' => $userId,
            'amount' => round($amount, 2),
            'status' => WithdrawalRequest::STATUS_PENDING,
            'bank_account_name' => $bankDetails['account_name'],
            'bank_account_number' => $bankDetails['account_number'],
            'bank_ifsc' => $bankDetails['ifsc'],
        ]);
    }

    /**
     * Admin processes (approves) a withdrawal request.
     *
     * @throws WalletException
     */
    public function processWithdrawal(int $withdrawalId, int $adminUserId): WithdrawalRequest
    {
        $withdrawal = WithdrawalRequest::find($withdrawalId);

        if (!$withdrawal) {
            throw new WithdrawalNotFoundException('Withdrawal request not found.', 404);
        }

        if (!$withdrawal->isProcessable()) {
            throw new InvalidWithdrawalStateException('This withdrawal cannot be processed.', 409);
        }

        // Re-check balance before processing
        $balance = $this->walletService->getBalance($withdrawal->user_id);

        if ($balance < (float) $withdrawal->amount) {
            $withdrawal->update([
                'status' => WithdrawalRequest::STATUS_FAILED,
                'failure_reason' => 'Insufficient balance at time of processing.',
                'processed_by' => $adminUserId,
                'processed_at' => now(),
            ]);

            throw new InsufficientBalanceException('User has insufficient balance. Withdrawal marked as failed.', 422);
        }

        // Mark as processing
        $withdrawal->update(['status' => WithdrawalRequest::STATUS_PROCESSING]);

        // Initiate payout via gateway
        $amountInPaise = (int) round((float) $withdrawal->amount * 100);

        try {
            $payoutResult = $this->gateway->createPayout(
                $amountInPaise,
                'INR',
                $withdrawal->bank_account_name,
                $withdrawal->bank_account_number,
                $withdrawal->bank_ifsc,
                'WDR-' . $withdrawal->id
            );
        } catch (\Throwable $e) {
            // Log failure
            PaymentLog::record($withdrawal, PaymentLog::EVENT_PAYOUT_FAILED, (float) $withdrawal->amount, [
                'gateway_status' => 'payout_creation_failed',
                'gateway_raw' => ['error' => $e->getMessage()],
            ]);

            $withdrawal->update([
                'status' => WithdrawalRequest::STATUS_FAILED,
                'failure_reason' => 'Gateway payout failed: ' . $e->getMessage(),
                'processed_by' => $adminUserId,
                'processed_at' => now(),
            ]);

            Log::error('Payout creation failed', [
                'withdrawal_id' => $withdrawal->id,
                'error' => $e->getMessage(),
            ]);

            throw new WithdrawalFailedException('Payout failed. The withdrawal has been marked as failed and can be retried.', 502);
        }

        // Payout initiated — now debit wallet and complete
        return DB::transaction(function () use ($withdrawal, $payoutResult, $adminUserId) {

            // Debit nurse wallet
            $this->walletService->debit(
                $withdrawal->user_id,
                (float) $withdrawal->amount,
                WalletTransaction::REASON_WITHDRAWAL,
                "Withdrawal to bank a/c ending " . substr($withdrawal->bank_account_number, -4),
                null
            );

            // Log payout
            PaymentLog::record($withdrawal, PaymentLog::EVENT_PAYOUT_INITIATED, (float) $withdrawal->amount, [
                'gateway_payout_id' => $payoutResult['payout_id'] ?? null,
                'gateway_status' => $payoutResult['status'] ?? 'initiated',
                'gateway_raw' => $payoutResult['gateway_raw'] ?? $payoutResult,
            ]);

            // Update withdrawal
            $withdrawal->update([
                'status' => WithdrawalRequest::STATUS_COMPLETED,
                'gateway_payout_id' => $payoutResult['payout_id'] ?? null,
                'processed_by' => $adminUserId,
                'processed_at' => now(),
            ]);

            return $withdrawal->fresh();
        });
    }

    /**
     * Admin rejects a withdrawal request.
     */
    public function rejectWithdrawal(int $withdrawalId, int $adminUserId, string $reason): WithdrawalRequest
    {
        $withdrawal = WithdrawalRequest::find($withdrawalId);

        if (!$withdrawal) {
            throw new WithdrawalNotFoundException('Withdrawal request not found.', 404);
        }

        if (!$withdrawal->isPending()) {
            throw new InvalidWithdrawalStateException('Only pending withdrawals can be rejected.', 409);
        }

        $withdrawal->update([
            'status' => WithdrawalRequest::STATUS_REJECTED,
            'admin_note' => $reason,
            'processed_by' => $adminUserId,
            'processed_at' => now(),
        ]);

        return $withdrawal->fresh();
    }

    /**
     * Get withdrawal history for a user (paginated).
     */
    public function getWithdrawals(int $userId, int $perPage = 15)
    {
        return WithdrawalRequest::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
