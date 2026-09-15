@extends('admin.layouts.app')

@section('title', 'Admin Profile')

@section('page_title', 'My Profile')

@section('content')

    <!--begin::Toolbar-->
    <div  class="page-header d-print-none py-3 py-lg-6">
        <div  class="container-xl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <x-page-header title="My Profile" description="Update your personal details and security" />
                <x-breadcrumb :items="[
                    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                    ['label' => 'My Profile'],
                ]" />
            </div>
            
            <!--begin::Actions-->
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-light fw-bold small">
                    Discard
                </a>
                <button type="submit" form="kt_account_profile_details_form" class="btn btn-sm btn-primary fw-bold shadow-sm small">
                    <i class="ti ti-check me-1"></i>Save Changes
                </button>
            </div>
            <!--end::Actions-->
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div >
        <div  class="container-xl">

            <x-alert-success />

            <x-form-errors />

            <!--begin::Profile Header-->
            <div class="card mb-5 mb-xl-7 card-bordered border shadow-sm">
                <div class="card-body pt-6 pb-4">
                    <!--begin::Details-->
                    <div class="d-flex flex-wrap flex-sm-nowrap">
                        <!--begin::Pic-->
                        <div class="me-5 mb-2">
                            <div class="symbol symbol-70px symbol-lg-100px symbol-fixed position-relative">
                                <span class="symbol-label bg-light-primary text-primary fw-bold fs-1">
                                    {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
                                </span>
                                <div class="position-absolute translate-middle bottom-0 start-100 mb-2 bg-success rounded-circle border border-4 border-body h-15px w-15px" title="Active"></div>
                            </div>
                        </div>
                        <!--end::Pic-->
                        
                        <!--begin::Info-->
                        <div class="flex-grow-1">
                            <!--begin::Title-->
                            <div class="d-flex justify-content-between align-items-start flex-wrap mb-1">
                                <div class="d-flex flex-column">
                                    <!--begin::Name-->
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="text-body h4 fw-bold me-1">{{ $user->name }}</span>
                                        <i class="ti ti-circle-check fs-3 text-primary"></i>
                                    </div>
                                    <!--end::Name-->

                                    <!--begin::Info-->
                                    <div class="d-flex flex-wrap fw-semibold small mb-2 pe-2">
                                        <span class="d-flex align-items-center text-muted me-4 mb-1">
                                            <i class="ti ti-circle me-1"></i> Admin Account
                                        </span>
                                        <span class="d-flex align-items-center text-muted me-4 mb-1">
                                            <i class="ti ti-mail me-1"></i> {{ $user->email }}
                                        </span>
                                        @if($user->phone)
                                        <span class="d-flex align-items-center text-muted mb-1">
                                            <i class="ti ti-phone me-1"></i> {{ $user->phone }}
                                        </span>
                                        @endif
                                    </div>
                                    <!--end::Info-->
                                </div>
                                <div class="d-flex my-2">
                                    <span class="badge badge bg-green-lt border border-success fw-bold px-2 py-1 text-uppercase">
                                        <i class="ti ti-shield-check small text-success me-1"></i> Super Admin
                                    </span>
                                </div>
                            </div>
                            <!--end::Title-->

                            <!--begin::Stats-->
                            <div class="d-flex flex-wrap flex-stack">
                                <div class="d-flex flex-column flex-grow-1 pe-8">
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="border border border-dashed rounded py-2 px-3">
                                            <div class="d-flex align-items-center">
                                                <i class="ti ti-circle h4 text-info me-2"></i>
                                                <div class=" fw-bold text-body">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</div>
                                            </div>
                                            <div class="fw-semibold text-muted">Last Login</div>
                                        </div>
                                        <div class="border border border-dashed rounded py-2 px-3">
                                            <div class="d-flex align-items-center">
                                                <i class="ti ti-calendar-add h4 text-warning me-2"></i>
                                                <div class=" fw-bold text-body">{{ $user->created_at->format('M Y') }}</div>
                                            </div>
                                            <div class="fw-semibold text-muted">Joined</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Stats-->
                        </div>
                        <!--end::Info-->
                    </div>
                    <!--end::Details-->
                </div>
            </div>
            <!--end::Profile Header-->

            <!--begin::Form-->
            <form  action="{{ route('admin.profile.update') }}" method="POST" class="form d-flex flex-column gap-5 gap-lg-7">
                @csrf

                <!--begin::Card-->
                <div class="card card-flush py-4 card-bordered border shadow-sm">
                    <div class="card-header border-0 pt-4 min-h-40px">
                        <div class="card-title">
                            <h2 class=" fw-bold text-body text-uppercase m-0">
                                <i class="ti ti-user-edit fs-3 text-primary me-2"></i>Personal Information
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        <!--begin::Input group-->
                        <div class="row mb-5">
                            <div class="col-lg-6 mb-5 mb-lg-0">
                                <label class="required form-label text-secondary fw-semibold small mb-1">Full Name</label>
                                <div class="position-relative">
                                    <i class="ti ti-user h4 position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                                    <input type="text" name="name" class="form-control form-control-sm text-body border border bg-transparent ps-10 small" placeholder="Full name" value="{{ old('name', $user->name) }}" required />
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <label class="required form-label text-secondary fw-semibold small mb-1">Email Address</label>
                                <div class="position-relative">
                                    <i class="ti ti-mail h4 position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                                    <input type="email" name="email" class="form-control form-control-sm text-body border border bg-transparent ps-10 small" placeholder="Email address" value="{{ old('email', $user->email) }}" required />
                                </div>
                            </div>
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="row mb-0">
                            <div class="col-lg-6">
                                <label class="form-label text-secondary fw-semibold small mb-1">Phone Number</label>
                                <div class="position-relative">
                                    <i class="ti ti-phone h4 position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                                    <input type="text" name="phone" class="form-control form-control-sm text-body border border bg-transparent ps-10 small" placeholder="Phone number" value="{{ old('phone', $user->phone) }}" />
                                </div>
                            </div>
                        </div>
                        <!--end::Input group-->
                    </div>
                </div>
                <!--end::Card-->

                <!--begin::Card-->
                <div class="card card-flush py-4 card-bordered border shadow-sm">
                    <div class="card-header border-0 pt-4 min-h-40px">
                        <div class="card-title">
                            <h2 class=" fw-bold text-body text-uppercase m-0">
                                <i class="ti ti-shield-search fs-3 text-primary me-2"></i>Security Settings
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        
                        <!--begin::Input group-->
                        <div class="row mb-5">
                            <div class="col-lg-6 mb-5 mb-lg-0">
                                <label class="form-label text-secondary fw-semibold small mb-1">Current Password</label>
                                <div class="position-relative">
                                    <i class="ti ti-lock-2 h4 position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                                    <input type="password" name="current_password" class="form-control form-control-sm text-body border border bg-transparent ps-10 small" placeholder="Current password" />
                                </div>
                                <div class="text-muted mt-1">Required only if setting a new password.</div>
                            </div>

                            <div class="col-lg-6">
                                <label class="form-label text-secondary fw-semibold small mb-1">New Password</label>
                                <div class="position-relative">
                                    <i class="ti ti-circle h4 position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                                    <input type="password" name="new_password" class="form-control form-control-sm text-body border border bg-transparent ps-10 small" placeholder="New password" />
                                </div>
                                <div class="text-muted mt-1">Must be at least 8 characters long.</div>
                            </div>
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="row mb-0">
                            <div class="col-lg-6">
                                <label class="form-label text-secondary fw-semibold small mb-1">Confirm New Password</label>
                                <div class="position-relative">
                                    <i class="ti ti-circle h4 position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                                    <input type="password" name="new_password_confirmation" class="form-control form-control-sm text-body border border bg-transparent ps-10 small" placeholder="Confirm new password" />
                                </div>
                            </div>
                        </div>
                        <!--end::Input group-->
                        
                    </div>
                    
                    <div class="card-footer d-flex justify-content-end py-4 px-9 border-0">
                        <button type="submit" class="btn btn-sm btn-primary fw-bold shadow-sm small">
                            <i class="ti ti-check me-1"></i>Save Changes
                        </button>
                    </div>
                </div>
                <!--end::Card-->

            </form>
            <!--end::Form-->
        </div>
    </div>
@endsection
