<?php

namespace App\Services;

use App\Models\CareRequest;
use App\Models\NurseProfile;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * Finds eligible service providers (nurses, future: doctors) for care requests.
 *
 * This service is the single source of truth for provider matching logic.
 * All matching queries live here — not in models, not in controllers.
 *
 * Return contract: Always returns a Collection of profile models with a `distance` attribute.
 */
class ProviderMatchingService
{
    /**
     * Find eligible nurses for a care request based on distance, service type, and availability.
     *
     * @param CareRequest $careRequest
     * @param float $radiusKm          Maximum distance in kilometers
     * @param float $flexibilityHours  Time flexibility in hours
     * @return Collection<NurseProfile>  Each item has a `distance` attribute (in km)
     */
    public function findEligibleNurses(CareRequest $careRequest, float $radiusKm = 5, float $flexibilityHours = 0): Collection
    {
        $lat = $careRequest->latitude;
        $lng = $careRequest->longitude;
        $careTypeId = $careRequest->care_type_id;

        $distanceQuery = "ST_Distance_Sphere(nurse_profiles.location, ST_GeomFromText('POINT({$lng} {$lat})', 4326)) / 1000";

        $requestStartDate = $careRequest->start_date;
        $requestEndDate = $careRequest->end_date ?? $careRequest->start_date;
        $requestStartTime = $careRequest->start_time;
        $requestEndTime = $careRequest->end_time;

        return NurseProfile::query()
            ->select('nurse_profiles.*')
            ->selectRaw("{$distanceQuery} AS distance")

            // 1. Within radius
            ->whereRaw("{$distanceQuery} <= ?", [$radiusKm])

            // 2. Approved and available
            ->where('status', NurseProfile::STATUS_APPROVED)

            // 3. Provides the requested care type
            ->whereHas('careTypes', function ($query) use ($careTypeId) {
                $query->where('care_types.id', $careTypeId);
            })

            // 4. Time availability matches
            ->when($careRequest->start_time && $careRequest->end_time, function ($query) use ($careRequest, $flexibilityHours) {
                $adjustedStartTime = Carbon::parse($careRequest->start_time)->addHours($flexibilityHours)->format('H:i:s');
                $adjustedEndTime = Carbon::parse($careRequest->end_time)->subHours($flexibilityHours)->format('H:i:s');

                $query->whereTime('available_from', '<=', $adjustedStartTime)
                    ->whereTime('available_to', '>=', $adjustedEndTime);
            })

            // 5. Exclude nurses who have conflicting bookings (date+time overlap)
            ->whereDoesntHave('bookings', function ($query) use ($requestStartDate, $requestEndDate, $requestStartTime, $requestEndTime) {
                $query->whereIn('status', [
                    Booking::STATUS_CONFIRMED,
                    Booking::STATUS_ACTIVE,
                    Booking::STATUS_PENDING_PAYMENT,
                ])
                    // Date overlap: booking.start_date <= request.end_date AND booking.end_date >= request.start_date
                    ->where('start_date', '<=', $requestEndDate)
                    ->where('end_date', '>=', $requestStartDate)
                    // Time overlap: booking.start_time < request.end_time AND booking.end_time > request.start_time
                    ->when($requestStartTime && $requestEndTime, function ($q) use ($requestStartTime, $requestEndTime) {
                        $q->where('start_time', '<', $requestEndTime)
                            ->where('end_time', '>', $requestStartTime);
                    });
            })
            ->get();
    }

    /**
     * Run iterative matching with progressively relaxed constraints.
     *
     * Tries each matching level from config/care.php until enough nurses are found.
     * Returns the best set of nurses and the matching level that worked.
     *
     * @param CareRequest $careRequest
     * @param int $minimumNurses   Minimum nurses needed to consider a successful match
     * @return array{nurses: Collection, level: int}
     */
    public function runIterativeMatching(CareRequest $careRequest, int $minimumNurses = 2): array
    {
        $attempts = config('care.matching_attempts', [
            ['radius_km' => 5, 'time_flex_hours' => 0],
        ]);

        $bestNurses = collect();
        $matchedLevel = 0;

        foreach ($attempts as $index => $attempt) {
            $nurses = $this->findEligibleNurses(
                $careRequest,
                $attempt['radius_km'],
                $attempt['time_flex_hours']
            );

            // Found enough — stop immediately
            if ($nurses->count() >= $minimumNurses) {
                return ['nurses' => $nurses, 'level' => $index];
            }

            // Keep the best result so far
            if ($nurses->count() > $bestNurses->count()) {
                $bestNurses = $nurses;
                $matchedLevel = $index;
            }
        }

        return ['nurses' => $bestNurses, 'level' => $matchedLevel];
    }
}
