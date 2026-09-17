@extends('admin.layouts.app')

@section('title', $product->title . ' — Product Details')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'Products', 'url' => route('admin.products.index')],
                    ['label' => 'Product Details'],
                ]" />
                <h2 class="page-title">Product Details</h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary">
                    <i class="ti ti-pencil me-1"></i>Edit Product
                </a>

                {{-- Status Change Dropdown --}}
                <div class="dropdown">
                    <button class="btn btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-settings me-1"></i>Status
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><h6 class="dropdown-header">Change Product Status</h6></li>
                        @foreach (\App\Models\Product::getStatusList() as $value => $label)
                            @if($product->status != $value)
                                <li>
                                    <a class="dropdown-item status-change-btn" href="#"
                                       data-id="{{ $product->id }}" data-status="{{ $value }}">
                                        @if($value == \App\Models\Product::STATUS_ACTIVE)
                                            <i class="ti ti-check me-2 text-success"></i>
                                        @elseif($value == \App\Models\Product::STATUS_SOLD)
                                            <i class="ti ti-tag me-2 text-info"></i>
                                        @elseif($value == \App\Models\Product::STATUS_EXPIRED)
                                            <i class="ti ti-clock-off me-2 text-warning"></i>
                                        @elseif($value == \App\Models\Product::STATUS_BLOCKED)
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

    {{-- Product Header Card --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto">
                    @if($product->primaryImage)
                        <span class="avatar avatar-xl rounded" style="background-image: url({{ $product->primaryImage->url }}); width: 120px; height: 120px; background-size: contain; background-color: #f8f9fa;"></span>
                    @else
                        <span class="avatar rounded bg-primary-lt fs-2 fw-bold" style="width: 120px; height: 120px;">
                            <i class="ti ti-package"></i>
                        </span>
                    @endif
                </div>
                <div class="col">
                    <div class="d-flex align-items-center mb-1 flex-wrap gap-2">
                        <h2 class="mb-0 me-1">{{ $product->title }}</h2>
                        <span class="badge bg-{{ $product->status_color }}-lt">
                            <i class="ti ti-circle-filled me-1"></i>{{ $product->status_name }}
                        </span>
                        @if($product->is_featured)
                            <span class="badge bg-yellow-lt"><i class="ti ti-star-filled me-1"></i>Featured</span>
                        @endif
                        <span class="badge bg-{{ $product->condition == \App\Models\Product::CONDITION_NEW ? 'green' : 'orange' }}-lt">
                            {{ $product->condition_name }}
                        </span>
                    </div>
                    <div class="d-flex flex-wrap gap-3 text-secondary small mt-1">
                        <span><i class="ti ti-currency-rupee me-1"></i>{{ number_format($product->price, 2) }}{{ $product->price_unit ? ' / ' . $product->price_unit : '' }}</span>
                        @if($product->is_negotiable)
                            <span class="text-success"><i class="ti ti-arrows-exchange me-1"></i>Negotiable</span>
                        @endif
                        <span><i class="ti ti-category me-1"></i>{{ $product->category->name ?? 'N/A' }}</span>
                        <span><i class="ti ti-map-pin me-1"></i>{{ $product->city ?? 'No City' }}@if($product->pincode), {{ $product->pincode }}@endif</span>
                        <span><i class="ti ti-clock me-1"></i>Listed {{ $product->created_at->format('d M Y') }}</span>
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
                            <div class="fw-bold fs-3">{{ number_format($product->views_count) }}</div>
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
                            <div class="fw-bold fs-3">{{ number_format($product->leads_count) }}</div>
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
                            <div class="fw-bold fs-3">{{ $product->is_featured ? 'Yes' : 'No' }}</div>
                            <div class="text-secondary">Featured {{ $product->featured_at ? $product->featured_at->diffForHumans() : '' }}</div>
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
                                {{ $product->expires_at ? $product->expires_at->format('d M, Y') : 'No Expiry' }}
                            </div>
                            <div class="text-secondary">
                                Expires {{ $product->expires_at ? $product->expires_at->diffForHumans() : '' }}
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
                        <i class="ti ti-photo me-1"></i>Images <span class="badge bg-secondary-lt ms-1">{{ $product->images->count() }}</span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-seller" role="tab">
                        <i class="ti ti-user me-1"></i>Seller Info
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
                            <h3 class="mb-3">Product Details</h3>
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Title</div>
                                    <div class="datagrid-content">{{ $product->title }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Slug</div>
                                    <div class="datagrid-content"><code>{{ $product->slug }}</code></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Category</div>
                                    <div class="datagrid-content">
                                        {{ $product->category->name ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Condition</div>
                                    <div class="datagrid-content">
                                        <span class="badge bg-{{ $product->condition == \App\Models\Product::CONDITION_NEW ? 'green' : 'orange' }}-lt">
                                            {{ $product->condition_name }}
                                        </span>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Minimum Quantity</div>
                                    <div class="datagrid-content">{{ $product->minimum_quantity }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Status</div>
                                    <div class="datagrid-content">
                                        <span class="badge bg-{{ $product->status_color }}-lt">
                                            <i class="ti ti-circle-filled me-1"></i>{{ $product->status_name }}
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
                                    <div class="datagrid-content fw-bold text-primary">₹{{ number_format($product->price, 2) }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Price Unit</div>
                                    <div class="datagrid-content">{{ $product->price_unit ?? 'N/A' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Negotiable</div>
                                    <div class="datagrid-content">
                                        @if($product->is_negotiable)
                                            <span class="badge bg-green-lt"><i class="ti ti-check me-1"></i>Yes</span>
                                        @else
                                            <span class="badge bg-secondary-lt">No</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Location</div>
                                    <div class="datagrid-content">{{ $product->location ?? 'Not provided' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">City</div>
                                    <div class="datagrid-content">{{ $product->city ?? 'N/A' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Pincode</div>
                                    <div class="datagrid-content">{{ $product->pincode ?? 'N/A' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Created</div>
                                    <div class="datagrid-content">{{ $product->created_at->format('d M Y, h:i A') }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Last Updated</div>
                                    <div class="datagrid-content">{{ $product->updated_at->format('d M Y, h:i A') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($product->description)
                        <div class="mt-3">
                            <h3 class="mb-2">Description</h3>
                            <div class="card card-body bg-light border-0">
                                <p class="mb-0" style="white-space: pre-wrap;">{{ $product->description }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Tab: Images --}}
                <div class="tab-pane fade" id="tab-images" role="tabpanel">
                    <div class="p-3">
                        @if($product->images->count() > 0)
                            <div class="row g-3">
                                @foreach($product->images as $image)
                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                        <div class="card card-sm shadow-sm">
                                            <a href="{{ $image->url }}" target="_blank" class="d-block bg-light">
                                                <img src="{{ $image->url }}" class="card-img-top" alt="Product Image"
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
                                This product does not have any images yet.
                            </p>
                        </div>
                    @endif
                    </div>
                </div>

                {{-- Tab: Seller Info --}}
                <div class="tab-pane fade" id="tab-seller" role="tabpanel">
                    @if($product->seller)
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            @if($product->seller->profile_photo)
                                                <span class="avatar avatar-lg rounded-circle me-3" style="background-image: url({{ asset('storage/' . $product->seller->profile_photo) }})"></span>
                                            @else
                                                <span class="avatar avatar-lg rounded-circle bg-primary-lt fs-3 fw-bold me-3">
                                                    {{ mb_strtoupper(mb_substr($product->seller->name ?? 'U', 0, 2)) }}
                                                </span>
                                            @endif
                                            <div>
                                                <h3 class="mb-0">{{ $product->seller->name ?? 'Unknown User' }}</h3>
                                                <div class="text-secondary small">
                                                    <span class="badge bg-{{ $product->seller->status_color ?? 'secondary' }}-lt">
                                                        {{ $product->seller->status_name ?? 'Unknown' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <a href="{{ route('admin.users.show', $product->seller->id) }}" class="btn btn-outline-primary">
                                                    <i class="ti ti-external-link me-1"></i>View Full Profile
                                                </a>
                                            </div>
                                        </div>
                                        <div class="datagrid">
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Phone</div>
                                                <div class="datagrid-content">{{ $product->seller->phone ?? 'N/A' }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Email</div>
                                                <div class="datagrid-content">{{ $product->seller->email ?? 'Not provided' }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">City</div>
                                                <div class="datagrid-content">{{ $product->seller->city ?? 'N/A' }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Joined</div>
                                                <div class="datagrid-content">{{ $product->seller->created_at->format('d M Y') }}</div>
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
                            <p class="empty-title">Seller Not Found</p>
                            <p class="empty-subtitle text-secondary">
                                The seller account associated with this product could not be found.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Tab: Views (Dummy for now) --}}
                <div class="tab-pane fade" id="tab-views" role="tabpanel">
                    <div class="mb-3">
                        <h3 class="mb-1">Recent Views</h3>
                        <p class="text-secondary">Users who viewed this product listing.</p>
                    </div>
                    @if($product->views_count > 0)
                        <div class="table-responsive">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Viewed At</th>
                                        <th>Source</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Dummy rows for now --}}
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="avatar avatar-sm bg-secondary-lt rounded-circle me-2">
                                                    <i class="ti ti-user"></i>
                                                </span>
                                                <div>
                                                    <div class="fw-semibold">Anonymous Viewer</div>
                                                    <div class="text-secondary small">Guest</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="text-secondary">{{ now()->subHours(2)->format('d M Y, H:i') }}</span></td>
                                        <td><span class="badge bg-blue-lt">App</span></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="avatar avatar-sm bg-secondary-lt rounded-circle me-2">
                                                    <i class="ti ti-user"></i>
                                                </span>
                                                <div>
                                                    <div class="fw-semibold">Anonymous Viewer</div>
                                                    <div class="text-secondary small">Guest</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="text-secondary">{{ now()->subHours(5)->format('d M Y, H:i') }}</span></td>
                                        <td><span class="badge bg-green-lt">Search</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty py-5">
                            <div class="empty-icon">
                                <i class="ti ti-eye-off text-muted" style="font-size: 3rem;"></i>
                            </div>
                            <p class="empty-title">No Views Yet</p>
                            <p class="empty-subtitle text-secondary">
                                This product has not been viewed by anyone yet. Views will be tracked once the app is live.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Tab: Leads / Enquiries (Dummy for now) --}}
                <div class="tab-pane fade" id="tab-leads" role="tabpanel">
                    <div class="mb-3">
                        <h3 class="mb-1">Leads & Enquiries</h3>
                        <p class="text-secondary">Enquiries and buy leads received for this product.</p>
                    </div>
                    <div class="empty py-5">
                        <div class="empty-icon">
                            <i class="ti ti-bulb text-muted" style="font-size: 3rem;"></i>
                        </div>
                        <p class="empty-title">No Leads or Enquiries</p>
                        <p class="empty-subtitle text-secondary">
                            Buy leads and enquiries for this product will appear here once the leads module is active.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Comments / Admin Notes --}}
    <x-comments type="{{ \App\Models\Comment::TYPE_PRODUCT }}" :model-id="$product->id" />

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
                title: 'Change Product Status?',
                text: 'Are you sure you want to change this product\'s status?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Change',
                customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-light ms-2' },
                buttonsStyling: false,
            }).then(function (result) {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: '/admin/products/' + id + '/status',
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
    });
</script>
@endpush
