<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\NurseProfile;
use App\Models\NurseDocument;
use App\Models\NurseEducation;
use App\Models\NurseWorkHistory;

class NurseService
{
    /**
     * Update Nurse profile details from Admin panel
     *
     * @param User $user The Nurse to update
     * @param array $data Validated request data
     * @return User
     * @throws \Exception
     */
    public function updateNurse(User $user, array $data)
    {
        try {
            DB::beginTransaction();

            // 1. Update User Record
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            if (array_key_exists('phone', $data)) {
                $userData['phone'] = $data['phone'];
            }

            // Handle Profile Photo Upload
            if (isset($data['profile_photo']) && $data['profile_photo'] instanceof \Illuminate\Http\UploadedFile) {
                $disk = 'public';
                // Delete old photo if exists
                if ($user->profile_photo && Storage::disk($disk)->exists($user->profile_photo)) {
                    Storage::disk($disk)->delete($user->profile_photo);
                }

                $path = $data['profile_photo']->store('users/profile-photos', $disk);
                $userData['profile_photo'] = $path;
            }

            $user->update($userData);

            // 2. Update NurseProfile Record
            if ($user->nurseProfile) {
                $profileData = [
                    'is_available' => $data['is_available'],
                    'bio' => $data['bio'] ?? null,
                    'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                    'address' => $data['address'],
                    'city' => $data['city'],
                    'state' => $data['state'],
                    'country' => $data['country'],
                    'pincode' => $data['pincode'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'available_from' => $data['available_from'] ?? null,
                    'available_to' => $data['available_to'] ?? null,
                    'available_days' => $data['available_days'] ?? null,
                ];

                $user->nurseProfile->update($profileData);

                // 3. Sync Care Types
                if (isset($data['care_types']) && is_array($data['care_types'])) {
                    $careTypesWithPivot = [];
                    foreach ($data['care_types'] as $careTypeId) {
                        $careTypesWithPivot[$careTypeId] = ['status' => 1];
                    }
                    $user->nurseProfile->careTypes()->sync($careTypesWithPivot);
                } else {
                    $user->nurseProfile->careTypes()->detach();
                }

                // 4. Sync Educations
                $user->nurseProfile->educations()->delete();
                if (isset($data['educations']) && is_array($data['educations'])) {
                    foreach ($data['educations'] as $edu) {
                        if (!empty($edu['degree_name']) && !empty($edu['institution_name']) && !empty($edu['start_date'])) {
                            NurseEducation::create([
                                'nurse_id' => $user->nurseProfile->id,
                                'degree_or_course' => $edu['degree_name'],
                                'institute_name' => $edu['institution_name'],
                                'start_year' => date('Y', strtotime($edu['start_date'])),
                                'end_year' => !empty($edu['end_date']) ? date('Y', strtotime($edu['end_date'])) : null,
                                'is_currently_studying' => empty($edu['end_date']),
                                'status' => 1,
                            ]);
                        }
                    }
                }

                // 5. Sync Experiences
                $user->nurseProfile->workHistories()->delete();
                if (isset($data['experiences']) && is_array($data['experiences'])) {
                    foreach ($data['experiences'] as $exp) {
                        if (!empty($exp['designation']) && !empty($exp['hospital_name']) && !empty($exp['start_date'])) {
                            NurseWorkHistory::create([
                                'nurse_id' => $user->nurseProfile->id,
                                'role_or_position' => $exp['designation'],
                                'organization_name' => $exp['hospital_name'],
                                'start_date' => $exp['start_date'],
                                'end_date' => $exp['end_date'] ?? null,
                                'is_currently_working' => isset($exp['is_currently_working']) ? (bool) $exp['is_currently_working'] : false,
                                'status' => 1,
                            ]);
                        }
                    }
                }

                // 6. Sync Documents
                $existingDocIds = $data['existing_documents'] ?? [];
                
                // Get docs to delete (ones not in the submitted array)
                $docsToDelete = $user->nurseProfile->documents()->whereNotIn('id', $existingDocIds)->get();
                foreach ($docsToDelete as $doc) {
                    if (Storage::disk('public')->exists($doc->file_path)) {
                        Storage::disk('public')->delete($doc->file_path);
                    }
                    $doc->delete();
                }

                // Upload new documents
                if (isset($data['documents']) && is_array($data['documents'])) {
                    foreach ($data['documents'] as $type => $file) {
                        if ($file instanceof \Illuminate\Http\UploadedFile) {
                            $path = $file->store('nurse-documents/' . $user->nurseProfile->id, 'public');
                            NurseDocument::create([
                                'nurse_id' => $user->nurseProfile->id,
                                'document_type' => $type, // Use the specific document type ID
                                'file_path' => $path,
                                'status' => NurseDocument::STATUS_PENDING,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return $user->fresh(['nurseProfile', 'nurseProfile.careTypes']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin Nurse Update Failed: ' . $e->getMessage(), ['user_id' => $user->id]);
            throw $e;
        }
    }

    /**
     * Create a new Nurse from Admin panel
     *
     * @param array $data Validated request data
     * @return User
     * @throws \Exception
     */
    public function createNurse(array $data)
    {
        try {
            DB::beginTransaction();

            // 1. Create User Record
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make(Str::random(16)), // Nurses login via OTP
                'role' => User::ROLE_NURSE,
                'created_by_admin' => true,
            ];

            if (array_key_exists('phone', $data)) {
                $userData['phone'] = $data['phone'];
            }

            // Handle Profile Photo Upload
            if (isset($data['profile_photo']) && $data['profile_photo'] instanceof \Illuminate\Http\UploadedFile) {
                $disk = 'public';
                $path = $data['profile_photo']->store('users/profile-photos', $disk);
                $userData['profile_photo'] = $path;
            }

            $user = User::create($userData);

            // 2. Create NurseProfile Record
            $profileData = [
                'user_id' => $user->id,
                'is_available' => $data['is_available'],
                'bio' => $data['bio'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'address' => $data['address'],
                'city' => $data['city'],
                'state' => $data['state'],
                'country' => $data['country'],
                'pincode' => $data['pincode'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'available_from' => $data['available_from'] ?? null,
                'available_to' => $data['available_to'] ?? null,
                'available_days' => $data['available_days'] ?? null,
                'status' => NurseProfile::STATUS_PENDING,
            ];

            if (!empty($data['auto_approve'])) {
                $profileData['status'] = NurseProfile::STATUS_APPROVED;
                $profileData['is_onboarding_completed'] = true;
                $profileData['onboarding_step'] = NurseProfile::STEP_SUBMIT_FOR_REVIEW;
                $profileData['approved_by'] = auth()->id() ?? 1;
                $profileData['approved_at'] = now();
            }

            $nurseProfile = NurseProfile::create($profileData);

            // 3. Sync Care Types
            if (isset($data['care_types']) && is_array($data['care_types'])) {
                $careTypesWithPivot = [];
                foreach ($data['care_types'] as $careTypeId) {
                    $careTypesWithPivot[$careTypeId] = ['status' => 1];
                }
                $nurseProfile->careTypes()->sync($careTypesWithPivot);
            }

            // 4. Save Educations
            if (isset($data['educations']) && is_array($data['educations'])) {
                foreach ($data['educations'] as $edu) {
                    if (!empty($edu['degree_name']) && !empty($edu['institution_name']) && !empty($edu['start_date'])) {
                        NurseEducation::create([
                            'nurse_id' => $nurseProfile->id,
                            'degree_or_course' => $edu['degree_name'],
                            'institute_name' => $edu['institution_name'],
                            'start_year' => date('Y', strtotime($edu['start_date'])),
                            'end_year' => !empty($edu['end_date']) ? date('Y', strtotime($edu['end_date'])) : null,
                            'is_currently_studying' => empty($edu['end_date']),
                            'status' => 1,
                        ]);
                    }
                }
            }

            // 5. Save Experiences
            if (isset($data['experiences']) && is_array($data['experiences'])) {
                foreach ($data['experiences'] as $exp) {
                    if (!empty($exp['designation']) && !empty($exp['hospital_name']) && !empty($exp['start_date'])) {
                        NurseWorkHistory::create([
                            'nurse_id' => $nurseProfile->id,
                            'role_or_position' => $exp['designation'],
                            'organization_name' => $exp['hospital_name'],
                            'start_date' => $exp['start_date'],
                            'end_date' => $exp['end_date'] ?? null,
                            'is_currently_working' => isset($exp['is_currently_working']) ? (bool) $exp['is_currently_working'] : false,
                            'status' => 1,
                        ]);
                    }
                }
            }

            // 6. Save Documents
            if (isset($data['documents']) && is_array($data['documents'])) {
                foreach ($data['documents'] as $type => $file) {
                    if ($file instanceof \Illuminate\Http\UploadedFile) {
                        $path = $file->store('nurse-documents/' . $nurseProfile->id, 'public');
                        NurseDocument::create([
                            'nurse_id' => $nurseProfile->id,
                            'document_type' => $type, // Use the specific document type ID
                            'file_path' => $path,
                            'status' => !empty($data['auto_approve']) ? NurseDocument::STATUS_APPROVED : NurseDocument::STATUS_PENDING,
                        ]);
                    }
                }
            }

            DB::commit();

            return $user->fresh(['nurseProfile', 'nurseProfile.careTypes']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin Nurse Creation Failed: ' . $e->getMessage(), ['data' => $data]);
            throw $e;
        }
    }
}
