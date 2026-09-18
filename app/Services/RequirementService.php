<?php

namespace App\Services;

use App\Exceptions\RequirementServiceException;
use App\Models\Requirement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RequirementService
{
    public function createRequirement(array $data, User $buyer, $images = null): Requirement
    {
        try {
            DB::beginTransaction();

            $data['user_id'] = $buyer->id;
            $data['status'] = $data['status'] ?? Requirement::STATUS_OPEN;
            $data['expires_at'] = $data['expires_at'] ?? now()->addDays(30);
            
            $data['delivery_location'] = $data['delivery_location'] ?? $buyer->address;
            $data['city'] = $data['city'] ?? $buyer->city;
            $data['delivery_pincode'] = $data['delivery_pincode'] ?? $buyer->pincode;
            $data['latitude'] = $data['latitude'] ?? $buyer->latitude;
            $data['longitude'] = $data['longitude'] ?? $buyer->longitude;

            if (empty($data['city']) || empty($data['latitude']) || empty($data['longitude'])) {
                throw new \Exception('Location details are missing. Please provide address/location details for the requirement or update them in your profile.');
            }

            $requirement = Requirement::create($data);

            if ($images && is_array($images)) {
                foreach ($images as $index => $image) {
                    $path = $image->store('requirements/images', 'public');
                    $requirement->images()->create([
                        'image_path' => $path,
                        'sort_order' => $index,
                    ]);
                }
            }

            DB::commit();

            return $requirement->load(['user', 'category', 'images']);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Requirement creation failed: ' . $e->getMessage());
            throw new RequirementServiceException('Failed to create requirement. ' . $e->getMessage());
        }
    }

    public function updateRequirement(Requirement $requirement, array $data, $newImages = null, $deletedImages = null): Requirement
    {
        try {
            DB::beginTransaction();

            $requirement->update($data);

            if ($deletedImages && is_array($deletedImages)) {
                $imagesToDelete = $requirement->images()->whereIn('id', $deletedImages)->get();
                foreach ($imagesToDelete as $img) {
                    Storage::disk('public')->delete($img->image_path);
                    $img->delete();
                }
            }

            if ($newImages && is_array($newImages)) {
                $maxSortOrder = $requirement->images()->max('sort_order') ?? -1;
                foreach ($newImages as $index => $image) {
                    $path = $image->store('requirements/images', 'public');
                    $requirement->images()->create([
                        'image_path' => $path,
                        'sort_order' => $maxSortOrder + $index + 1,
                    ]);
                }
            }

            DB::commit();

            return $requirement->load(['user', 'category', 'images' => fn($q) => $q->orderBy('sort_order')]);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Requirement update failed: ' . $e->getMessage());
            throw new RequirementServiceException('Failed to update requirement. ' . $e->getMessage());
        }
    }
}
