@extends('admin.layouts.app')

@section('title', $requirement->title . ' — Requirement Details')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'Requirements', 'url' => route('admin.requirements.index')],
                    ['label' => 'Requirement Details'],
                ]" />
                <h2 class="page-title">Requirement Details</h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="{{ route('admin.requirements.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
                <a href="{{ route('admin.requirements.edit', $requirement->id) }}" class="btn btn-primary">
                    <i class="ti ti-pencil me-1"></i>Edit Requirement
                </a>

                {{-- Status Change Dropdown --}}
                <div class="dropdown">
                    <button class="btn btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-settings me-1"></i>Status
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><h6 class="dropdown-header">Change Requirement Status</h6></li>
                        @foreach (\App\Models\Requirement::getStatusList() as $value => $label)
                            @if($requirement->status != $value)
                                <li>
                                    <a class="dropdown-item status-change-btn" href="#"
                                       data-id="{{ $requirement->id }}" data-status="{{ $value }}">
                                        @if($value == \App\Models\Requirement::STATUS_OPEN)
                                            <i class="ti ti-check me-2 text-success"></i>
                                        @elseif($value == \App\Models\Requirement::STATUS_FULFILLED)
                                            <i class="ti ti-tag me-2 text-info"></i>
                                        @elseif($value == \App\Models\Requirement::STATUS_EXPIRED)
                                            <i class="ti ti-clock-off me-2 text-warning"></i>
                                        @elseif($value == \App\Models\Requirement::STATUS_CLOSED)
                                            <i class="ti ti-ban me-2 text-danger"></i>
                                        @endif
                                        {{ $label }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Requirement Header Card --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto">
                    @if($requirement->primaryImage)
                        <span class="avatar avatar-xl rounded" style="background-image: url({{ $requirement->primaryImage->url }}); width: 120px; height: 120px; background-size: contain; background-color: #f8f9fa;"></span>
                    @else
                        <span class="avatar rounded bg-primary-lt fs-2 fw-bold" style="width: 120px; height: 120px;">
                            <i class="ti ti-package"></i>
                        </span>
                    @endif
                </div>
                <div class="col">
                    <div class="d-flex align-items-center mb-1 flex-wrap gap-2">
                        <h2 class="mb-0 me-1">{{ $requirement->title }}</h2>
                        <span class="badge bg-{{ $requirement->status_color }}-lt">
                            <i class="ti ti-circle-filled me-1"></i>{{ $requirement->status_name }}
                        </span>
                        @if($requirement->is_featured)
                            <span class="badge bg-yellow-lt"><i class="ti ti-star-filled me-1"></i>Featured</span>
                        @endif
                        <span class="badge bg-{{ $requirement->condition == \App\Models\Requirement::CONDITION_NEW ? 'green' : 'orange' }}-lt">
                            {{ $requirement->condition_name }}
                        </span>
                    </div>
                    <div class="d-flex flex-wrap gap-3 text-secondary small mt-1">
                        <span><i class="ti ti-currency-rupee me-1"></i>{{ number_format($requirement->target_budget, 2) }}{{ $requirement->target_budget_unit ? ' / ' . $requirement->target_budget_unit : '' }}</span>
                        @if($requirement->is_negotiable)
                            <span class="text-success"><i class="ti ti-arrows-exchange me-1"></i>Negotiable</span>
                        @endif
                        <span><i class="ti ti-category me-1"></i>{{ $requirement->category->name ?? 'N/A' }}</span>
                        <span><i class="ti ti-map-pin me-1"></i>{{ $requirement->city ?? 'No City' }}@if($requirement->delivery_pincode), {{ $requirement->delivery_pincode }}@endif</span>
                        <span><i class="ti ti-clock me-1"></i>Listed {{ $requirement->created_at->format('d M Y') }}</span>
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
                                <i class="ti ti-eye"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-3">{{ number_format($requirement->views_count) }}</div>
                            <div class="text-secondary">Total Views</div>
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
                                <i class="ti ti-bulb"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-3">{{ number_format($requirement->leads_count) }}</div>
                            <div class="text-secondary">Total Leads</div>
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
                            <div class="fw-bold fs-3">{{ $requirement->is_featured ? 'Yes' : 'No' }}</div>
                            <div class="text-secondary">Featured {{ $requirement->featured_at ? $requirement->featured_at->diffForHumans() : '' }}</div>
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
                                <i class="ti ti-clock"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold">
                                {{ $requirement->expires_at ? $requirement->expires_at->format('d M, Y') : 'No Expiry' }}
                            </div>
                            <div class="text-secondary">
                                Expires {{ $requirement->expires_at ? $requirement->expires_at->diffForHumans() : '' }}
                            </div>
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
                        <i class="ti ti-info-circle me-1"></i>Overview
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-images" role="tab">
                        <i class="ti ti-photo me-1"></i>Images <span class="badge bg-secondary-lt ms-1">{{ $requirement->images->count() }}</span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-buyer" role="tab">
                        <i class="ti ti-user me-1"></i>Buyer Info
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-views" role="tab">
                        <i class="ti ti-eye me-1"></i>Views
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-leads" role="tab">
                        <i class="ti ti-bulb me-1"></i>Leads / Enquiries
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">

                {{-- Tab: Overview --}}
                <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
                    <div class="row g-4 mb-4">
                        <div class="col-lg-6">
                            <h3 class="mb-3">Requirement Details</h3>
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Title</div>
                                    <div class="datagrid-content">{{ $requirement->title }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Slug</div>
                                    <div class="datagrid-content"><code>{{ $requirement->slug }}</code></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Category</div>
                                    <div class="datagrid-content">
                                        {{ $requirement->category->name ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Condition</div>
                                    <div class="datagrid-content">
                                        <span class="badge bg-{{ $requirement->condition == \App\Models\Requirement::CONDITION_NEW ? 'green' : 'orange' }}-lt">
                                            {{ $requirement->condition_name }}
                                        </span>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Minimum Quantity</div>
                                    <div class="datagrid-content">{{ $requirement->quantity }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Status</div>
                                    <div class="datagrid-content">
                                        <span class="badge bg-{{ $requirement->status_color }}-lt">
                                            <i class="ti ti-circle-filled me-1"></i>{{ $requirement->status_name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <h3 class="mb-3">Pricing & Location</h3>
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Price</div>
                                    <div class="datagrid-content fw-bold text-primary">₹{{ number_format($requirement->target_budget, 2) }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Price Unit</div>
                                    <div class="datagrid-content">{{ $requirement->target_budget_unit ?? 'N/A' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Negotiable</div>
                                    <div class="datagrid-content">
                                        @if($requirement->is_negotiable)
                                            <span class="badge bg-green-lt"><i class="ti ti-check me-1"></i>Yes</span>
                                        @else
                                            <span class="badge bg-secondary-lt">No</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Location</div>
                                    <div class="datagrid-content">{{ $requirement->delivery_location ?? 'Not provided' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">City</div>
                                    <div class="datagrid-content">{{ $requirement->city ?? 'N/A' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Pincode</div>
                                    <div class="datagrid-content">{{ $requirement->delivery_pincode ?? 'N/A' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Created</div>
                                    <div class="datagrid-content">{{ $requirement->created_at->format('d M Y, h:i A') }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Last Updated</div>
                                    <div class="datagrid-content">{{ $requirement->updated_at->format('d M Y, h:i A') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($requirement->description)
                        <div class="mt-3">
                            <h3 class="mb-2">Description</h3>
                            <div class="card card-body bg-light border-0">
                                <p class="mb-0" style="white-space: pre-wrap;">{{ $requirement->description }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Tab: Images --}}
                <div class="tab-pane fade" id="tab-images" role="tabpanel">
                    <div class="p-3">
                        @if($requirement->images->count() > 0)
                            <div class="row g-3">
                                @foreach($requirement->images as $image)
                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                        <div class="card card-sm shadow-sm">
                                            <a href="{{ $image->url }}" target="_blank" class="d-block bg-light">
                                                <img src="{{ $image->url }}" class="card-img-top" alt="Requirement Image"
                                                    style="height: 160px; object-fit: contain; background-color: #f8f9fa;">
                                            </a>
                                        <div class="card-body p-2 text-center">
                                            @if($image->is_primary)
                                                <span class="badge bg-primary-lt"><i class="ti ti-star me-1"></i>Primary</span>
                                            @else
                                                <span class="badge bg-secondary-lt">Image #{{ $image->sort_order }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty py-5">
                            <div class="empty-icon">
                                <i class="ti ti-photo text-muted" style="font-size: 3rem;"></i>
                            </div>
                            <p class="empty-title">No Images Uploaded</p>
                            <p class="empty-subtitle text-secondary">
                                This requirement does not have any images yet.
                            </p>
                        </div>
                    @endif
                    </div>
                </div>

                {{-- Tab: Buyer Info --}}
                <div class="tab-pane fade" id="tab-buyer" role="tabpanel">
                    @if($requirement->buyer)
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            @if($requirement->buyer->profile_photo)
                                                <span class="avatar avatar-lg rounded-circle me-3" style="background-image: url({{ asset('storage/' . $requirement->buyer->profile_photo) }})"></span>
                                            @else
                                                <span class="avatar avatar-lg rounded-circle bg-primary-lt fs-3 fw-bold me-3">
                                                    {{ mb_strtoupper(mb_substr($requirement->buyer->name ?? 'U', 0, 2)) }}
                                                </span>
                                            @endif
                                            <div>
                                                <h3 class="mb-0">{{ $requirement->buyer->name ?? 'Unknown User' }}</h3>
                                                <div class="text-secondary small">
                                                    <span class="badge bg-{{ $requirement->buyer->status_color ?? 'secondary' }}-lt">
                                                        {{ $requirement->buyer->status_name ?? 'Unknown' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <a href="{{ route('admin.users.show', $requirement->buyer->id) }}" class="btn btn-outline-primary">
                                                    <i class="ti ti-external-link me-1"></i>View Full Profile
                                                </a>
                                            </div>
                                        </div>
                                        <div class="datagrid">
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Phone</div>
                                                <div class="datagrid-content">{{ $requirement->buyer->phone ?? 'N/A' }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Email</div>
                                                <div class="datagrid-content">{{ $requirement->buyer->email ?? 'Not provided' }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">City</div>
                                                <div class="datagrid-content">{{ $requirement->buyer->city ?? 'N/A' }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Joined</div>
                                                <div class="datagrid-content">{{ $requirement->buyer->created_at->format('d M Y') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="empty py-5">
                            <div class="empty-icon">
                                <i class="ti ti-user-off text-muted" style="font-size: 3rem;"></i>
                            </div>
                            <p class="empty-title">Buyer Not Found</p>
                            <p class="empty-subtitle text-secondary">
                                The buyer account associated with this requirement could not be found.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Tab: Views (Dummy for now) --}}
                <div class="tab-pane fade" id="tab-views" role="tabpanel">
                    <div class="mb-3">
                        <h3 class="mb-1">Recent Views</h3>
                        <p class="text-secondary">Users who viewed this requirement listing.</p>
                    </div>
                        <p class="text-secondary">No views tracked for requirements.</p>

                {{-- Tab: Leads / Enquiries (Dummy for now) --}}
                <div class="tab-pane fade" id="tab-leads" role="tabpanel">
                    <div class="mb-3">
                        <h3 class="mb-1">Leads & Enquiries</h3>
                        <p class="text-secondary">Enquiries and buy leads received for this requirement.</p>
                    </div>
                    <div class="empty py-5">
                        <div class="empty-icon">
                            <i class="ti ti-bulb text-muted" style="font-size: 3rem;"></i>
                        </div>
                        <p class="empty-title">No Leads or Enquiries</p>
                        <p class="empty-subtitle text-secondary">
                            Buy leads and enquiries for this requirement will appear here once the leads module is active.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Comments / Admin Notes --}}
    <x-comments type="{{ \App\Models\Comment::TYPE_PRODUCT }}" :model-id="$requirement->id" />

@endsection

@push('scripts')
<script>
    $(function() {
        // Status change from dropdown
        $(document).on('click', '.status-change-btn', function (e) {
            e.preventDefault();
            let id = $(this).data('id');
            let status = $(this).data('status');

            Swal.fire({
                title: 'Change Requirement Status?',
                text: 'Are you sure you want to change this requirement\'s status?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Change',
                customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-light ms-2' },
                buttonsStyling: false,
            }).then(function (result) {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: '/admin/requirements/' + id + '/status',
                    type: 'POST',
                    data: { status: status, _token: '{{ csrf_token() }}' },
                    success: function () {
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: 'Status updated' });
                        setTimeout(function () { location.reload(); }, 1500);
                    },
                    error: function () {
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'error', title: 'Failed to update' });
                    }
                });
            });
        });

        // Fix DataTable column width in Bootstrap tabs
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            if($.fn.dataTable) {
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
            }
        });
    });
</script>
@endpush
