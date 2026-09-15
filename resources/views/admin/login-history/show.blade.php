@extends('admin.layouts.app')

@section('title', 'Login Details #' . $loginHistory->id)

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'System'],
                    ['label' => 'Login History', 'url' => route('admin.login-history.index')],
                    ['label' => '#' . $loginHistory->id],
                ]" />
                <h2 class="page-title">Login Details <span class="text-primary ms-2">#{{ $loginHistory->id }}</span></h2>
            </div>
            <div class="col-auto ms-auto d-flex gap-2">
                <a href="{{ route('admin.login-history.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Main Column --}}
        <div class="col-xl-8">
            
            {{-- IP Location Overview --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">IP Intelligence</h3>
                        <p class="card-subtitle">Geographic & network routing data</p>
                    </div>
                    <div class="card-actions">
                        <span class="badge badge-outline text-primary border-primary px-2 py-1 fs-9">
                            <i class="ti ti-map-pin me-1"></i> {{ $loginHistory->ip_address }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    
                    @if($ipLocation && $ipLocation->isSuccess())
                        <div class="d-flex flex-wrap gap-4 align-items-center">
                            
                            {{-- Icon --}}
                            <div class="avatar avatar-xl bg-primary-lt rounded">
                                <i class="ti ti-map text-primary fs-1"></i>
                            </div>

                            <div class="flex-grow-1">
                                <div class="row g-3">
                                    {{-- Left Stats --}}
                                    <div class="col-sm-6 border-end-sm pe-sm-3">
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-secondary small text-uppercase fw-semibold">Country</span>
                                            <span class="fw-medium text-body">{{ $ipLocation->getCountry() ?: '—' }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-secondary small text-uppercase fw-semibold">Region</span>
                                            <span class="fw-medium text-body">{{ $ipLocation->getRegion() ?: '—' }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between py-2">
                                            <span class="text-secondary small text-uppercase fw-semibold">City</span>
                                            <span class="fw-medium text-body">{{ $ipLocation->getCity() ?: '—' }}</span>
                                        </div>
                                    </div>
                                    
                                    {{-- Right Stats --}}
                                    <div class="col-sm-6 ps-sm-3">
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-secondary small text-uppercase fw-semibold">ISP</span>
                                            <span class="fw-medium text-body">{{ $ipLocation->getIsp() ?: '—' }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-secondary small text-uppercase fw-semibold">Zip Code</span>
                                            <span class="fw-medium text-body">{{ $ipLocation->getZip() ?: '—' }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between py-2">
                                            <span class="text-secondary small text-uppercase fw-semibold">Coordinates</span>
                                            <span class="fw-medium text-body">{{ $ipLocation->getLat() ?: '—' }}, {{ $ipLocation->getLon() ?: '—' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @else
                        <!-- Fallback -->
                        <div class="d-flex align-items-center bg-yellow-lt rounded p-4">
                            <i class="ti ti-info-circle fs-1 text-yellow me-3"></i>
                            <div>
                                <h4 class="mb-1 text-dark fw-bold">Data Unavailable</h4>
                                <div class="text-secondary small">Location routing details could not be determined for this IP. It may be a local or reserved address.</div>
                            </div>
                        </div>
                    @endif

                </div>
        </div>

        {{-- Side Column --}}
        <div class="col-xl-4">
            
            {{-- User Profile --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Account Identity</h3>
                </div>
                <div class="card-body">
                    
                    @if($loginHistory->user)
                        <div class="d-flex align-items-center mb-4">
                            <div class="avatar avatar-md bg-primary-lt me-3 fw-bold">
                                {{ mb_strtoupper(mb_substr($loginHistory->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="m-0 fw-bold text-body">{{ $loginHistory->user->name }}</h4>
                                <div class="text-secondary small">ID: #{{ $loginHistory->user->id }}</div>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-3 mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-secondary small text-uppercase fw-semibold">Privilege</span>
                                @if((int)$loginHistory->user->role === \App\Models\User::ROLE_ADMIN)
                                    <span class="badge badge-outline text-red border-red fs-9 px-2 py-1">Admin</span>
                                @elseif((int)$loginHistory->user->role === \App\Models\User::ROLE_USER)
                                    <span class="badge badge-outline text-blue border-blue fs-9 px-2 py-1">Patient</span>
                                @elseif((int)$loginHistory->user->role === \App\Models\User::ROLE_NURSE)
                                    <span class="badge badge-outline text-green border-green fs-9 px-2 py-1">Nurse</span>
                                @else
                                    <span class="badge badge-outline text-secondary border-secondary fs-9 px-2 py-1">Guest</span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-secondary small text-uppercase fw-semibold">Email</span>
                                <span class="fw-medium text-body">{{ $loginHistory->user->email ?: '—' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-secondary small text-uppercase fw-semibold">Phone</span>
                                <span class="fw-medium text-body">{{ $loginHistory->user->phone ?: '—' }}</span>
                            </div>
                        </div>

                        @if((int)$loginHistory->user->role === \App\Models\User::ROLE_USER)
                            <a href="{{ route('admin.patients.show', $loginHistory->user->id) }}" class="btn btn-outline-primary w-100">
                                View Full Profile <i class="ti ti-chevron-right ms-1"></i>
                            </a>
                        @elseif((int)$loginHistory->user->role === \App\Models\User::ROLE_NURSE)
                            <a href="{{ route('admin.nurses.show', $loginHistory->user->id) }}" class="btn btn-outline-success w-100">
                                View Full Profile <i class="ti ti-chevron-right ms-1"></i>
                            </a>
                        @endif
                    @else
                        <div class="empty py-4">
                            <div class="empty-icon">
                                <i class="ti ti-user-x text-muted fs-1"></i>
                            </div>
                            <p class="empty-title mt-3 h4">Unresolved Entity</p>
                            <p class="empty-subtitle text-secondary">
                                This user account is untraceable or deleted.
                            </p>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Session Result --}}
            <div class="card mb-3">
                <div class="card-body">
                    
                    <div class="d-flex align-items-center mb-4">
                        <div class="avatar bg-light me-3">
                            <i class="ti ti-shield text-muted"></i>
                        </div>
                        <div>
                            <h4 class="m-0 fw-bold text-body">Authentication</h4>
                            <div class="text-secondary small">Session outcome</div>
                        </div>
                        <div class="ms-auto">
                            @if((int)$loginHistory->status === 1)
                                <span class="badge badge-outline text-green border-green fs-9 px-2 py-1">Authorized</span>
                            @else
                                <span class="badge badge-outline text-red border-red fs-9 px-2 py-1">Denied</span>
                            @endif
                        </div>
                    </div>

                    <div class="hr-text my-4">Timeline</div>

                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-secondary small text-uppercase fw-semibold">Date</span>
                            <span class="fw-medium text-body">{{ $loginHistory->logged_in_at ? $loginHistory->logged_in_at->format('d M Y') : '—' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-secondary small text-uppercase fw-semibold">Time</span>
                            <span class="fw-medium text-body">{{ $loginHistory->logged_in_at ? $loginHistory->logged_in_at->format('h:i A') : '—' }}</span>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

@endsection
