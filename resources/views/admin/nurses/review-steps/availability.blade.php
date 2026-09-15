<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Availability</h3>
            <div class="text-secondary small mt-1">Review shift preferences and schedules</div>
        </div>
        <div class="card-actions">
            @php
                $badgeClass = 'badge bg-yellow-lt';
                $badgeText = 'In review';
                if ($status == \App\Models\NurseProfileVerification::STATUS_APPROVED) {
                    $badgeClass = 'badge bg-green-lt';
                    $badgeText = 'Verified';
                } elseif ($status == \App\Models\NurseProfileVerification::STATUS_REJECTED) {
                    $badgeClass = 'badge bg-red-lt';
                    $badgeText = 'Rejected';
                }
            @endphp
            <span class="badge {{ $badgeClass }} px-3 py-2">{{ $badgeText }}</span>
        </div>
    </div>

    <div class="card-body">
        @if($status == \App\Models\NurseProfileVerification::STATUS_REJECTED && !empty($verification->review_message))
            <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                <i class="ti ti-alert-circle fs-2 me-3"></i>
                <div>
                    <h4 class="alert-title mb-1">Rejection Reason</h4>
                    <div class="text-secondary small">{{ $verification->review_message }}</div>
                </div>
            </div>
        @endif

        <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
            <i class="ti ti-circle-check fs-2 me-3"></i>
            <div>
                <h4 class="alert-title mb-1">Currently Taking Shifts</h4>
                <div class="text-secondary small">This nurse is marked as actively available for shift assignments.</div>
            </div>
        </div>

        <div class="datagrid mb-4">
            <div class="datagrid-item">
                <div class="datagrid-title">Available From</div>
                <div class="datagrid-content fw-bold">
                    {{ $sectionData['available_from'] ? \Carbon\Carbon::parse($sectionData['available_from'])->format('h:i A') : 'N/A' }}
                </div>
            </div>
            <div class="datagrid-item">
                <div class="datagrid-title">Available To</div>
                <div class="datagrid-content fw-bold">
                    {{ $sectionData['available_to'] ? \Carbon\Carbon::parse($sectionData['available_to'])->format('h:i A') : 'N/A' }}
                </div>
            </div>
        </div>

        <h4 class="mb-3">Preferred Working Days</h4>
        @php
            $allDaysMap = \App\Models\NurseProfile::getDaysList();
            $chosenDays = [];
            if (!empty($sectionData['available_days'])) {
                if (is_string($sectionData['available_days'])) {
                    $decoded = json_decode($sectionData['available_days'], true);
                    $chosenDays = is_array($decoded) ? $decoded : explode(',', $sectionData['available_days']);
                } elseif (is_array($sectionData['available_days'])) {
                    $chosenDays = $sectionData['available_days'];
                }
            }
            $chosenDays = array_map('intval', $chosenDays);
        @endphp

        <div class="d-flex flex-wrap gap-2 mb-4">
            @if(count($chosenDays) > 0)
                @foreach($allDaysMap as $dayValue => $dayName)
                    @if(in_array($dayValue, $chosenDays, true))
                        <span class="badge bg-primary px-3 py-2">{{ $dayName }}</span>
                    @else
                        <span class="badge bg-secondary-lt text-muted px-3 py-2">{{ $dayName }}</span>
                    @endif
                @endforeach
            @else
                <div class="text-secondary small">No specific days selected.</div>
            @endif
        </div>

        @if(!$isReadOnly)
            <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                <button type="button" class="btn btn-outline-danger" onclick="processStepReview({{ $stepId }}, {{ \App\Models\NurseProfileVerification::STATUS_REJECTED }})">
                    <i class="ti ti-x me-1"></i> Reject Section
                </button>
                <button type="button" class="btn btn-success" onclick="processStepReview({{ $stepId }}, {{ \App\Models\NurseProfileVerification::STATUS_APPROVED }})">
                    <i class="ti ti-check me-1"></i> Approve Section
                </button>
            </div>
        @endif
    </div>
</div>
