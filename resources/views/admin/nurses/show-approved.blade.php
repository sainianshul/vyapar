@extends('admin.layouts.app')
@section('title', 'Nurse Profile — ' . $user->name)

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'People'],
                    ['label' => 'Nurses', 'url' => route('admin.nurses.index')],
                    ['label' => $user->name],
                ]" />
                <h2 class="page-title">{{ $user->name }}</h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="{{ route('admin.nurses.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
                <a href="{{ route('admin.nurses.show-application', $user->id) }}" class="btn btn-outline-primary"
                    data-bs-toggle="tooltip" title="View onboarding application and verification history">
                    <i class="ti ti-folder-check me-1"></i>Verification History
                </a>
                <a href="{{ route('admin.nurses.edit', $user->id) }}" class="btn btn-primary">
                    <i class="ti ti-pencil me-1"></i>Edit Nurse
                </a>

                {{-- Status Change Dropdown --}}
                <div class="dropdown">
                    <button class="btn btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-settings me-1"></i>Status
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><h6 class="dropdown-header">Change Account Status</h6></li>
                        @if($profile->status != \App\Models\NurseProfile::STATUS_APPROVED)
                            <li>
                                <form action="{{ route('admin.nurses.status.update', $user->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ \App\Models\NurseProfile::STATUS_APPROVED }}">
                                    <button type="submit" class="dropdown-item text-success" onclick="return confirm('Are you sure you want to approve/reactivate this nurse?')">
                                        <i class="ti ti-check me-2 text-success"></i>Approve / Active
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
                                    <button type="submit" class="dropdown-item text-warning" onclick="return confirm('Are you sure you want to suspend this nurse account?')">
                                        <i class="ti ti-alert-triangle me-2 text-warning"></i>Suspend Account
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
                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to mark this nurse as rejected?')">
                                        <i class="ti ti-x me-2 text-danger"></i>Reject Application
                                    </button>
                                </form>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <x-alert-success />

    {{-- Profile Header --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto">
                    @if($user->profile_photo)
                        <span class="avatar avatar-xl rounded-circle" style="background-image: url({{ Storage::url($user->profile_photo) }})"></span>
                    @else
                        <span class="avatar avatar-xl rounded-circle bg-primary-lt fs-2 fw-bold">
                            {{ mb_strtoupper(mb_substr($user->name, 0, 2)) }}
                        </span>
                    @endif
                </div>
                <div class="col">
                    <div class="d-flex align-items-center mb-1 flex-wrap gap-2">
                        <h2 class="mb-0 me-1">{{ $user->name }}</h2>
                        @php
                            $statusColor = match($profile->status) {
                                \App\Models\NurseProfile::STATUS_APPROVED => 'green',
                                \App\Models\NurseProfile::STATUS_SUSPENDED => 'yellow',
                                \App\Models\NurseProfile::STATUS_REJECTED => 'red',
                                default => 'primary',
                            };
                        @endphp
                        <span class="badge bg-{{ $statusColor }}-lt">{{ $profile->status_name ?? 'Active' }}</span>
                        @if($profile->status === \App\Models\NurseProfile::STATUS_APPROVED)
                            <span class="badge bg-blue-lt"><i class="ti ti-circle-check me-1"></i>Verified</span>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap gap-3 text-secondary small mt-1">
                        <span><i class="ti ti-mail me-1"></i>{{ $user->email }}</span>
                        <span><i class="ti ti-phone me-1"></i>{{ $user->phone ?? 'N/A' }}</span>
                        <span><i class="ti ti-map-pin me-1"></i>{{ $profile->city ?? 'N/A' }}, {{ $profile->state ?? 'N/A' }}</span>
                        <span><i class="ti ti-clock me-1"></i>Joined {{ $user->created_at->format('d M Y') }}</span>
                    </div>
                </div>
                <div class="col-auto d-none d-md-block">
                    <div class="d-flex flex-wrap gap-1 justify-content-end" style="max-width: 220px;">
                        @forelse($profile->careTypes as $careType)
                            <span class="badge bg-blue-lt">{{ $careType->name }}</span>
                        @empty
                            <span class="text-secondary small">No specializations</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-primary-lt text-primary avatar">
                                <i class="ti ti-calendar-event"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-3">{{ $profile->total_bookings ?? 0 }}</div>
                            <div class="text-secondary">Total Bookings</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-green-lt text-green avatar">
                                <i class="ti ti-circle-check"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-3">{{ $profile->total_bookings_completed ?? 0 }}</div>
                            <div class="text-secondary">Completed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-yellow-lt text-yellow avatar">
                                <i class="ti ti-star"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-3">
                                {{ number_format($profile->avg_rating ?? 0, 1) }}
                                <span class="fs-6 fw-normal text-secondary">({{ $profile->total_reviews ?? 0 }})</span>
                            </div>
                            <div class="text-secondary">Rating</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-cyan-lt text-cyan avatar">
                                <i class="ti ti-shield-check"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-3">{{ $profile->trust_score ?? 100 }}%</div>
                            <div class="text-secondary">Trust Score</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Card --}}
    <div class="card mb-3">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-overview" role="tab">
                        <i class="ti ti-user me-1"></i>Overview
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-bookings" role="tab"
                       onclick="loadNurseTab('{{ route('admin.nurses.bookings', $user->id) }}', 'tab-bookings')">
                        <i class="ti ti-calendar-event me-1"></i>Bookings
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-bids" role="tab"
                       onclick="loadNurseTab('{{ route('admin.nurses.bids', $user->id) }}', 'tab-bids')">
                        <i class="ti ti-gavel me-1"></i>Bids
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-care-requests" role="tab"
                       onclick="loadNurseTab('{{ route('admin.nurses.care-requests', $user->id) }}', 'tab-care-requests')">
                        <i class="ti ti-bell me-1"></i>Care Requests
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-reviews" role="tab"
                       onclick="loadNurseTab('{{ route('admin.nurses.reviews', $user->id) }}', 'tab-reviews')">
                        <i class="ti ti-star me-1"></i>Reviews
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-login-history" role="tab"
                       onclick="loadNurseTab('{{ route('admin.nurses.login-history', $user->id) }}', 'tab-login-history')">
                        <i class="ti ti-history me-1"></i>Login History
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-contact" role="tab"
                       onclick="loadNurseTab('{{ route('admin.nurses.contact-form', $user->id) }}', 'tab-contact')">
                        <i class="ti ti-mail me-1"></i>Contact
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">

                {{-- Tab: Overview --}}
                <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">

                    {{-- Personal & Professional Details - clean datagrid --}}
                    <div class="row g-4 mb-4">
                        <div class="col-lg-6">
                            <h3 class="mb-3">Professional Details</h3>
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Experience</div>
                                    <div class="datagrid-content">{{ $profile->years_of_experience ?? 0 }} Years</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">License Number</div>
                                    <div class="datagrid-content">
                                        @if($profile->license_number)
                                            <span class="badge bg-blue-lt">{{ $profile->license_number }}</span>
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Gender</div>
                                    <div class="datagrid-content">
                                        @if($profile->gender == \App\Models\NurseProfile::GENDER_MALE) Male
                                        @elseif($profile->gender == \App\Models\NurseProfile::GENDER_OTHER) Other
                                        @else Female @endif
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Availability</div>
                                    <div class="datagrid-content">
                                        @if($profile->is_available)
                                            <span class="badge bg-green-lt"><i class="ti ti-check me-1"></i>Active</span>
                                        @else
                                            <span class="badge bg-red-lt"><i class="ti ti-x me-1"></i>Unavailable</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Working Hours</div>
                                    <div class="datagrid-content">
                                        @if($profile->available_from && $profile->available_to)
                                            {{ \Carbon\Carbon::parse($profile->available_from)->format('h:i A') }} — {{ \Carbon\Carbon::parse($profile->available_to)->format('h:i A') }}
                                        @else
                                            Not specified
                                        @endif
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Working Days</div>
                                    <div class="datagrid-content">
                                        @php
                                            $daysList = \App\Models\NurseProfile::getDaysList();
                                            $selectedDays = is_array($profile->available_days) ? $profile->available_days : (is_string($profile->available_days) ? json_decode($profile->available_days, true) : []);
                                        @endphp
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($daysList as $key => $label)
                                                @if(is_array($selectedDays) && in_array($key, $selectedDays))
                                                    <span class="badge bg-primary">{{ substr($label, 0, 3) }}</span>
                                                @else
                                                    <span class="badge bg-secondary-lt text-muted">{{ substr($label, 0, 3) }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <h3 class="mb-3">Address & Location</h3>
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Full Address</div>
                                    <div class="datagrid-content">{{ $profile->address ?? 'N/A' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">City</div>
                                    <div class="datagrid-content">{{ $profile->city ?? 'N/A' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">State</div>
                                    <div class="datagrid-content">{{ $profile->state ?? 'N/A' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Pincode</div>
                                    <div class="datagrid-content">{{ $profile->pincode ?? 'N/A' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Country</div>
                                    <div class="datagrid-content">{{ $profile->country ?? 'N/A' }}</div>
                                </div>
                                @if($user->created_by_admin)
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Source</div>
                                    <div class="datagrid-content"><span class="badge bg-cyan-lt"><i class="ti ti-shield-check me-1"></i>Created by Admin</span></div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($profile->bio)
                        <div class="mb-4">
                            <h3 class="mb-2">About</h3>
                            <div class="text-secondary lh-lg">
                                {!! nl2br(e($profile->bio)) !!}
                            </div>
                        </div>
                    @endif

                    <hr class="my-4">

                    {{-- Education & Work side by side --}}
                    <div class="row g-4 mb-4">
                        <div class="col-lg-6">
                            <h3 class="mb-3">Education</h3>
                            @forelse($user->nurseProfile->educations as $edu)
                                <div class="d-flex align-items-start {{ !$loop->last ? 'mb-3 pb-3 border-bottom' : '' }}">
                                    <span class="avatar avatar-sm bg-primary-lt rounded me-3 mt-1">
                                        <i class="ti ti-school"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold">{{ $edu->degree_name ?? $edu->degree_or_course }}</div>
                                        <div class="text-secondary small">{{ $edu->institution_name ?? $edu->institute_name }}</div>
                                        <div class="text-muted small">
                                            {{ $edu->start_date ? \Carbon\Carbon::parse($edu->start_date)->format('Y') : '' }} –
                                            {{ $edu->end_date ? \Carbon\Carbon::parse($edu->end_date)->format('Y') : 'Present' }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-secondary small">No education records provided.</div>
                            @endforelse
                        </div>
                        <div class="col-lg-6">
                            <h3 class="mb-3">Work Experience</h3>
                            @forelse($user->nurseProfile->workHistories as $exp)
                                <div class="d-flex align-items-start {{ !$loop->last ? 'mb-3 pb-3 border-bottom' : '' }}">
                                    <span class="avatar avatar-sm bg-azure-lt rounded me-3 mt-1">
                                        <i class="ti ti-briefcase"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold">{{ $exp->designation ?? $exp->role_or_position }}</div>
                                        <div class="text-secondary small">{{ $exp->hospital_name ?? $exp->organization_name }}</div>
                                        <div class="text-muted small">
                                            {{ $exp->start_date ? \Carbon\Carbon::parse($exp->start_date)->format('M Y') : '' }} –
                                            {{ $exp->is_currently_working ? 'Present' : ($exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('M Y') : 'N/A') }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-secondary small">No work history records provided.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Documents - compact list --}}
                    @if($user->nurseProfile->documents->count() > 0)
                        <hr class="my-4">
                        <h3 class="mb-3">Documents ({{ $user->nurseProfile->documents->count() }})</h3>
                        <div class="table-responsive">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Document</th>
                                        <th>Type</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->nurseProfile->documents as $doc)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="avatar avatar-sm bg-secondary-lt rounded me-2">
                                                        <i class="ti ti-file-certificate"></i>
                                                    </span>
                                                    <span class="fw-semibold">{{ $doc->title }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary-lt text-uppercase">{{ pathinfo($doc->file_path, PATHINFO_EXTENSION) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.nurses.document', $doc->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="ti ti-external-link me-1"></i>View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                </div>

                {{-- Tab: Bookings --}}
                <div class="tab-pane fade" id="tab-bookings" role="tabpanel">
                    <div class="d-flex justify-content-center align-items-center py-5" id="loader_tab-bookings">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading…</span></div>
                    </div>
                </div>

                {{-- Tab: Bids --}}
                <div class="tab-pane fade" id="tab-bids" role="tabpanel">
                    <div class="d-flex justify-content-center align-items-center py-5" id="loader_tab-bids">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading…</span></div>
                    </div>
                </div>

                {{-- Tab: Care Requests --}}
                <div class="tab-pane fade" id="tab-care-requests" role="tabpanel">
                    <div class="d-flex justify-content-center align-items-center py-5" id="loader_tab-care-requests">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading…</span></div>
                    </div>
                </div>

                {{-- Tab: Reviews --}}
                <div class="tab-pane fade" id="tab-reviews" role="tabpanel">
                    <div class="d-flex justify-content-center align-items-center py-5" id="loader_tab-reviews">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading…</span></div>
                    </div>
                </div>

                {{-- Tab: Login History --}}
                <div class="tab-pane fade" id="tab-login-history" role="tabpanel">
                    <div class="d-flex justify-content-center align-items-center py-5" id="loader_tab-login-history">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading…</span></div>
                    </div>
                </div>

                {{-- Tab: Contact Nurse --}}
                <div class="tab-pane fade" id="tab-contact" role="tabpanel">
                    <div class="d-flex justify-content-center align-items-center py-5" id="loader_tab-contact">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading…</span></div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Comments / Admin Notes --}}
    <x-comments type="nurse" :model-id="$user->id" />

@endsection

@push('datatables_css')
    @include('admin.layouts.partials._datatable-cdn-css')
@endpush

@push('datatables_js')
    @include('admin.layouts.partials._datatable-cdn-js')
@endpush

@push('scripts')
<script>
    function loadNurseTab(url, tabId) {
        const tabPane = document.getElementById(tabId);
        if (!tabPane || !tabPane.querySelector('#loader_' + tabId)) {
            return;
        }

        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.text();
            })
            .then(html => {
                tabPane.innerHTML = html;
                const scripts = tabPane.getElementsByTagName('script');
                for (let i = 0; i < scripts.length; i++) {
                    const newScript = document.createElement('script');
                    newScript.text = scripts[i].text;
                    document.body.appendChild(newScript).parentNode.removeChild(newScript);
                }
            })
            .catch(err => {
                tabPane.innerHTML = '<div class="alert alert-danger m-3">Failed to load content. Please try again.</div>';
                console.error('Error loading tab:', err);
            });
    }
</script>
@endpush
