@extends('admin.layouts.app')

@section('title', $careType->name)

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'Services'],
                    ['label' => 'Care Types', 'url' => route('admin.services.care-types.index')],
                    ['label' => $careType->name],
                ]" />
                <h2 class="page-title">{{ $careType->name }}</h2>
            </div>
            <div class="col-auto ms-auto d-flex gap-2">
                <a href="{{ route('admin.services.care-types.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
                <a href="{{ route('admin.services.care-types.edit', $careType) }}" class="btn btn-primary shadow-sm">
                    <i class="ti ti-edit me-1"></i>Edit
                </a>
            </div>
        </div>
    </div>

    {{-- Top Header Card — Care Type Details --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center g-4">
                
                {{-- Thumbnail & Name --}}
                <div class="col-md-5 col-sm-12">
                    <div class="d-flex align-items-center">
                        @if(!empty(trim($careType->image_path ?? '')))
                            <img src="{{ Storage::url($careType->image_path) }}" alt="{{ $careType->name }}" class="rounded shadow-sm me-3" style="width: 60px; height: 60px; object-fit: cover;" />
                        @else
                            <div class="avatar avatar-xl bg-light-primary text-primary fw-bold rounded shadow-sm me-3" style="width: 60px; height: 60px;">
                                {{ strtoupper(substr($careType->name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h3 class="fw-bold mb-1">{{ $careType->name }}</h3>
                            <span class="text-secondary small">Care Type</span>
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div class="col-md-3 col-sm-6 border-start">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Status</div>
                    @if($careType->status === \App\Models\CareType::STATUS_ACTIVE)
                        <span class="badge badge-outline text-green border-green fs-9 px-2 py-1">Active</span>
                    @elseif($careType->status === \App\Models\CareType::STATUS_INACTIVE)
                        <span class="badge badge-outline text-red border-red fs-9 px-2 py-1">Inactive</span>
                    @else
                        <span class="badge badge-outline text-yellow border-yellow fs-9 px-2 py-1">Draft</span>
                    @endif
                </div>

                {{-- Commission --}}
                <div class="col-md-4 col-sm-6 border-start">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Commission</div>
                    <div class="d-flex align-items-center">
                        <span class="avatar avatar-sm rounded-circle bg-primary-lt me-2"><i class="ti ti-cash"></i></span>
                        <div>
                            @if($careType->commision_value !== null)
                                <span class="fw-bold d-block text-body">
                                    @if($careType->commision_type == \App\Models\CareType::COMMISION_TYPE_PERCENT)
                                        {{ $careType->commision_value ?? 0 }} %
                                    @elseif($careType->commision_type == \App\Models\CareType::COMMISION_TYPE_FLAT_FIXED)
                                        ₹{{ $careType->commision_value ?? 0 }} Flat
                                    @else
                                        ₹{{ $careType->commision_value ?? 0 }} / day
                                    @endif
                                </span>
                                <span class="text-secondary small">{{ \App\Models\CareType::getCommisionTypeList()[$careType->commision_type] ?? 'Unknown' }}</span>
                            @else
                                <span class="fw-medium text-muted">N/A</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Section --}}
    <div class="card mb-3">
        <div class="card-header border-bottom-0 pb-0 pt-3">
            <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                <li class="nav-item">
                    <a href="#tabs-overview" class="nav-link active fw-medium pb-3" data-bs-toggle="tab">
                        <i class="ti ti-info-circle me-1"></i> Overview
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#tabs-bookings" class="nav-link fw-medium pb-3" data-bs-toggle="tab">
                        <i class="ti ti-calendar-event me-1"></i> Bookings
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="card-body">
            <div class="tab-content">
                
                {{-- ── OVERVIEW TAB ── --}}
                <div class="tab-pane active show" id="tabs-overview">
                    
                    <div class="mb-5">
                        <h4 class="fw-bold mb-2">Description</h4>
                        <div class="text-secondary">
                            {{ $careType->description ?: 'No description provided.' }}
                        </div>
                    </div>

                    <h4 class="fw-bold mb-3">System Information</h4>
                    {{-- System Meta Row --}}
                    <div class="row row-cards">
                        <div class="col-sm-6 col-lg-4">
                            <div class="card card-sm shadow-none border">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <span class="bg-primary-lt text-primary avatar rounded">
                                                <i class="ti ti-calendar-plus fs-2"></i>
                                            </span>
                                        </div>
                                        <div class="col">
                                            <div class="font-weight-medium text-dark">
                                                Created On
                                            </div>
                                            <div class="text-secondary small">
                                                {{ $careType->created_at ? $careType->created_at->format('d M Y, h:i A') : 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="card card-sm shadow-none border">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <span class="bg-secondary-lt text-secondary avatar rounded">
                                                <i class="ti ti-clock-edit fs-2"></i>
                                            </span>
                                        </div>
                                        <div class="col">
                                            <div class="font-weight-medium text-dark">
                                                Last Updated
                                            </div>
                                            <div class="text-secondary small">
                                                {{ $careType->updated_at ? $careType->updated_at->format('d M Y, h:i A') : 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── BOOKINGS TAB ── --}}
                <div class="tab-pane" id="tabs-bookings">
                    <div class="empty py-5">
                        <div class="empty-icon">
                            <i class="ti ti-calendar-off text-muted fs-1"></i>
                        </div>
                        <p class="empty-title h3 mt-3">No Bookings Yet</p>
                        <p class="empty-subtitle text-secondary">
                            Bookings related to this care type will appear here once they are created.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection