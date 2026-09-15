<?php

namespace App\Services;

use App\Exceptions\Wallet\InvalidWalletAmountException;
use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Single source of truth for all wallet operations.
 *
 * Every financial operation is wrapped in a DB transaction.
 * Every credit/debit records balance_after for audit reconciliation.
 * Wallet balance NEVER goes negative.
 */
class WalletService
{
    /**
     * Get or create a wallet for a user.
     */
    public function getOrCreateWallet(int $userId): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0]
        );
    }

    /**
     * Credit (add money) to a user's wallet.
     *
     * @throws WalletException
     */
    public function credit(int $userId, float $amount, int $reason, string $description = '', ?int $bookingId = null): WalletTransaction
    {
        if ($amount <= 0) {
            throw new InvalidWalletAmountException('Credit amount must be positive.', 422);
        }

        return DB::transaction(function () use ($userId, $amount, $reason, $description, $bookingId) {
            $wallet = $this->getOrCreateWallet($userId);

            // Lock row for update to prevent race conditions
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            $newBalance = round($wallet->balance + $amount, 2);

            $wallet->update(['balance' => $newBalance]);

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'booking_id' => $bookingId,
                'type' => WalletTransaction::TYPE_CREDIT,
                'amount' => round($amount, 2),
                'balance_after' => $newBalance,
                'reason' => $reason,
                'description' => $description,
                'reference_id' => Str::uuid(),
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Debit (remove money) from a user's wallet.
     *
     * @throws WalletException
     */
    public function debit(int $userId, float $amount, int $reason, string $description = '', ?int $bookingId = null): WalletTransaction
    {
        if ($amount <= 0) {
            throw new InvalidWalletAmountException('Debit amount must be positive.', 422);
        }

        return DB::transaction(function () use ($userId, $amount, $reason, $description, $bookingId) {
            $wallet = $this->getOrCreateWallet($userId);

            // Lock row for update to prevent race conditions
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            if ($wallet->balance < $amount) {
                throw new InsufficientBalanceException('Insufficient wallet balance.', 422);
            }

            $newBalance = round($wallet->balance - $amount, 2);

            $wallet->update(['balance' => $newBalance]);

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'booking_id' => $bookingId,
                'type' => WalletTransaction::TYPE_DEBIT,
                'amount' => round($amount, 2),
                'balance_after' => $newBalance,
                'reason' => $reason,
                'description' => $description,
                'reference_id' => Str::uuid(),
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Get wallet balance for a user.
     */
    public function getBalance(int $userId): float
    {
        $wallet = Wallet::where('user_id', $userId)->first();

        return $wallet ? (float) $wallet->balance : 0;
    }

    /**
     * Get transaction history for a user (paginated).
     */
    public function getTransactions(int $userId, int $perPage = 15)
    {
        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$wallet) {
            return collect();
        }

        return WalletTransaction::where('wallet_id', $wallet->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
