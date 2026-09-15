<?php

namespace App\Helpers;

use App\Models\CareRequest;
use Illuminate\Support\Carbon;

/**
 * Calculates when the bidding window closes for a care request.
 *
 * Rules:
 *   - If lead time < 24 hours: Bidding closes 2 hours before session start
 *   - If lead time >= 24 hours: Bidding window stays open for 24 hours
 *   - Safety: never returns a past timestamp
 */
class BiddingWindow
{
    /**
     * Calculate when bidding closes for a care request.
     *
     * @param CareRequest $careRequest
     * @return Carbon
     */
    public static function calculateExpiry(CareRequest $careRequest): Carbon
    {
        $now = now();

        $sessionStart = Carbon::parse(
            $careRequest->start_date->format('Y-m-d') . ' ' . $careRequest->start_time
        );

        $hoursUntilSession = $now->diffInHours($sessionStart, false);

        if ($hoursUntilSession < 24) {
            // Urgent request: close bidding 2 hours before session start
            $expiry = $sessionStart->copy()->subHours(2);
        } else {
            // Normal request: 24-hour bidding window from now
            $expiry = $now->copy()->addHours(24);
        }

        // Safety: never expire in the past
        if ($expiry->isPast()) {
            $expiry = $sessionStart;
        }

        return $expiry;
    }
}
