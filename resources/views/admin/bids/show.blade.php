@extends('admin.layouts.app')

@section('title', 'Bid Details')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'Care Requests', 'url' => route('admin.requests.index')],
                    ['label' => '#' . ($bid->careRequest->reference_id ?? 'N/A'), 'url' => route('admin.requests.show', $bid->care_request_id)],
                    ['label' => 'Bid Details'],
                ]" />
                <h2 class="page-title">Bid Details</h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="{{ route('admin.requests.show', $bid->care_request_id) }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back to Request
                </a>
            </div>
        </div>
    </div>

    <x-alert-success />
    <x-form-errors />

    {{-- Header Card — People Involved --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">

                {{-- Nurse --}}
                <div class="col-md-4 col-sm-6">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Nurse</div>
                    @if($bid->nurse && $bid->nurse->user)
                        <div class="d-flex align-items-center">
                            @if($bid->nurse->user->profile_photo)
                                <span class="avatar avatar-sm rounded-circle me-2" style="background-image: url({{ Storage::url($bid->nurse->user->profile_photo) }})"></span>
                            @else
                                <span class="avatar avatar-sm rounded-circle bg-green-lt me-2">{{ mb_strtoupper(mb_substr($bid->nurse->user->name ?? 'N', 0, 1)) }}</span>
                            @endif
                            <div>
                                <a href="{{ route('admin.nurses.show', $bid->nurse->user_id ?? 0) }}" class="fw-semibold text-reset d-block">{{ $bid->nurse->user->name ?? 'Unknown' }}</a>
                                <span class="text-secondary small">{{ $bid->nurse->user->phone ?? $bid->nurse->user->email ?? 'N/A' }}</span>
                            </div>
                        </div>
                    @else
                        <span class="text-secondary small">Unknown</span>
                    @endif
                </div>

                {{-- Patient --}}
                <div class="col-md-4 col-sm-6 border-start">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Patient</div>
                    <div class="d-flex align-items-center">
                        <span class="avatar avatar-sm rounded-circle bg-info-lt me-2"><i class="ti ti-user"></i></span>
                        <div>
                            <span class="fw-semibold d-block">
                                {{ $bid->careRequest->patient_name ?? 'N/A' }}
                                @if($bid->careRequest->patient_age)
                                    <span class="badge bg-cyan-lt ms-1">{{ $bid->careRequest->patient_age }} yrs</span>
                                @endif
                            </span>
                            <span class="text-secondary small">{{ $bid->careRequest->user->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Location --}}
                <div class="col-md-4 col-sm-6 border-start">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Service Location</div>
                    <div class="d-flex align-items-start">
                        <i class="ti ti-map-pin text-red me-2 mt-1"></i>
                        <div>
                            <span class="fw-semibold small d-block text-truncate" style="max-width: 250px;">{{ $bid->careRequest->address ?? 'N/A' }}</span>
                            <span class="text-secondary small">{{ $bid->careRequest->city ?? '' }} {{ $bid->careRequest->pincode ?? '' }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    @php
        $bidStatusColor = match(true) {
            str_contains($bid->status_color ?? '', 'success') => 'green',
            str_contains($bid->status_color ?? '', 'danger') => 'red',
            str_contains($bid->status_color ?? '', 'warning') => 'yellow',
            str_contains($bid->status_color ?? '', 'info') => 'cyan',
            default => 'primary',
        };
    @endphp
    <div class="row mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-{{ $bidStatusColor }}-lt text-{{ $bidStatusColor }} avatar">
                                <i class="ti ti-clipboard-check"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold">
                                <span class="badge bg-{{ $bidStatusColor }}-lt">{{ $bid->status_text ?? 'Unknown' }}</span>
                            </div>
                            <div class="text-secondary small">Bid Status</div>
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
                            <span class="bg-primary-lt text-primary avatar">
                                <i class="ti ti-currency-rupee"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-3">₹{{ number_format($bid->total_amount ?? 0, 2) }}</div>
                            <div class="text-secondary small">Total Amount</div>
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
                                <i class="ti ti-wallet"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-3">₹{{ number_format($bid->nurse_amount ?? 0, 2) }}</div>
                            <div class="text-secondary small">Nurse Earnings</div>
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
                                <i class="ti ti-building-bank"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-3">₹{{ number_format($bid->commission_amount ?? 0, 2) }}</div>
                            <div class="text-secondary small">Platform Commission</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Card --}}
    <div class="card mb-3">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="bid-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-overview" role="tab">
                        <i class="ti ti-info-circle me-1"></i>Overview
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="bid-tabs-content">

                {{-- Overview Tab --}}
                <div class="tab-pane fade show active" id="tab-overview">

                    <div class="row g-4">

                        {{-- Request Details --}}
                        <div class="col-lg-6">
                            <h3 class="mb-3">Request Details</h3>
                            <table class="table table-vcenter">
                                <tbody>
                                    <tr>
                                        <td class="text-secondary w-40">Reference ID</td>
                                        <td>{{ $bid->careRequest->reference_id ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary">Care Type</td>
                                        <td>{{ $bid->careRequest->careType->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary">Patient</td>
                                        <td>{{ $bid->careRequest->patient_name ?? 'N/A' }} {{ $bid->careRequest->patient_age ? '(' . $bid->careRequest->patient_age . 'y)' : '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary">Account Owner</td>
                                        <td>{{ $bid->careRequest->user->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary">Submitted</td>
                                        <td>{{ $bid->created_at ? $bid->created_at->format('d M Y, h:i A') : 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <a href="{{ route('admin.requests.show', $bid->care_request_id ?? 0) }}" class="btn btn-outline-primary btn-sm mt-2">
                                <i class="ti ti-file-text me-1"></i>View Full Request
                            </a>
                        </div>

                        {{-- Location & Distance --}}
                        <div class="col-lg-6">
                            <h3 class="mb-3">Location & Distance</h3>
                            <table class="table table-vcenter">
                                <tbody>
                                    <tr>
                                        <td class="text-secondary w-40">Request Location</td>
                                        <td>
                                            <span class="d-block">{{ $bid->careRequest->address ?? 'N/A' }}</span>
                                            <span class="text-secondary small">{{ $bid->careRequest->city ?? '' }}, {{ $bid->careRequest->state ?? '' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary">Nurse Location</td>
                                        <td>
                                            <span class="d-block">{{ $bid->nurse->address ?? 'N/A' }}</span>
                                            <span class="text-secondary small">{{ $bid->nurse->city ?? '' }}, {{ $bid->nurse->state ?? '' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary">Distance</td>
                                        <td>{{ $bid->distance_km ?? 'N/A' }} km</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>

                    <hr class="my-4">

                    <div class="row g-4">

                        {{-- Nurse Specialities --}}
                        <div class="col-lg-6">
                            <h3 class="mb-3">Nurse Specialities</h3>
                            <div class="d-flex flex-wrap gap-2">
                                @if(isset($bid->nurse->careTypes) && $bid->nurse->careTypes->count() > 0)
                                    @foreach($bid->nurse->careTypes as $spec)
                                        <span class="badge bg-blue-lt">{{ $spec->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-secondary small">No specialities listed.</span>
                                @endif
                            </div>
                        </div>

                        {{-- Bid Notes --}}
                        <div class="col-lg-6">
                            <h3 class="mb-3">Bid Notes</h3>
                            @if($bid->notes)
                                <div class="bg-light rounded p-3">
                                    <p class="mb-0">{{ $bid->notes }}</p>
                                </div>
                            @else
                                <span class="text-secondary small">No notes provided.</span>
                            @endif
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>

    {{-- Comments Section --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="ti ti-message me-2"></i>Discussion / Comments</h3>
        </div>
        <div class="card-body">
            <x-comments type="{{ \App\Models\Comment::TYPE_REQUEST_BID }}" :model-id="$bid->id" />
        </div>
    </div>

@endsection
