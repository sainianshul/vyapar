<?php

namespace App\Services;

use App\Exceptions\Nurse\InvalidOnboardingStepException;
use App\Models\CareTypeNurse;
use App\Models\NurseDocument;
use App\Models\NurseEducation;
use App\Models\NurseProfile;
use App\Models\NurseProfileVerification;
use App\Models\NurseWorkHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Storage;

class OnboardingService
{
    public function saveBasicProfile(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data) {

            $nurseProfile = $user->nurseProfile;

            // If Nurse Profile Not Present Create it
            if (!$nurseProfile) {
                $nurseProfile = NurseProfile::create([
                    'user_id' => $user->id,
                    'onboarding_step' => NurseProfile::STEP_BASIC_PROFILE,
                    'status' => NurseProfile::STATUS_PENDING,
                ]);
            }

            $nurseProfile->canSaveStep(NurseProfile::STEP_BASIC_PROFILE);

            // User update
            $userData = ['email' => $data['email']];

            if (isset($data['profile_photo'])) {
                $photoPath = Storage::disk('public')->put(
                    'users/profile-photos',
                    $data['profile_photo']
                );

                // Upload success ke baad hi purana delete karo
                if ($photoPath && $user->profile_photo) {
                    Storage::disk('public')->delete($user->profile_photo);
                }

                $userData['profile_photo'] = $photoPath;
            }

            $user->update($userData);

            // Nurse profile update
            $nurseProfile->update([
                'bio' => $data['bio'] ?? null,
                'years_of_experience' => $data['years_of_experience'],
                'license_number' => $data['license_number'],
                'license_expiry_date' => $data['license_expiry_date'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'address' => $data['address'],
                'city' => $data['city'],
                'state' => $data['state'],
                'country' => $data['country'],
                'pincode' => $data['pincode'],
            ]);

            $nurseProfile->updateOnboardingStep(NurseProfile::STEP_CARE_TYPES);
        });
    }

    public function saveCareTypes(User $user, array $data)
    {
        $nurseProfile = $user->nurseProfile;
        if (!$nurseProfile) {
            throw new InvalidOnboardingStepException('Nurse profile not found. Please complete basic profile first.');
        }
        $nurseProfile->canSaveStep(NurseProfile::STEP_CARE_TYPES);
        DB::transaction(function () use ($nurseProfile, $data) {

            $attachData = [];
            foreach ($data['care_type_ids'] as $careTypeId) {
                $attachData[$careTypeId] = [
                    'status' => CareTypeNurse::STATUS_ACTIVE,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $nurseProfile->careTypes()->sync($attachData);
            $nurseProfile->updateOnboardingStep(
                NurseProfile::STEP_EDUCATION
            );
        });
    }

    public function saveEducation(User $user, array $data): void
    {
        $nurseProfile = $user->nurseProfile;
        if (!$nurseProfile) {
            throw new InvalidOnboardingStepException('Nurse profile not found. Please complete basic profile first.');
        }
        $nurseProfile->canSaveStep(NurseProfile::STEP_EDUCATION);
        DB::transaction(function () use ($nurseProfile, $data) {

            // Remove Existing Education
            $nurseProfile->educations()->delete();

            // Save Education
            foreach ($data['educations'] as $education) {
                $nurseProfile->educations()->create([
                    'degree_or_course' => $education['degree_or_course'],
                    'institute_name' => $education['institute_name'],
                    'field_of_study' => $education['field_of_study'] ?? null,
                    'start_year' => $education['start_year'],
                    'end_year' => $education['end_year'] ?? null,
                    'is_currently_studying' => $education['is_currently_studying'],
                    'status' => NurseEducation::STATUS_ACTIVE,
                ]);
            }

            $nurseProfile->updateOnboardingStep(
                NurseProfile::STEP_WORK_HISTORY
            );
        });
    }

    public function saveWorkHistory(User $user, array $data): void
    {
        $nurseProfile = $user->nurseProfile;
        if (!$nurseProfile) {
            throw new InvalidOnboardingStepException('Nurse profile not found. Please complete basic profile first.');
        }
        $nurseProfile->canSaveStep(NurseProfile::STEP_WORK_HISTORY);

        DB::transaction(function () use ($nurseProfile, $data) {

            // Remove Existing Work History
            $nurseProfile->workHistories()->delete();

            // Save Work History
            foreach ($data['work_histories'] as $workHistory) {
                $nurseProfile->workHistories()
                    ->create([
                        'role_or_position' => $workHistory['role_or_position'],
                        'organization_name' => $workHistory['organization_name'],
                        'location' => $workHistory['location'] ?? null,
                        'start_date' => $workHistory['start_date'],
                        'end_date' => $workHistory['is_currently_working'] ? null : $workHistory['end_date'],
                        'is_currently_working' => $workHistory['is_currently_working'],
                        'description' => $workHistory['description'] ?? null,
                        'status' => NurseWorkHistory::STATUS_ACTIVE,
                    ]);
            }

            $nurseProfile->updateOnboardingStep(
                NurseProfile::STEP_DOCUMENTS
            );
        });
    }


    public function saveDocuments(
        User $user,
        array $data
    ): void {

        $nurseProfile = $user->nurseProfile;
        if (!$nurseProfile) {
            throw new InvalidOnboardingStepException('Nurse profile not found. Please complete basic profile first.');
        }

        $nurseProfile->canSaveStep(NurseProfile::STEP_DOCUMENTS);

        DB::transaction(function () use ($nurseProfile, $data) {

            // Map request fields to Document Types
            $documents = [
                'aadhar_document' => NurseDocument::TYPE_AADHAR,
                'pan_document' => NurseDocument::TYPE_PAN,
                'marksheet_10_document' => NurseDocument::TYPE_MARKSHEET_10,
                'marksheet_12_document' => NurseDocument::TYPE_MARKSHEET_12,
                'nursing_certificate_document' => NurseDocument::TYPE_NURSING_CERTIFICATE,
                'license_document' => NurseDocument::TYPE_LICENSE,
                'degree_document' => NurseDocument::TYPE_DEGREE,
            ];

            foreach ($documents as $field => $documentType) {

                // File Not Send In request (For Edit)
                if (!isset($data[$field])) {
                    continue;
                }

                // Existing same type document
                $existingDocument = $nurseProfile
                    ->documents()
                    ->where(
                        'document_type',
                        $documentType
                    )
                    ->first();

                if ($existingDocument) {
                    Storage::disk('local')->delete($existingDocument->file_path);
                    $existingDocument->delete();
                }

                // Upload new file
                $filePath = Storage::disk('local')->put('nurse/documents', $data[$field]);

                // Save new document
                $nurseProfile->documents()->create([
                    'document_type' => $documentType,
                    'file_path' => $filePath,
                    'status' => NurseDocument::STATUS_APPROVED,
                ]);
            }

            // Move onboarding forward only
            $nurseProfile->updateOnboardingStep(NurseProfile::STEP_SUBMIT_FOR_REVIEW);
        });
    }

    public function submitForReview(User $user): void
    {
        $nurseProfile = $user->nurseProfile;

        // Validate Required Data

        if (
            !$nurseProfile->license_number ||
            !$nurseProfile->years_of_experience
        ) {
            throw new InvalidOnboardingStepException('Please complete all onboarding details before submission.');
        }

        if (!$nurseProfile->careTypes()->exists()) {
            throw new InvalidOnboardingStepException('Please select at least one care type.');
        }

        if (!$nurseProfile->educations()->exists()) {

            throw new InvalidOnboardingStepException('Please add at least one education record.');
        }

        if (!$nurseProfile->workHistories()->exists()) {
            throw new InvalidOnboardingStepException('Please add at least one work history record.');
        }

        if (!$nurseProfile->documents()->exists()) {
            throw new InvalidOnboardingStepException('Please upload required documents.');
        }

        DB::transaction(function () use ($nurseProfile) {

            $nurseProfile->update([
                'status' => NurseProfile::STATUS_UNDER_REVIEW,
                'onboarding_step' => NurseProfile::STEP_SUBMIT_FOR_REVIEW,
                'is_onboarding_completed' => true,
            ]);

            $user->notify(new \App\Notifications\NurseProfileSubmitted());
        });
    }

    public function getStepData(User $user, int $step): array
    {
        // Prevent future invalid step access
        if (!array_key_exists($step, NurseProfile::getOnboardingStepList())) {
            throw new InvalidOnboardingStepException('Invalid onboarding step.');
        }

        $nurseProfile = $user->nurseProfile;
        if (!$nurseProfile) {
            throw new InvalidOnboardingStepException('Nurse profile not found.');
        }

        // Allow fetching data for completed steps or the current step
        if ($step > $nurseProfile->onboarding_step && $nurseProfile->onboarding_step < NurseProfile::STEP_SUBMIT_FOR_REVIEW) {
            throw new InvalidOnboardingStepException('You have not reached this step yet.');
        }

        return match ($step) {
            NurseProfile::STEP_BASIC_PROFILE => [
                'bio' => $nurseProfile->bio,
                'years_of_experience' => $nurseProfile->years_of_experience,
                'license_number' => $nurseProfile->license_number,
                'license_expiry_date' => $nurseProfile->license_expiry_date,
                'address' => $nurseProfile->address,
                'city' => $nurseProfile->city,
                'state' => $nurseProfile->state,
                'country' => $nurseProfile->country,
                'pincode' => $nurseProfile->pincode,
                'latitude' => $nurseProfile->latitude,
                'longitude' => $nurseProfile->longitude,
            ],

            NurseProfile::STEP_CARE_TYPES => [
                'care_types' =>
                    $nurseProfile
                        ->careTypes()
                        ->select('care_types.id', 'care_types.name')
                        ->get()
                        ->toArray(),
            ],

            NurseProfile::STEP_EDUCATION => [
                'educations' => $nurseProfile
                    ->educations
                    ->map(fn($education) => [
                        'degree_or_course' => $education->degree_or_course,
                        'institute_name' => $education->institute_name,
                        'field_of_study' => $education->field_of_study,
                        'start_year' => $education->start_year,
                        'end_year' => $education->end_year,
                        'is_currently_studying' => $education->is_currently_studying,
                    ])
                    ->values()
                    ->toArray(),
            ],

            NurseProfile::STEP_WORK_HISTORY => [
                'work_histories' =>
                    $nurseProfile
                        ->workHistories
                        ->map(fn($workHistory) => [
                            'role_or_position' => $workHistory->role_or_position,
                            'organization_name' => $workHistory->organization_name,
                            'location' => $workHistory->location,
                            'start_date' => $workHistory->start_date?->format('Y-m-d'),
                            'end_date' => $workHistory->end_date?->format('Y-m-d'),
                            'is_currently_working' => $workHistory->is_currently_working,
                            'description' => $workHistory->description,
                        ])
                        ->values()
                        ->toArray(),
            ],

            NurseProfile::STEP_DOCUMENTS => [
                'documents' =>
                    $nurseProfile
                        ->documents()
                        ->orderBy('id')
                        ->get()
                        ->map(fn($document) => [
                            'id' => $document->id,
                            'document_type' => $document->document_type,
                            'document_type_name' => $document->document_type_text,
                            'status' => $document->status,
                            'status_name' => $document->status_text,
                            'rejection_reason' => $document->rejection_reason,
                            'file_path' => $document->file_path,
                        ])
                        ->values()
                        ->toArray(),
            ],

            default => [],
        };
    }

    public function reapply(User $user): void
    {
        $nurseProfile = $user->nurseProfile;
        if (!$nurseProfile) {
            throw new InvalidOnboardingStepException('Nurse profile not found.');
        }
        if ($nurseProfile->status !== NurseProfile::STATUS_REJECTED) {
            throw new InvalidOnboardingStepException('Only rejected profiles can reapply.');
        }

        DB::transaction(function () use ($nurseProfile) {
            $nurseProfile->update([
                'status' => NurseProfile::STATUS_UNDER_REVIEW,
                'rejection_reason' => null,
            ]);
        });
    }

    public function reviewStep(User $user, int $stepId, int $status, ?string $reason = null): void
    {
        $nurseProfile = $user->nurseProfile;

        if (!$nurseProfile) {
            throw new InvalidOnboardingStepException('Nurse profile not found.');
        }

        NurseProfileVerification::updateOrCreate(
            [
                'nurse_profile_id' => $nurseProfile->id,
                'step_id' => $stepId,
            ],
            [
                'status' => $status,
                'review_message' => $reason,
                'action_by' => auth()->id(),
                'action_at' => now(),
            ]
        );
    }

    public function finalizeReview(User $user, int $status, ?string $reason = null, $canReapply = 1): void
    {
        $nurseProfile = $user->nurseProfile;
        if (!$nurseProfile) {
            throw new InvalidOnboardingStepException('Nurse profile not found.');
        }

        DB::transaction(function () use ($nurseProfile, $status, $reason, $canReapply) {
            $updateData = [
                'status' => $status,
            ];

            if ($status == NurseProfile::STATUS_APPROVED) {
                $updateData['approved_at'] = now();
                $updateData['rejection_reason'] = null;
                $updateData['can_reapply'] = true;

                // Clean up verification steps as they are no longer needed
                $nurseProfile->verifications()->delete();
            } elseif ($status == NurseProfile::STATUS_REJECTED) {
                $updateData['rejection_reason'] = $reason;
                $updateData['can_reapply'] = (bool) $canReapply;
            }

            $nurseProfile->update($updateData);

            $statusName = $status == NurseProfile::STATUS_APPROVED ? 'approved' : 'rejected';
            $user->notify(new \App\Notifications\NurseProfileStatusChanged($statusName, $reason));
        });
    }

}
