<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Documents</h3>
            <div class="text-secondary small mt-1">Review uploaded legal documents</div>
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

        @if(count($sectionData['documents'] ?? []) > 0)
            <div class="table-responsive mb-4">
                <table class="table table-vcenter">
                    <thead>
                        <tr>
                            <th>Document</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sectionData['documents'] as $doc)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-sm bg-primary-lt rounded me-2">
                                            <i class="ti ti-file-certificate"></i>
                                        </span>
                                        <span class="fw-semibold">{{ $doc['document_type_name'] ?? 'Document' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if(isset($doc['status']))
                                        @php
                                            $docBadge = 'badge bg-yellow-lt';
                                            if ($doc['status'] == \App\Models\NurseDocument::STATUS_APPROVED) {
                                                $docBadge = 'badge bg-green-lt';
                                            } elseif ($doc['status'] == \App\Models\NurseDocument::STATUS_REJECTED) {
                                                $docBadge = 'badge bg-red-lt';
                                            }
                                        @endphp
                                        <span id="doc-badge-{{ $doc['id'] }}" class="{{ $docBadge }}">
                                            {{ $doc['status_name'] ?? 'Pending' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        @if(!$isReadOnly)
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="processDocumentReview({{ $doc['id'] }}, {{ \App\Models\NurseDocument::STATUS_REJECTED }})" title="Reject">
                                                <i class="ti ti-x"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="processDocumentReview({{ $doc['id'] }}, {{ \App\Models\NurseDocument::STATUS_APPROVED }})" title="Approve">
                                                <i class="ti ti-check"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('admin.nurses.document', $doc['id']) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="ti ti-external-link"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-secondary small mb-4">No documents uploaded.</div>
        @endif

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
