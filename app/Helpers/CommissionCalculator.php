<?php

namespace App\Helpers;

use App\Models\CareType;

/**
 * Single source of truth for commission calculations.
 *
 * Supports all commission types defined in CareType:
 *   - COMMISION_TYPE_FIXED_PER_DAY (0): Fixed amount × number of days
 *   - COMMISION_TYPE_PERCENT (1): Percentage of nurse amount
 *   - COMMISION_TYPE_FLAT_FIXED (2): Flat fixed amount for whole booking
 */
class CommissionCalculator
{
    /**
     * Calculate commission amount.
     *
     * @param float $nurseAmount   The nurse's requested amount
     * @param int   $type          Commission type constant (from CareType)
     * @param float $value         Commission value (percentage or flat amount)
     * @param int   $totalDays     Number of days (only used for FIXED_PER_DAY type)
     * @return float
     */
    public static function calculate(float $nurseAmount, int $type, float $value, int $totalDays = 1): float
    {
        return match ($type) {
            CareType::COMMISION_TYPE_FIXED_PER_DAY => round($value * $totalDays, 2),
            CareType::COMMISION_TYPE_PERCENT       => round($nurseAmount * ($value / 100), 2),
            CareType::COMMISION_TYPE_FLAT_FIXED     => round($value, 2),
            default => 0,
        };
    }

    /**
     * Calculate total amount (nurse amount + commission).
     *
     * @param float $nurseAmount
     * @param int   $type
     * @param float $value
     * @param int   $totalDays
     * @return array{commission_amount: float, total_amount: float}
     */
    public static function calculateWithTotal(float $nurseAmount, int $type, float $value, int $totalDays = 1): array
    {
        $commissionAmount = self::calculate($nurseAmount, $type, $value, $totalDays);

        return [
            'commission_amount' => $commissionAmount,
            'total_amount' => round($nurseAmount + $commissionAmount, 2),
        ];
    }
}
