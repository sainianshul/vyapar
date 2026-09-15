<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Education</h3>
            <div class="text-secondary small mt-1">Review degrees and certifications</div>
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

        @forelse($sectionData['educations'] ?? [] as $edu)
            <div class="d-flex align-items-start {{ !$loop->last ? 'mb-3 pb-3 border-bottom' : 'mb-4' }}">
                <span class="avatar avatar-sm bg-primary-lt rounded me-3 mt-1">
                    <i class="ti ti-school"></i>
                </span>
                <div class="flex-fill">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                        <div class="fw-semibold">{{ $edu['degree_or_course'] }}</div>
                        <span class="badge bg-secondary-lt">
                            {{ $edu['start_year'] }} – {{ $edu['is_currently_studying'] ? 'Present' : $edu['end_year'] }}
                        </span>
                    </div>
                    <div class="text-secondary small">
                        <i class="ti ti-building me-1"></i>{{ $edu['institute_name'] }}
                    </div>
                    @if(!empty($edu['field_of_study']))
                        <div class="text-secondary small mt-1">
                            <strong>Field:</strong> {{ $edu['field_of_study'] }}
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-secondary small mb-4">No education records provided.</div>
        @endforelse

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
