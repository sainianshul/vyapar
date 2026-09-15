<?php

namespace App\Jobs;

use App\Models\CareRequest;
use App\Models\NurseProfile;
use App\Models\NurseRequestCache;
use Dom\Implementation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MatchNursesForCareRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $careRequest;

    /**
     * Create a new job instance.
     */
    public function __construct(CareRequest $careRequest)
    {
        $this->careRequest = $careRequest;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $careRequest = $this->careRequest;
        
        // 1. Use the smart ProviderMatchingService to find nurses progressively (5km -> 5km+flex -> 7km -> ...)
        $matchingService = app(\App\Services\ProviderMatchingService::class);
        $result = $matchingService->runIterativeMatching($careRequest, 3); // Minimum 3 nurses required to stop
        $nurses = $result['nurses'];

        foreach ($nurses as $nurse) {
            // Check if cache entry already exists to prevent duplicates
            $exists = NurseRequestCache::where('nurse_id', $nurse->id)
                ->where('care_request_id', $careRequest->id)
                ->exists();

            if (!$exists) {
                $snapshot = $careRequest->toUserApiArray();
                
                NurseRequestCache::create([
                    'nurse_id' => $nurse->id,
                    'care_request_id' => $careRequest->id,
                    'request_snapshot' => $snapshot,
                    'status' => NurseRequestCache::STATUS_NOTIFIED,
                    'expires_at' => $careRequest->bidding_ends_at ?? now()->addHours(24),
                ]);

                // Notify Nurse
                if ($nurse->user) {
                    $nurse->user->notify(new \App\Notifications\NewCareRequestNotification($careRequest));
                }
            }
        }
        
        // If no nurses found at all, mark the request as failed and notify user
        if ($nurses->isEmpty()) {
            $careRequest->update(['status' => CareRequest::STATUS_FAILED_NO_NURSES]);
            
            if ($careRequest->user) {
                $careRequest->user->notify(new \App\Notifications\NoNurseFoundNotification($careRequest));
            }
        }
    }
}
