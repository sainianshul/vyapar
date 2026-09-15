<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Personal Info</h3>
            <div class="text-secondary small mt-1">Review identity and contact details</div>
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

        @if(!empty($sectionData['bio']))
            <div class="mb-4">
                <h4 class="mb-2">About / Bio</h4>
                <div class="text-secondary lh-lg">{{ $sectionData['bio'] }}</div>
            </div>
            <hr class="my-4">
        @endif

        <div class="row g-4">
            <div class="col-lg-6">
                <h4 class="mb-3">Professional Details</h4>
                <div class="datagrid">
                    <div class="datagrid-item">
                        <div class="datagrid-title">License Number</div>
                        <div class="datagrid-content">
                            @if($sectionData['license_number'] ?? null)
                                <span class="badge bg-blue-lt">{{ $sectionData['license_number'] }}</span>
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">License Expiry</div>
                        <div class="datagrid-content">
                            {{ ($sectionData['license_expiry_date'] ?? null) ? \Carbon\Carbon::parse($sectionData['license_expiry_date'])->format('d M Y') : 'N/A' }}
                        </div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Experience</div>
                        <div class="datagrid-content">{{ $sectionData['years_of_experience'] ?? '0' }} Years</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Gender</div>
                        <div class="datagrid-content">{{ $sectionData['gender_name'] ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <h4 class="mb-3">Address & Location</h4>
                <div class="datagrid">
                    <div class="datagrid-item">
                        <div class="datagrid-title">Full Address</div>
                        <div class="datagrid-content">{{ $sectionData['address'] ?? 'N/A' }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">City</div>
                        <div class="datagrid-content">{{ $sectionData['city'] ?? 'N/A' }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">State</div>
                        <div class="datagrid-content">{{ $sectionData['state'] ?? 'N/A' }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Country</div>
                        <div class="datagrid-content">{{ $sectionData['country'] ?? 'N/A' }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Pincode</div>
                        <div class="datagrid-content">{{ $sectionData['pincode'] ?? 'N/A' }}</div>
                    </div>
                    @if(isset($sectionData['latitude']) && isset($sectionData['longitude']))
                    <div class="datagrid-item">
                        <div class="datagrid-title">Coordinates</div>
                        <div class="datagrid-content text-secondary small">{{ $sectionData['latitude'] }}, {{ $sectionData['longitude'] }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if(!$isReadOnly)
            <div class="d-flex align-items-center justify-content-end gap-2 pt-4 mt-4 border-top">
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
