@extends('admin.layouts.app')
@section('title', 'Add New Nurse')

@push('styles')
<style>
    /* Wizard Container & Card */
    .wizard-card {
        border: none;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        border-radius: 8px;
    }
    .wizard-card .card-header {
        background: transparent;
        padding: 1rem 1.25rem;
    }
    
    .wizard-nav {
        display: flex;
        gap: 0.25rem;
        margin-bottom: 0;
        padding: 0.75rem 1.25rem 0 1.25rem;
        background: var(--tblr-bg-surface, #fff);
        overflow-x: auto;
        border-bottom: 1px solid var(--tblr-border-color, #e6e7e9);
    }
    .wizard-step-btn {
        flex: 1;
        min-width: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.5rem 0.75rem;
        background: transparent;
        border: none;
        border-radius: 6px;
        color: var(--tblr-secondary, #6c757d);
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .wizard-step-btn:hover {
        background: rgba(var(--tblr-body-color-rgb), 0.05);
    }
    .wizard-step-btn.active {
        background: rgba(var(--tblr-primary-rgb), 0.1);
        color: var(--tblr-primary, #206bc4);
        font-weight: 600;
    }
    .wizard-step-btn.completed {
        color: var(--tblr-success, #2fb344);
    }
    .wizard-step-btn.has-error {
        color: var(--tblr-danger, #d63939);
        background: rgba(var(--tblr-danger-rgb), 0.05);
    }
    .step-num {
        font-size: 0.8rem;
        font-weight: 600;
        opacity: 0.7;
    }
    .wizard-step-title {
        white-space: nowrap;
    }
    /* Form pane animations */
    .step-pane {
        display: none;
        opacity: 0;
        transform: translateY(10px);
    }
    .step-pane.active {
        display: block;
        animation: slideFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes slideFadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    /* Custom inputs focus */
    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 0.25rem rgba(var(--tblr-primary-rgb), 0.15);
        border-color: var(--tblr-primary);
    }
</style>
@endpush

@section('content')

    {{-- Page Header --}}
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'People'],
                    ['label' => 'Nurses', 'url' => route('admin.nurses.index')],
                    ['label' => 'Add Nurse'],
                ]" />
                <h2 class="page-title">Add New Nurse</h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="{{ route('admin.nurses.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
                <button type="submit" form="nurse-create-form" class="btn btn-primary" id="btn-header-submit">
                    <i class="ti ti-check me-1"></i>Create Nurse
                </button>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible mb-4" role="alert">
            <div class="d-flex">
                <div><i class="ti ti-alert-circle fs-2 me-2"></i></div>
                <div>{{ session('error') }}</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    @endif

    <x-alert-success />
    <x-form-errors />

    {{-- Main Wizard Form --}}
    <form id="nurse-create-form" action="{{ route('admin.nurses.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card wizard-card" id="wizard-card">
            {{-- Step Navigation Bar --}}
            <div class="wizard-nav" id="wizard-nav">
                @php
                    $steps = [
                        ['title' => 'Personal', 'desc' => 'Basic Profile', 'icon' => 'ti-user'],
                        ['title' => 'Location', 'desc' => 'Address & GPS', 'icon' => 'ti-map-pin'],
                        ['title' => 'Education', 'desc' => 'Degrees & Study', 'icon' => 'ti-school'],
                        ['title' => 'Experience', 'desc' => 'Work History', 'icon' => 'ti-briefcase'],
                        ['title' => 'Documents', 'desc' => 'Certificates & IDs', 'icon' => 'ti-file-text'],
                        ['title' => 'Schedule', 'desc' => 'Availability & Care', 'icon' => 'ti-calendar-time'],
                    ];
                @endphp
                @foreach($steps as $i => $step)
                    <button type="button" class="wizard-step-btn {{ $i === 0 ? 'active' : '' }}" data-step="{{ $i }}">
                        <span class="step-num">{{ $i + 1 }}.</span>
                        <i class="ti ti-check step-check d-none"></i>
                        <span class="wizard-step-title">{{ $step['title'] }}</span>
                    </button>
                @endforeach
            </div>
            {{-- Dynamic Card Header --}}
            <div class="card-header d-flex align-items-center justify-content-between border-0 pt-4 pb-0">
                <div>
                    <h3 class="card-title mb-0" id="card-step-title">
                        <span id="card-step-name">Personal Details</span>
                    </h3>
                </div>
                <div class="card-actions" id="step-0-actions">
                    <label class="form-check form-switch m-0" title="Automatically approve this nurse upon creation">
                        <input class="form-check-input" type="checkbox" name="auto_approve" value="1" id="auto_approve_check" {{ old('auto_approve', '1') == '1' ? 'checked' : '' }} />
                        <span class="form-check-label fw-semibold">Auto-Approve</span>
                    </label>
                </div>
            </div>

            <div class="card-body py-4">

                {{-- STEP 0: Personal Details --}}
                <div class="step-pane active" id="pane-0" data-title="Personal Details" data-desc="Enter nurse's identity, contact information, and profile photo.">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label required">Full Name</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-user"></i></span>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="Enter full name" required />
                            </div>
                            @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Email Address</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-mail"></i></span>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" placeholder="Enter email address" required />
                            </div>
                            @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Phone Number</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-phone"></i></span>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                    value="{{ old('phone') }}" placeholder="e.g. 9876543210" required
                                    pattern="[0-9+]*" minlength="10" maxlength="15"
                                    oninput="this.value = this.value.replace(/[^0-9+]/g, '')" />
                            </div>
                            @error('phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Emergency Contact</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-phone-call"></i></span>
                                <input type="text" name="emergency_contact_phone" pattern="[0-9+]*" minlength="10" maxlength="15"
                                    oninput="this.value = this.value.replace(/[^0-9+]/g, '')"
                                    class="form-control @error('emergency_contact_phone') is-invalid @enderror"
                                    value="{{ old('emergency_contact_phone') }}" placeholder="Emergency phone" />
                            </div>
                            @error('emergency_contact_phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        {{-- Profile Photo with immediate preview --}}
                        <div class="col-md-8">
                            <label class="form-label">Profile Photo</label>
                            <div class="d-flex align-items-center gap-3">
                                <img id="avatar-preview" src="" class="rounded-circle border d-none" style="width: 44px; height: 44px; object-fit: cover;" alt="Preview" />
                                <div class="flex-grow-1">
                                    <input type="file" name="profile_photo" id="avatar-upload" accept=".png,.jpg,.jpeg"
                                        class="form-control @error('profile_photo') is-invalid @enderror" />
                                </div>
                            </div>
                            @error('profile_photo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Bio / Profile Summary</label>
                            <textarea name="bio" class="form-control @error('bio') is-invalid @enderror" rows="3"
                                placeholder="Brief background, specialization overview, or experience summary...">{{ old('bio') }}</textarea>
                            @error('bio') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- STEP 1: Location Details --}}
                <div class="step-pane" id="pane-1" data-title="Location Details" data-desc="Specify residential address and coordinates for geographical dispatching.">
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label required">Full Street Address</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-map"></i></span>
                                <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                                    value="{{ old('address') }}" placeholder="Street address, flat/building, landmark" required />
                            </div>
                            @error('address') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label required">City</label>
                            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                                value="{{ old('city') }}" placeholder="City" required />
                            @error('city') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">State</label>
                            <input type="text" name="state" class="form-control @error('state') is-invalid @enderror"
                                value="{{ old('state') }}" placeholder="State" required />
                            @error('state') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">Country</label>
                            <input type="text" name="country" class="form-control @error('country') is-invalid @enderror"
                                value="{{ old('country', 'India') }}" required />
                            @error('country') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">Pincode</label>
                            <input type="text" name="pincode" class="form-control @error('pincode') is-invalid @enderror"
                                value="{{ old('pincode') }}" placeholder="6-digit pincode" required pattern="[0-9]*"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                            @error('pincode') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Latitude</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-compass"></i></span>
                                <input type="text" name="latitude" class="form-control @error('latitude') is-invalid @enderror"
                                    value="{{ old('latitude', '0') }}" required />
                            </div>
                            @error('latitude') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Longitude</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-compass"></i></span>
                                <input type="text" name="longitude" class="form-control @error('longitude') is-invalid @enderror"
                                    value="{{ old('longitude', '0') }}" required />
                            </div>
                            @error('longitude') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- STEP 2: Education History --}}
                <div class="step-pane" id="pane-2" data-title="Education History" data-desc="Add academic qualifications, nursing degrees, and educational institutions.">
                    <div class="d-flex align-items-center justify-content-end mb-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-education">
                            <i class="ti ti-plus me-1"></i>Add Degree
                        </button>
                    </div>

                    <div id="educations-container">
                        @php $oldEducations = old('educations', [[]]); @endphp
                        @foreach($oldEducations as $index => $edu)
                            <div class="card card-body bg-light border-0 mb-3 education-row p-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label required">Degree / Course</label>
                                        <input type="text" name="educations[{{ $index }}][degree_name]"
                                            class="form-control @error("educations.$index.degree_name") is-invalid @enderror"
                                            placeholder="e.g. B.Sc Nursing, GNM" value="{{ $edu['degree_name'] ?? '' }}">
                                        @error("educations.$index.degree_name") <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required">Institution / College</label>
                                        <input type="text" name="educations[{{ $index }}][institution_name]"
                                            class="form-control @error("educations.$index.institution_name") is-invalid @enderror"
                                            placeholder="e.g. AIIMS Nursing College" value="{{ $edu['institution_name'] ?? '' }}">
                                        @error("educations.$index.institution_name") <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" name="educations[{{ $index }}][start_date]"
                                            class="form-control @error("educations.$index.start_date") is-invalid @enderror"
                                            value="{{ $edu['start_date'] ?? '' }}">
                                        @error("educations.$index.start_date") <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">End Date</label>
                                        <input type="date" name="educations[{{ $index }}][end_date]"
                                            class="form-control @error("educations.$index.end_date") is-invalid @enderror"
                                            value="{{ $edu['end_date'] ?? '' }}">
                                        @error("educations.$index.end_date") <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        @if($loop->index > 0)
                                            <button type="button" class="btn btn-outline-danger remove-edu w-100" title="Remove"><i class="ti ti-trash me-1"></i>Remove</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- STEP 3: Work Experience --}}
                <div class="step-pane" id="pane-3" data-title="Work Experience" data-desc="List past clinical experiences, hospital affiliations, and professional roles.">
                    <div class="d-flex align-items-center justify-content-end mb-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-experience">
                            <i class="ti ti-plus me-1"></i>Add Experience
                        </button>
                    </div>

                    <div id="experiences-container">
                        @php $oldExperiences = old('experiences', [[]]); @endphp
                        @foreach($oldExperiences as $index => $exp)
                            <div class="card card-body bg-light border-0 mb-3 experience-row p-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label required">Designation / Role</label>
                                        <input type="text" name="experiences[{{ $index }}][designation]"
                                            class="form-control @error("experiences.$index.designation") is-invalid @enderror"
                                            placeholder="e.g. ICU Staff Nurse" value="{{ $exp['designation'] ?? '' }}">
                                        @error("experiences.$index.designation") <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required">Hospital / Clinic</label>
                                        <input type="text" name="experiences[{{ $index }}][hospital_name]"
                                            class="form-control @error("experiences.$index.hospital_name") is-invalid @enderror"
                                            placeholder="e.g. Max Healthcare" value="{{ $exp['hospital_name'] ?? '' }}">
                                        @error("experiences.$index.hospital_name") <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" name="experiences[{{ $index }}][start_date]"
                                            class="form-control @error("experiences.$index.start_date") is-invalid @enderror"
                                            value="{{ $exp['start_date'] ?? '' }}">
                                        @error("experiences.$index.start_date") <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">End Date</label>
                                        <input type="date" name="experiences[{{ $index }}][end_date]"
                                            class="form-control @error("experiences.$index.end_date") is-invalid @enderror"
                                            value="{{ $exp['end_date'] ?? '' }}">
                                        @error("experiences.$index.end_date") <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 d-flex flex-column justify-content-end">
                                        <div class="d-flex align-items-center gap-3">
                                            <label class="form-check form-check-inline m-0">
                                                <input class="form-check-input" type="checkbox" name="experiences[{{ $index }}][is_currently_working]" value="1" {{ !empty($exp['is_currently_working']) ? 'checked' : '' }}>
                                                <span class="form-check-label fw-medium">Present</span>
                                            </label>
                                            @if($loop->index > 0)
                                                <button type="button" class="btn btn-outline-danger remove-exp flex-grow-1" title="Remove"><i class="ti ti-trash me-1"></i>Remove</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- STEP 4: Documents --}}
                <div class="step-pane" id="pane-4" data-title="Verification Documents" data-desc="Upload government IDs, nursing license, and certificates for onboarding.">
                    <div class="row g-3">
                        @php $documentTypes = \App\Models\NurseDocument::getDocumentTypeList(); @endphp
                        @foreach($documentTypes as $id => $label)
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label class="form-label fw-medium mb-1">{{ $label }}</label>
                                    <input type="file" name="documents[{{ $id }}]" class="form-control @error('documents.'.$id) is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                                    @error('documents.'.$id) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- STEP 5: Schedule & Specializations --}}
                <div class="step-pane" id="pane-5" data-title="Schedule & Specializations" data-desc="Configure booking availability, working days/hours, and medical specialties.">
                    <div class="row g-4">
                        {{-- Left Column: Availability & Hours --}}
                        <div class="col-lg-6">
                            <label class="form-label fw-bold mb-3">Availability & Working Hours</label>

                            <div class="mb-3 p-3 bg-light rounded">
                                <label class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="is_available" value="1" id="is_available_check" checked />
                                    <span class="form-check-label fw-semibold">Currently Accepting Bookings</span>
                                </label>
                                <input type="hidden" name="is_available" value="0" id="is_available_hidden" disabled>
                                @error('is_available') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Available Working Days</label>
                                @php
                                    $days = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
                                    $oldDays = old('available_days', [0, 1, 2, 3, 4, 5, 6]);
                                @endphp
                                <div class="form-selectgroup form-selectgroup-pills">
                                    @foreach($days as $val => $label)
                                        <label class="form-selectgroup-item">
                                            <input type="checkbox" name="available_days[]" value="{{ $val }}"
                                                class="form-selectgroup-input" {{ in_array($val, $oldDays) ? 'checked' : '' }} />
                                            <span class="form-selectgroup-label px-3">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('available_days') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label">Available From (Time)</label>
                                    <div class="input-icon">
                                        <span class="input-icon-addon"><i class="ti ti-clock-play"></i></span>
                                        <input type="time" name="available_from" class="form-control @error('available_from') is-invalid @enderror"
                                            value="{{ old('available_from', '09:00') }}" />
                                    </div>
                                    @error('available_from') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Available To (Time)</label>
                                    <div class="input-icon">
                                        <span class="input-icon-addon"><i class="ti ti-clock-stop"></i></span>
                                        <input type="time" name="available_to" class="form-control @error('available_to') is-invalid @enderror"
                                            value="{{ old('available_to', '18:00') }}" />
                                    </div>
                                    @error('available_to') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Care Specializations --}}
                        <div class="col-lg-6">
                            <label class="form-label fw-bold mb-3">Care Specializations <span class="text-danger">*</span></label>

                            <div class="form-selectgroup form-selectgroup-pills d-flex flex-wrap gap-2">
                                @foreach($careTypes as $careType)
                                    <label class="form-selectgroup-item">
                                        <input type="checkbox" name="care_types[]" value="{{ $careType->id }}"
                                            class="form-selectgroup-input"
                                            {{ in_array($careType->id, old('care_types', [])) ? 'checked' : '' }} />
                                        <span class="form-selectgroup-label py-2 px-3">
                                            {{ $careType->name }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('care_types') <div class="invalid-feedback d-block mt-2">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

            </div>

            {{-- Wizard Footer Navigation --}}
            <div class="card-footer d-flex align-items-center justify-content-between py-3">
                <div>
                    <button type="button" class="btn btn-outline-secondary" id="btn-prev" style="visibility: hidden;">
                        <i class="ti ti-arrow-left me-1"></i>Previous
                    </button>
                </div>
                <div class="text-secondary small fw-medium" id="step-counter-text">
                    Step 1 of 6
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" id="btn-next">
                        Next <i class="ti ti-arrow-right ms-1"></i>
                    </button>
                    <button type="submit" class="btn btn-primary d-none" id="btn-submit">
                        <i class="ti ti-check me-1"></i>Create Nurse
                    </button>
                </div>
            </div>
        </div>

    </form>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const totalSteps = 6;
        let currentStep = 0;

        const stepName = $('#card-step-name');
        const btnPrev = $('#btn-prev');
        const btnNext = $('#btn-next');
        const btnSubmit = $('#btn-submit');
        const wizardNavButtons = $('.wizard-step-btn');
        const step0Actions = $('#step-0-actions');

        function updateStepUI(stepIndex) {
            currentStep = stepIndex;

            // Panes
            $('.step-pane').removeClass('active');
            const activePane = $(`#pane-${currentStep}`);
            activePane.addClass('active');

            // Header info
            const title = activePane.data('title');
            stepName.text(title);

            // Show Auto-Approve switch only on Step 1 (index 0)
            if (currentStep === 0) {
                step0Actions.removeClass('d-none');
            } else {
                step0Actions.addClass('d-none');
            }

            // Wizard Nav states
            wizardNavButtons.each(function(i) {
                const btn = $(this);
                btn.removeClass('active');
                if (i === currentStep) {
                    btn.addClass('active');
                }
                if (i < currentStep) {
                    btn.addClass('completed');
                    btn.find('.step-num').addClass('d-none');
                    btn.find('.step-check').removeClass('d-none');
                } else {
                    btn.removeClass('completed');
                    btn.find('.step-num').removeClass('d-none');
                    btn.find('.step-check').addClass('d-none');
                }
            });

            // Buttons
            if (currentStep === 0) {
                btnPrev.css('visibility', 'hidden');
            } else {
                btnPrev.css('visibility', 'visible');
            }

            if (currentStep === totalSteps - 1) {
                btnNext.addClass('d-none');
                btnSubmit.removeClass('d-none');
            } else {
                btnNext.removeClass('d-none');
                btnSubmit.addClass('d-none');
            }

            // Scroll to wizard card top smoothly
            $('html, body').animate({ scrollTop: $('#wizard-card').offset().top - 80 }, 200);
        }

        // Validate fields within current pane before going next
        function validateCurrentStep() {
            const currentPane = $(`#pane-${currentStep}`);
            let isValid = true;
            let firstInvalid = null;

            currentPane.find('input, select, textarea').each(function() {
                if (!this.checkValidity()) {
                    isValid = false;
                    if (!firstInvalid) {
                        firstInvalid = this;
                    }
                }
            });

            if (!isValid && firstInvalid) {
                firstInvalid.reportValidity();
                return false;
            }
            return true;
        }

        // Next button click
        btnNext.on('click', function() {
            if (!validateCurrentStep()) {
                return;
            }
            if (currentStep < totalSteps - 1) {
                updateStepUI(currentStep + 1);
            }
        });

        // Prev button click
        btnPrev.on('click', function() {
            if (currentStep > 0) {
                updateStepUI(currentStep - 1);
            }
        });

        // Step Navigation click
        wizardNavButtons.on('click', function() {
            const targetStep = parseInt($(this).data('step'));
            // If navigating forward, validate current step
            if (targetStep > currentStep) {
                if (!validateCurrentStep()) {
                    return;
                }
            }
            updateStepUI(targetStep);
        });

        // Server-side validation error auto-focus to first erroneous step
        let firstErrorStep = null;
        $('.step-pane').each(function(index) {
            if ($(this).find('.is-invalid, .invalid-feedback:visible').length > 0) {
                wizardNavButtons.eq(index).addClass('has-error');
                if (firstErrorStep === null) {
                    firstErrorStep = index;
                }
            }
        });
        if (firstErrorStep !== null) {
            updateStepUI(firstErrorStep);
        }

        // Toggle hidden input for is_available checkbox
        $('#is_available_check').on('change', function() {
            $('#is_available_hidden').prop('disabled', this.checked);
        });

        // Image upload immediate preview
        $('#avatar-upload').on('change', function () {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#avatar-preview').attr('src', e.target.result).removeClass('d-none');
            };
            reader.readAsDataURL(file);
        });

        // Repeater — Education
        let eduIndex = {{ count(old('educations', [[]])) }};
        $('#add-education').click(function() {
            $('#educations-container').append(`
                <div class="card card-body bg-light border-0 mb-3 education-row p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Degree / Course</label>
                            <input type="text" name="educations[${eduIndex}][degree_name]" class="form-control" placeholder="e.g. B.Sc Nursing, GNM">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Institution / College</label>
                            <input type="text" name="educations[${eduIndex}][institution_name]" class="form-control" placeholder="e.g. AIIMS Nursing College">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="educations[${eduIndex}][start_date]" class="form-control">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">End Date</label>
                            <input type="date" name="educations[${eduIndex}][end_date]" class="form-control">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger remove-edu w-100" title="Remove"><i class="ti ti-trash me-1"></i>Remove</button>
                        </div>
                    </div>
                </div>
            `);
            eduIndex++;
        });
        $(document).on('click', '.remove-edu', function() { $(this).closest('.education-row').remove(); });

        // Repeater — Experience
        let expIndex = {{ count(old('experiences', [[]])) }};
        $('#add-experience').click(function() {
            $('#experiences-container').append(`
                <div class="card card-body bg-light border-0 mb-3 experience-row p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Designation / Role</label>
                            <input type="text" name="experiences[${expIndex}][designation]" class="form-control" placeholder="e.g. ICU Staff Nurse">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Hospital / Clinic</label>
                            <input type="text" name="experiences[${expIndex}][hospital_name]" class="form-control" placeholder="e.g. Max Healthcare">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="experiences[${expIndex}][start_date]" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Date</label>
                            <input type="date" name="experiences[${expIndex}][end_date]" class="form-control">
                        </div>
                        <div class="col-md-4 d-flex flex-column justify-content-end">
                            <div class="d-flex align-items-center gap-3">
                                <label class="form-check form-check-inline m-0">
                                    <input class="form-check-input" type="checkbox" name="experiences[${expIndex}][is_currently_working]" value="1">
                                    <span class="form-check-label fw-medium">Present</span>
                                </label>
                                <button type="button" class="btn btn-outline-danger remove-exp flex-grow-1" title="Remove"><i class="ti ti-trash me-1"></i>Remove</button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            expIndex++;
        });
        $(document).on('click', '.remove-exp', function() { $(this).closest('.experience-row').remove(); });
    });
</script>
@endpush
