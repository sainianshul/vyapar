@extends('admin.layouts.app')

@section('title', 'Review Nurse Application')

@section('content')
    @inject('onboardingService', 'App\Services\OnboardingService')

    @php
        $isReadOnly = !in_array($profile->status, [\App\Models\NurseProfile::STATUS_PENDING, \App\Models\NurseProfile::STATUS_UNDER_REVIEW]);
    @endphp

    {{-- Page Header --}}
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'People'],
                    ['label' => 'Nurses', 'url' => route('admin.nurses.index')],
                    ['label' => $user->name],
                    ['label' => 'Application Review'],
                ]" />
                <h2 class="page-title">Application Review</h2>
                <div class="text-muted small mt-1">Verify onboarding sections independently</div>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.nurses.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-chevron-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    @if($profile->status == \App\Models\NurseProfile::STATUS_REJECTED)
        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
            <i class="ti ti-alert-circle fs-2 me-3"></i>
            <div>
                <h4 class="alert-title mb-1">Application Rejected</h4>
                <div class="text-secondary small">Reason: {{ $profile->rejection_reason ?? 'No final reason provided.' }}</div>
            </div>
        </div>
    @endif

    {{-- Top Profile Card --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center g-3">
                <div class="col-auto">
                    @if($user->profile_photo)
                        <span class="avatar avatar-xl rounded-circle" style="background-image: url('{{ Storage::url($user->profile_photo) }}')"></span>
                    @else
                        <span class="avatar avatar-xl rounded-circle bg-primary-lt fw-bold fs-2">
                            {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                        </span>
                    @endif
                </div>
                <div class="col">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h2 class="card-title mb-0 fs-2 fw-bold">{{ $user->name }}</h2>
                        @php
                            $statusColor = $profile->status_color ?? 'primary';
                            if ($statusColor === 'warning') $statusColor = 'yellow';
                            elseif ($statusColor === 'danger') $statusColor = 'red';
                            elseif ($statusColor === 'success') $statusColor = 'green';
                            elseif ($statusColor === 'info') $statusColor = 'azure';
                        @endphp
                        <span class="badge bg-{{ $statusColor }}-lt">
                            {{ $profile->status_name ?? 'Under Review' }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-4 flex-wrap text-muted small mt-2">
                        <span><i class="ti ti-phone me-1"></i>{{ $user->phone ?: '—' }}</span>
                        <span><i class="ti ti-mail me-1"></i>{{ $user->email }}</span>
                        <span><i class="ti ti-calendar me-1"></i>Joined {{ $user->created_at->format('d M Y') }}</span>
                        <span><i class="ti ti-clock me-1"></i>Last Login: {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</span>
                    </div>
                </div>
                <div class="col-auto ms-auto d-flex align-items-center gap-2">
                    <a href="{{ route('admin.nurses.edit', $user->id) }}" class="btn btn-outline-warning btn-sm">
                        <i class="ti ti-edit me-1"></i> Edit
                    </a>

                    @if($isReadOnly)
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-settings me-1"></i> Status
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><h6 class="dropdown-header">Change Status</h6></li>
                                @if($profile->status != \App\Models\NurseProfile::STATUS_APPROVED)
                                <li>
                                    <form action="{{ route('admin.nurses.status.update', $user->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="{{ \App\Models\NurseProfile::STATUS_APPROVED }}">
                                        <button type="submit" class="dropdown-item py-2 text-success" onclick="return confirm('Are you sure you want to approve this nurse?')">
                                            <i class="ti ti-circle-check text-success me-2"></i> Approve Account
                                        </button>
                                    </form>
                                </li>
                                @endif

                                @if($profile->status != \App\Models\NurseProfile::STATUS_SUSPENDED)
                                <li>
                                    <form action="{{ route('admin.nurses.status.update', $user->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="{{ \App\Models\NurseProfile::STATUS_SUSPENDED }}">
                                        <input type="hidden" name="reason" value="Suspended by Admin">
                                        <button type="submit" class="dropdown-item py-2 text-warning" onclick="return confirm('Are you sure you want to suspend this nurse?')">
                                            <i class="ti ti-alert-triangle text-warning me-2"></i> Suspend Account
                                        </button>
                                    </form>
                                </li>
                                @endif

                                @if($profile->status != \App\Models\NurseProfile::STATUS_REJECTED)
                                <li>
                                    <form action="{{ route('admin.nurses.status.update', $user->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="{{ \App\Models\NurseProfile::STATUS_REJECTED }}">
                                        <input type="hidden" name="reason" value="Rejected by Admin">
                                        <button type="submit" class="dropdown-item py-2 text-danger" onclick="return confirm('Are you sure you want to mark this nurse as rejected?')">
                                            <i class="ti ti-circle-x text-danger me-2"></i> Mark Rejected
                                        </button>
                                    </form>
                                </li>
                                @endif
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @php
        $steps = [
            1 => ['name' => 'Personal info', 'desc' => 'Identity & contact details', 'icon' => 'ti ti-user'],
            2 => ['name' => 'Care Types', 'desc' => 'Selected specializations', 'icon' => 'ti ti-heart'],
            3 => ['name' => 'Education', 'desc' => 'Degrees & certifications', 'icon' => 'ti ti-school'],
            4 => ['name' => 'Work History', 'desc' => 'Employment records', 'icon' => 'ti ti-briefcase'],
            5 => ['name' => 'Documents', 'desc' => 'Uploaded legal documents', 'icon' => 'ti ti-files'],
        ];
    @endphp

    <div class="row g-4">
        {{-- Left Sidebar: Verification Steps Navigation --}}
        <div class="col-lg-4 col-xl-3">
            <div class="card">
                <div class="card-header py-3">
                    <h3 class="card-title fs-4">Verification Steps</h3>
                </div>
                <div class="list-group list-group-flush" id="verification-nav">
                    @foreach($steps as $stepId => $stepData)
                        @php
                            $verification = $profile->verifications->where('step_id', $stepId)->first();
                            $status = $verification ? $verification->status : \App\Models\NurseProfileVerification::STATUS_PENDING;

                            $statusBadge = '<span class="badge bg-secondary-lt ms-auto" id="step-badge-' . $stepId . '">Pending</span>';
                            if ($status == \App\Models\NurseProfileVerification::STATUS_APPROVED) {
                                $statusBadge = '<span class="badge bg-green-lt ms-auto" id="step-badge-' . $stepId . '"><i class="ti ti-check me-1"></i>Verified</span>';
                            } elseif ($status == \App\Models\NurseProfileVerification::STATUS_REJECTED) {
                                $statusBadge = '<span class="badge bg-red-lt ms-auto" id="step-badge-' . $stepId . '"><i class="ti ti-x me-1"></i>Rejected</span>';
                            }
                        @endphp

                        <a href="javascript:void(0)"
                            class="list-group-item list-group-item-action d-flex align-items-center step-nav-item py-3 {{ $loop->first ? 'active' : '' }}"
                            data-step="{{ $stepId }}"
                            onclick="showStep({{ $stepId }}, this)">
                            <span class="avatar avatar-xs rounded me-3 bg-light text-secondary">
                                <i class="{{ $stepData['icon'] }}"></i>
                            </span>
                            <div class="text-truncate me-2">
                                <div class="fw-semibold nav-title">{{ $stepData['name'] }}</div>
                                <div class="text-muted small nav-desc">{{ $stepData['desc'] }}</div>
                            </div>
                            {!! $statusBadge !!}
                        </a>
                    @endforeach

                    @if(!$isReadOnly)
                        <div class="card-footer p-2">
                            <a href="javascript:void(0)"
                                class="list-group-item list-group-item-action d-flex align-items-center step-nav-item rounded py-3"
                                data-step="final"
                                onclick="showStep('final', this)">
                                <span class="avatar avatar-xs rounded me-3 bg-dark-lt">
                                    <i class="ti ti-shield-check"></i>
                                </span>
                                <div>
                                    <div class="fw-semibold nav-title">Final Decision</div>
                                    <div class="text-muted small nav-desc">Approve or reject</div>
                                </div>
                                <span class="badge bg-dark-lt ms-auto"><i class="ti ti-arrow-right"></i></span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Side: Dynamic Step Content Container --}}
        <div class="col-lg-8 col-xl-9">
            <div id="step-content-container">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="text-muted mt-3">Loading section details...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <x-comments type="{{ \App\Models\Comment::TYPE_NURSE }}" :model-id="$user->id" />
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Load first step automatically
            const firstStep = document.querySelector('.step-nav-item');
            if (firstStep) {
                firstStep.click();
            }
        });

        function showStep(stepId, element) {
            // Update active state in nav
            document.querySelectorAll('.step-nav-item').forEach(el => {
                el.classList.remove('active');
            });
            element.classList.add('active');

            // Show loading placeholder
            const container = document.getElementById('step-content-container');
            container.innerHTML = `
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="text-muted mt-3">Loading section details...</div>
                    </div>
                </div>
            `;

            // Load via AJAX
            let isReadOnlyParam = '{{ $isReadOnly ? "1" : "0" }}';
            let url = `{{ url('admin/nurses') }}/{{ $user->id }}/review-step-view/${stepId}?readonly=${isReadOnlyParam}`;

            fetch(url)
                .then(response => response.text())
                .then(html => {
                    container.innerHTML = html;
                })
                .catch(error => {
                    container.innerHTML = `
                        <div class="card border-danger">
                            <div class="card-body text-center py-5">
                                <i class="ti ti-alert-triangle fs-1 text-danger mb-3"></i>
                                <div class="text-danger fw-bold">Failed to load section data. Please try again.</div>
                            </div>
                        </div>
                    `;
                });
        }

        function processStepReview(stepId, status, existingReason = '') {
            if (status === {{ \App\Models\NurseProfileVerification::STATUS_REJECTED }}) {
                Swal.fire({
                    html: `
                        <div class="text-start">
                            <div class="d-flex align-items-center mb-3">
                                <span class="avatar avatar-md bg-red-lt rounded me-3">
                                    <i class="ti ti-x fs-2"></i>
                                </span>
                                <div>
                                    <h4 class="mb-0 fw-bold">Reject Section</h4>
                                    <div class="text-secondary small">Please provide a reason to help the nurse fix this.</div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label required fw-bold">Rejection Reason</label>
                                <textarea id="swal-step-reject-reason" class="form-control" rows="3" placeholder="Type your detailed reason here...">${existingReason}</textarea>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Submit Rejection',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'shadow',
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-outline-secondary'
                    },
                    preConfirm: () => {
                        return document.getElementById('swal-step-reject-reason').value.trim();
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitStepReview(stepId, status, result.value);
                    }
                });
            } else {
                submitStepReview(stepId, status, null);
            }
        }

        function submitStepReview(stepId, status, reason) {
            $.ajax({
                url: '{{ route("admin.nurses.review-step", $user->id) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    step_id: stepId,
                    status: status,
                    reason: reason
                },
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });

                        // Update badge in sidebar
                        updateSidebarStatus(stepId, status);

                        // Reload step content to show updated state
                        const activeStepElement = document.querySelector(`.step-nav-item[data-step="${stepId}"]`);
                        if (activeStepElement) {
                            showStep(stepId, activeStepElement);
                        }
                    } else {
                        Swal.fire('Cannot Approve', response.message, 'warning');
                    }
                },
                error: function (xhr) {
                    Swal.fire('Error', 'An error occurred while saving verification.', 'error');
                }
            });
        }

        function processDocumentReview(docId, status) {
            $.ajax({
                url: `{{ url('admin/nurses') }}/{{ $user->id }}/document-review/${docId}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status
                },
                success: function (response) {
                    if (response.success) {
                        const badge = document.getElementById(`doc-badge-${docId}`);
                        if (badge) {
                            if (status == {{ \App\Models\NurseDocument::STATUS_APPROVED }}) {
                                badge.className = 'badge bg-green-lt';
                                badge.innerHTML = '<i class="ti ti-check me-1"></i>Approved';
                            } else if (status == {{ \App\Models\NurseDocument::STATUS_REJECTED }}) {
                                badge.className = 'badge bg-red-lt';
                                badge.innerHTML = '<i class="ti ti-x me-1"></i>Rejected';
                            }
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1200
                        });
                    }
                },
                error: function (xhr) {
                    Swal.fire('Error', 'An error occurred while saving document verification.', 'error');
                }
            });
        }

        function updateSidebarStatus(stepId, status) {
            const badge = document.getElementById(`step-badge-${stepId}`);
            if (!badge) return;

            if (status == {{ \App\Models\NurseProfileVerification::STATUS_APPROVED }}) {
                badge.className = 'badge bg-green-lt ms-auto';
                badge.innerHTML = '<i class="ti ti-check me-1"></i>Verified';
            } else if (status == {{ \App\Models\NurseProfileVerification::STATUS_REJECTED }}) {
                badge.className = 'badge bg-red-lt ms-auto';
                badge.innerHTML = '<i class="ti ti-x me-1"></i>Rejected';
            } else {
                badge.className = 'badge bg-secondary-lt ms-auto';
                badge.innerText = 'Pending';
            }
        }

        function finalizeReview(status) {
            if (status === {{ \App\Models\NurseProfile::STATUS_REJECTED }}) {
                Swal.fire({
                    html: `
                        <div class="text-start">
                            <div class="d-flex align-items-center mb-3">
                                <span class="avatar avatar-md bg-red-lt rounded me-3">
                                    <i class="ti ti-shield-x fs-2"></i>
                                </span>
                                <div>
                                    <h4 class="mb-0 fw-bold">Reject Application</h4>
                                    <div class="text-secondary small">This will mark the nurse's profile as rejected.</div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label required fw-bold">Rejection Reason</label>
                                <textarea id="swal-reject-reason" class="form-control" rows="3" placeholder="Explain why the application was rejected..."></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="swal-can-reapply" checked>
                                    <span class="form-check-label fw-semibold">Allow Reapplication</span>
                                </label>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Confirm Rejection',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'shadow',
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-outline-secondary'
                    },
                    preConfirm: () => {
                        const reason = document.getElementById('swal-reject-reason').value.trim();
                        const canReapply = document.getElementById('swal-can-reapply').checked ? 1 : 0;
                        if (!reason) {
                            Swal.showValidationMessage('Please provide a reason!');
                            return false;
                        }
                        return { reason: reason, canReapply: canReapply };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitFinalReview(status, result.value.reason, result.value.canReapply);
                    }
                });
            } else {
                Swal.fire({
                    title: 'Approve Application',
                    text: 'Are you sure you want to officially approve this nurse?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Approve',
                    confirmButtonColor: '#206bc4',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitFinalReview(status, null, null);
                    }
                });
            }
        }

        function submitFinalReview(status, reason, canReapply) {
            $.ajax({
                url: '{{ route("admin.nurses.finalize-review", $user->id) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status,
                    reason: reason,
                    can_reapply: canReapply
                },
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Completed',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = '{{ route("admin.nurses.index") }}';
                        });
                    }
                },
                error: function (xhr) {
                    Swal.fire('Error', 'An error occurred while finalizing.', 'error');
                }
            });
        }
    </script>
@endpush
