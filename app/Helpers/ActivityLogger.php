<?php

namespace App\Helpers;

use App\Models\Activity;
use Illuminate\Support\Facades\Log;

class ActivityLogger
{
    /**
     * Log an activity safely without breaking the application flow.
     *
     * @param int $actionType
     * @param string|null $description
     * @param mixed|null $subject
     * @param array $properties
     * @return void
     */
    public static function log(int $actionType, string $description = null, $subject = null, array $properties = [])
    {
        try {
            Activity::create([
                'action_type' => $actionType,
                'description' => $description,
                'subject_type' => is_object($subject) ? get_class($subject) : null,
                'subject_id' => is_object($subject) ? $subject->id : null,
                'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
                'causer_id' => auth()->id(),
                'properties' => empty($properties) ? null : $properties,
            ]);
        } catch (\Exception $e) {
            Log::error('Activity Log Failed: ' . $e->getMessage());
        }
    }
}
