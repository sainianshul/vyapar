@extends('admin.layouts.app')

@section('title', $patient->name . ' — Patient')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'People'],
                    ['label' => 'Patients', 'url' => route('admin.patients.index')],
                    ['label' => $patient->name],
                ]" />
                <h2 class="page-title">{{ $patient->name }}</h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="{{ route('admin.patients.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
                <a href="{{ route('admin.patients.edit', $patient) }}" class="btn btn-primary">
                    <i class="ti ti-pencil me-1"></i>Edit Patient
                </a>
            </div>
        </div>
    </div>

    <x-alert-success />

    {{-- Profile Card --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto">
                    @if($patient->profile_photo)
                        <span class="avatar avatar-xl" style="background-image: url({{ Storage::url($patient->profile_photo) }})"></span>
                    @else
                        <span class="avatar avatar-xl bg-primary-lt fs-2 fw-bold">
                            {{ mb_strtoupper(mb_substr($patient->name, 0, 2)) }}
                        </span>
                    @endif
                </div>
                <div class="col">
                    <div class="d-flex align-items-center mb-1">
                        <h2 class="mb-0 me-2">{{ $patient->name }}</h2>
                        @php
                            $colorMap = ['success' => 'green', 'danger' => 'red', 'secondary' => 'secondary'];
                            $badgeColor = $colorMap[$patient->status_color] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $badgeColor }}-lt text-{{ $badgeColor }}">
                            {{ $patient->status_name }}
                        </span>
                    </div>
                    <div class="d-flex flex-wrap gap-3 text-secondary mt-1">
                        <span class="d-flex align-items-center">
                            <i class="ti ti-phone me-1"></i>{{ $patient->phone }}
                        </span>
                        @if($patient->email)
                            <span class="d-flex align-items-center">
                                <i class="ti ti-mail me-1"></i>{{ $patient->email }}
                            </span>
                        @endif
                        <span class="d-flex align-items-center">
                            <i class="ti ti-clock me-1"></i>
                            Last login: {{ $patient->last_login_at ? $patient->last_login_at->diffForHumans() : 'Never' }}
                        </span>
                    </div>
                </div>
                <div class="col-auto">
                    {{-- Quick Status Change --}}
                    <form action="{{ route('admin.patients.update', $patient) }}" method="POST" class="d-flex align-items-center gap-2">
                        @csrf
                        <input type="hidden" name="name" value="{{ $patient->name }}">
                        <input type="hidden" name="email" value="{{ $patient->email }}">
                        <input type="hidden" name="phone" value="{{ $patient->phone }}">
                        <select name="status" class="form-select form-select-sm" style="width: 130px;">
                            @foreach (\App\Models\User::getStatusList() as $value => $label)
                                <option value="{{ $value }}" {{ $patient->status == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-dark">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row mb-3">
        <div class="col-sm-6 col-lg-4">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-primary-lt text-primary avatar">
                                <i class="ti ti-file-text"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-3" id="stat-total-requests">
                                <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                            </div>
                            <div class="text-secondary">Total Requests</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-green-lt text-green avatar">
                                <i class="ti ti-circle-check"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-3" id="stat-completed">
                                <span class="spinner-border spinner-border-sm text-green" role="status"></span>
                            </div>
                            <div class="text-secondary">Completed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-yellow-lt text-yellow avatar">
                                <i class="ti ti-calendar"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold">
                                {{ $patient->created_at ? $patient->created_at->format('d M, Y') : 'N/A' }}
                            </div>
                            <div class="text-secondary">
                                {{ $patient->created_at ? 'Joined ' . $patient->created_at->diffForHumans() : 'Joined Date' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="card mb-3">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-requests" role="tab">
                        <i class="ti ti-file-text me-1"></i>Requests
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-bookings" role="tab"
                       onclick="loadTabContent('{{ route('admin.patients.bookings', $patient->id) }}', 'tab-bookings')">
                        <i class="ti ti-calendar-event me-1"></i>Bookings
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-activity" role="tab">
                        <i class="ti ti-activity me-1"></i>Activity Logs
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-login-history" role="tab"
                       onclick="loadTabContent('{{ route('admin.patients.login-history', $patient->id) }}', 'tab-login-history')">
                        <i class="ti ti-history me-1"></i>Login History
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-sms" role="tab">
                        <i class="ti ti-message me-1"></i>Send SMS
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">

                {{-- Requests Tab --}}
                <div class="tab-pane fade show active" id="tab-requests" role="tabpanel">
                    @include('admin.patients.tabs.requests')
                </div>

                {{-- Bookings Tab --}}
                <div class="tab-pane fade" id="tab-bookings" role="tabpanel">
                    <div class="d-flex justify-content-center align-items-center py-5" id="loader_tab-bookings">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>

                {{-- Activity Tab --}}
                <div class="tab-pane fade" id="tab-activity" role="tabpanel">
                    <div class="empty">
                        <div class="empty-icon">
                            <i class="ti ti-activity" style="font-size: 3rem;"></i>
                        </div>
                        <p class="empty-title">No Activity Recorded</p>
                        <p class="empty-subtitle text-secondary">
                            System activities related to this patient will appear here.
                        </p>
                    </div>
                </div>

                {{-- Login History Tab --}}
                <div class="tab-pane fade" id="tab-login-history" role="tabpanel">
                    <div class="d-flex justify-content-center align-items-center py-5" id="loader_tab-login-history">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>

                {{-- SMS Tab --}}
                <div class="tab-pane fade" id="tab-sms" role="tabpanel">
                    <form action="#" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Recipient Phone Number</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-phone"></i></span>
                                <input type="text" class="form-control" value="{{ $patient->phone }}" readonly disabled />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Message</label>
                            <textarea name="message" class="form-control" rows="4" placeholder="Type your message here..."></textarea>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary"
                                onclick="event.preventDefault(); Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: 'SMS Sent Successfully! (Dummy)' });">
                                <i class="ti ti-send me-1"></i>Send SMS
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    {{-- Comments --}}
    <x-comments type="{{ \App\Models\Comment::TYPE_PATIENT }}" :model-id="$patient->id" />

@endsection

@push('datatables_css')
    @include('admin.layouts.partials._datatable-cdn-css')
@endpush

@push('datatables_js')
    @include('admin.layouts.partials._datatable-cdn-js')
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fetch Stats
        fetch('{{ route('admin.patients.stats', $patient->id) }}')
            .then(response => response.json())
            .then(data => {
                document.getElementById('stat-total-requests').innerHTML = data.total_requests;
                document.getElementById('stat-completed').innerHTML = data.completed;
            })
            .catch(error => {
                document.getElementById('stat-total-requests').innerHTML = '-';
                document.getElementById('stat-completed').innerHTML = '-';
            });
    });

    function loadTabContent(url, tabId) {
        const tabPane = document.getElementById(tabId);

        // If content already loaded (loader is gone), don't reload
        if (!tabPane.querySelector('#loader_' + tabId)) {
            return;
        }

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(html => {
                tabPane.innerHTML = html;

                // Execute any scripts that came with the HTML
                const scripts = tabPane.getElementsByTagName('script');
                for (let i = 0; i < scripts.length; i++) {
                    const newScript = document.createElement('script');
                    newScript.text = scripts[i].text;
                    document.body.appendChild(newScript).parentNode.removeChild(newScript);
                }
            })
            .catch(error => {
                tabPane.innerHTML = '<div class="alert alert-danger m-3">Failed to load content. Please try again.</div>';
                console.error('Error loading tab content:', error);
            });
    }
</script>
@endpush
