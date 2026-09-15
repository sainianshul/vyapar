<?php

namespace App\Services\Nurse;

use App\Exceptions\Nurse\NurseProfileException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProfileService
{
    /**
     * Update the nurse's profile (post-onboarding).
     *
     * @param User $user
     * @param array $data Validated data (bio, available_from, available_to, available_days, timezone)
     * @return \App\Models\NurseProfile
     * @throws NurseProfileException
     */
    public function updateProfile(User $user, array $data)
    {
        $nurseProfile = $user->nurseProfile;

        if (!$nurseProfile) {
            throw new NurseProfileException('Nurse profile not found.', 404);
        }

        // Only approved nurses can use this service.
        if (!$nurseProfile->isApproved()) {
            throw new NurseProfileException('Your profile must be approved to perform this action.', 403);
        }

        try {
            DB::beginTransaction();

            $updateData = [];

            if (array_key_exists('bio', $data)) {
                $updateData['bio'] = $data['bio'];
            }
            if (array_key_exists('available_from', $data)) {
                $updateData['available_from'] = $data['available_from'];
            }
            if (array_key_exists('available_to', $data)) {
                $updateData['available_to'] = $data['available_to'];
            }
            if (array_key_exists('available_days', $data)) {
                $updateData['available_days'] = $data['available_days'];
            }
            if (array_key_exists('timezone', $data)) {
                $updateData['timezone'] = $data['timezone'];
            }

            $nurseProfile->update($updateData);

            DB::commit();

            return $nurseProfile->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Nurse Profile Update Failed: ' . $e->getMessage(), ['user_id' => $user->id]);
            throw new NurseProfileException('Failed to update profile. Please try again.');
        }
    }

    /**
     * Toggle the nurse's availability status.
     *
     * @param User $user
     * @param bool $isAvailable
     * @return \App\Models\NurseProfile
     * @throws NurseProfileException
     */
    public function toggleAvailability(User $user, bool $isAvailable)
    {
        $nurseProfile = $user->nurseProfile;

        if (!$nurseProfile) {
            throw new NurseProfileException('Nurse profile not found.', 404);
        }

        if (!$nurseProfile->isApproved()) {
            throw new NurseProfileException('Your profile must be approved to perform this action.', 403);
        }

        try {
            $nurseProfile->update([
                'is_available' => $isAvailable,
            ]);

            return $nurseProfile->fresh();
        } catch (\Exception $e) {
            Log::error('Nurse Availability Toggle Failed: ' . $e->getMessage(), ['user_id' => $user->id]);
            throw new NurseProfileException('Failed to update availability status.');
        }
    }
}
