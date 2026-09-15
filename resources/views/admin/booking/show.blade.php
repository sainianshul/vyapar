@extends('admin.layouts.app')

@section('title', 'Booking #' . $booking->reference_id)

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'Bookings', 'url' => route('admin.bookings.index')],
                    ['label' => '#' . $booking->reference_id],
                ]" />
                <h2 class="page-title">Booking <span class="text-primary">#{{ $booking->reference_id }}</span></h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
                @if($booking->careRequest)
                    <a href="{{ route('admin.requests.show', $booking->care_request_id) }}" class="btn btn-outline-primary">
                        <i class="ti ti-file-text me-1"></i>View Request
                    </a>
                @endif
            </div>
        </div>
    </div>

    <x-alert-success />

    {{-- Header Card — People Involved --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">

                {{-- Booked By --}}
                <div class="col-md-3 col-sm-6">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Booked By</div>
                    @if($booking->user)
                        <div class="d-flex align-items-center">
                            @if($booking->user->profile_photo)
                                <span class="avatar avatar-sm rounded-circle me-2" style="background-image: url({{ Storage::url($booking->user->profile_photo) }})"></span>
                            @else
                                <span class="avatar avatar-sm rounded-circle bg-primary-lt me-2">{{ mb_strtoupper(mb_substr($booking->user->name ?? 'U', 0, 1)) }}</span>
                            @endif
                            <div>
                                <a href="{{ route('admin.patients.show', $booking->user->id) }}" class="fw-semibold text-reset d-block">{{ $booking->user->name }}</a>
                                <span class="text-secondary small">{{ $booking->user->phone ?? $booking->user->email ?? 'N/A' }}</span>
                            </div>
                        </div>
                    @else
                        <span class="text-secondary small">Unknown</span>
                    @endif
                </div>

                {{-- Patient --}}
                <div class="col-md-3 col-sm-6 border-start">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Patient</div>
                    <div class="d-flex align-items-center">
                        <span class="avatar avatar-sm rounded-circle bg-info-lt me-2"><i class="ti ti-user"></i></span>
                        <div>
                            <span class="fw-semibold d-block">
                                {{ $booking->patient_name ?? ($booking->careRequest->patient_name ?? 'N/A') }}
                                @if($booking->patient_age || ($booking->careRequest && $booking->careRequest->patient_age))
                                    <span class="badge bg-cyan-lt ms-1">{{ $booking->patient_age ?? $booking->careRequest->patient_age }} yrs</span>
                                @endif
                            </span>
                            <span class="text-secondary small">{{ $booking->contact_phone ?? ($booking->careRequest->contact_phone ?? 'N/A') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Assigned Nurse --}}
                <div class="col-md-3 col-sm-6 border-start">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Assigned Nurse</div>
                    @if($booking->nurse && $booking->nurse->user)
                        <div class="d-flex align-items-center">
                            @if($booking->nurse->user->profile_photo)
                                <span class="avatar avatar-sm rounded-circle me-2" style="background-image: url({{ Storage::url($booking->nurse->user->profile_photo) }})"></span>
                            @else
                                <span class="avatar avatar-sm rounded-circle bg-green-lt me-2">{{ mb_strtoupper(mb_substr($booking->nurse->user->name ?? 'N', 0, 1)) }}</span>
                            @endif
                            <div>
                                <a href="{{ route('admin.nurses.show', $booking->nurse->user->id) }}" class="fw-semibold text-reset d-block">{{ $booking->nurse->user->name }}</a>
                                @if($booking->bid && $booking->bid->distance_km)
                                    <span class="text-secondary small">{{ $booking->bid->distance_km }} km away</span>
                                @else
                                    <span class="text-secondary small">ID: {{ $booking->nurse->id }}</span>
                                @endif
                            </div>
                        </div>
                    @else
                        <span class="text-secondary small"><i class="ti ti-minus me-1"></i>No nurse assigned</span>
                    @endif
                </div>

                {{-- Service Location --}}
                <div class="col-md-3 col-sm-6 border-start">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Location</div>
                    <div class="d-flex align-items-start">
                        <i class="ti ti-map-pin text-red me-2 mt-1"></i>
                        <div>
                            <span class="fw-semibold small d-block text-truncate" style="max-width: 200px;">{{ $booking->address ?? ($booking->careRequest->address ?? 'N/A') }}</span>
                            <span class="text-secondary small">{{ $booking->city ?? ($booking->careRequest->city ?? '') }} {{ $booking->pincode ?? ($booking->careRequest->pincode ?? '') }}</span>
                        </div>
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
                            @php
                                $statusBadgeColor = match(true) {
                                    str_contains($booking->status_color ?? '', 'success') => 'green',
                                    str_contains($booking->status_color ?? '', 'danger') => 'red',
                                    str_contains($booking->status_color ?? '', 'warning') => 'yellow',
                                    str_contains($booking->status_color ?? '', 'info') => 'cyan',
                                    default => 'primary',
                                };
                            @endphp
                            <span class="bg-{{ $statusBadgeColor }}-lt text-{{ $statusBadgeColor }} avatar">
                                <i class="ti ti-clipboard-check"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold">
                                <span class="badge bg-{{ $statusBadgeColor }}-lt">{{ $booking->status_text }}</span>
                            </div>
                            <div class="text-secondary small">Booking Status</div>
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
                            @php
                                $paymentBadgeColor = match(true) {
                                    str_contains($booking->payment_status_color ?? '', 'success') => 'green',
                                    str_contains($booking->payment_status_color ?? '', 'danger') => 'red',
                                    str_contains($booking->payment_status_color ?? '', 'warning') => 'yellow',
                                    default => 'cyan',
                                };
                            @endphp
                            <span class="bg-{{ $paymentBadgeColor }}-lt text-{{ $paymentBadgeColor }} avatar">
                                <i class="ti ti-currency-rupee"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold">
                                <span class="badge bg-{{ $paymentBadgeColor }}-lt">{{ $booking->payment_status_text }}</span>
                            </div>
                            <div class="text-secondary small">Payment Status</div>
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
                            <div class="fw-bold fs-3">₹{{ number_format($booking->total_amount, 2) }}</div>
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
                                <i class="ti ti-receipt-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-3">{{ $booking->completed_sessions }}/{{ $booking->total_sessions }}</div>
                            <div class="text-secondary small">Sessions Done</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Card --}}
    <div class="card mb-3">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="booking-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-overview" role="tab">
                        <i class="ti ti-info-circle me-1"></i>Overview
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-sessions" role="tab">
                        <i class="ti ti-calendar-event me-1"></i>Sessions
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-bids" role="tab">
                        <i class="ti ti-gavel me-1"></i>Bids
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-reviews" role="tab">
                        <i class="ti ti-star me-1"></i>Reviews
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-payments" role="tab">
                        <i class="ti ti-credit-card me-1"></i>Payment Logs
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content" id="booking-tabs-content">

                {{-- ── Overview Tab ──────────────────────────────────────── --}}
                <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">

                    {{-- Schedule + Financial details in datagrid --}}
                    <div class="row g-4 mb-4">
                        <div class="col-lg-6">
                            <h3 class="mb-3">Schedule & Service</h3>
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Care Type</div>
                                    <div class="datagrid-content">{{ $booking->careRequest->careType->name ?? 'N/A' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Start Date</div>
                                    <div class="datagrid-content">
                                        {{ $booking->start_date ? $booking->start_date->format('d M Y') : 'N/A' }}
                                        @if($booking->start_time)
                                            <span class="text-secondary ms-1">{{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">End Date</div>
                                    <div class="datagrid-content">
                                        {{ $booking->end_date ? $booking->end_date->format('d M Y') : 'N/A' }}
                                        @if($booking->end_time)
                                            <span class="text-secondary ms-1">{{ \Carbon\Carbon::parse($booking->end_time)->format('h:i A') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Total Sessions</div>
                                    <div class="datagrid-content">{{ $booking->total_sessions }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Completed Sessions</div>
                                    <div class="datagrid-content">
                                        <span class="badge bg-green-lt">{{ $booking->completed_sessions }}</span>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Created At</div>
                                    <div class="datagrid-content">{{ $booking->created_at->format('d M Y, h:i A') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <h3 class="mb-3">Financial Details</h3>
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Total Amount</div>
                                    <div class="datagrid-content fw-bold">₹{{ number_format($booking->total_amount, 2) }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Commission Earned</div>
                                    <div class="datagrid-content fw-bold text-green">₹{{ number_format($booking->commission_amount, 2) }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Commission Setup</div>
                                    <div class="datagrid-content">
                                        @if($booking->commission_type == 1) {{ $booking->commission_value }}%
                                        @elseif($booking->commission_type == 2) ₹{{ $booking->commission_value }} (Flat)
                                        @else N/A @endif
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Per Session Rate</div>
                                    <div class="datagrid-content">₹{{ number_format($booking->per_session_rate, 2) }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Nurse/Session Rate</div>
                                    <div class="datagrid-content">₹{{ number_format($booking->nurse_per_session_rate, 2) }}</div>
                                </div>
                                @if($booking->refund_amount > 0)
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Refund Amount</div>
                                    <div class="datagrid-content fw-bold text-red">₹{{ number_format($booking->refund_amount, 2) }}</div>
                                </div>
                                @endif
                                @if($booking->nurse_payout_amount > 0)
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Nurse Payout</div>
                                    <div class="datagrid-content fw-bold text-cyan">₹{{ number_format($booking->nurse_payout_amount, 2) }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Payment & Bid Details --}}
                    <div class="row g-4 mb-4">
                        <div class="col-lg-6">
                            <h3 class="mb-3">Payment Details</h3>
                            <div class="datagrid">
                                @if($booking->payment_method)
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Payment Method</div>
                                    <div class="datagrid-content">
                                        <span class="badge bg-blue-lt">{{ $booking->payment_method_text }}</span>
                                    </div>
                                </div>
                                @endif
                                @if($booking->wallet_amount_used > 0)
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Wallet Used</div>
                                    <div class="datagrid-content">₹{{ number_format($booking->wallet_amount_used, 2) }}</div>
                                </div>
                                @endif
                                @if($booking->gateway_amount > 0)
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Gateway Paid</div>
                                    <div class="datagrid-content">₹{{ number_format($booking->gateway_amount, 2) }}</div>
                                </div>
                                @endif
                                @if($booking->gateway_order_id)
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Gateway Order ID</div>
                                    <div class="datagrid-content"><code>{{ $booking->gateway_order_id }}</code></div>
                                </div>
                                @endif
                                @if($booking->gateway_payment_id)
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Gateway Payment ID</div>
                                    <div class="datagrid-content"><code>{{ $booking->gateway_payment_id }}</code></div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <h3 class="mb-3">Bid & References</h3>
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Booking ID</div>
                                    <div class="datagrid-content">{{ $booking->id }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Bid ID</div>
                                    <div class="datagrid-content">{{ $booking->bid_id ?? 'N/A' }}</div>
                                </div>
                                @if($booking->bid)
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">Nurse Bid Amount</div>
                                        <div class="datagrid-content fw-bold">₹{{ number_format($booking->bid->nurse_amount, 2) }}</div>
                                    </div>
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">Total Bid Amount</div>
                                        <div class="datagrid-content fw-bold">₹{{ number_format($booking->bid->total_amount, 2) }}</div>
                                    </div>
                                    @if($booking->bid->notes)
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Nurse Notes</div>
                                            <div class="datagrid-content">{{ $booking->bid->notes }}</div>
                                        </div>
                                    @endif
                                @endif
                                @if($booking->careRequest)
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">Linked Request</div>
                                        <div class="datagrid-content">
                                            <a href="{{ route('admin.requests.show', $booking->care_request_id) }}" class="text-primary fw-semibold">#{{ $booking->careRequest->reference_id ?? $booking->care_request_id }}</a>
                                        </div>
                                    </div>
                                @endif
                                @if($booking->parentBooking)
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">Parent Booking</div>
                                        <div class="datagrid-content">
                                            <a href="{{ route('admin.bookings.show', $booking->parent_booking_id) }}" class="text-yellow fw-semibold">#{{ $booking->parentBooking->reference_id }}</a>
                                        </div>
                                    </div>
                                @endif
                                @if($booking->extensions->count() > 0)
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">Extensions</div>
                                        <div class="datagrid-content">
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($booking->extensions as $ext)
                                                    <a href="{{ route('admin.bookings.show', $ext->id) }}" class="badge bg-blue-lt">#{{ $ext->reference_id }}</a>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Cancellation Info --}}
                    @if($booking->isCancelled())
                        <hr class="my-4">
                        <div class="alert alert-danger">
                            <div class="d-flex align-items-center mb-2">
                                <i class="ti ti-x-circle me-2"></i>
                                <h3 class="alert-title mb-0">Cancellation Details</h3>
                            </div>
                            <div class="datagrid mt-2">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Cancelled By</div>
                                    <div class="datagrid-content">
                                        @switch($booking->cancelled_by)
                                            @case(1) <span class="badge bg-yellow-lt">User</span> @break
                                            @case(2) <span class="badge bg-cyan-lt">Nurse</span> @break
                                            @case(3) <span class="badge bg-red-lt">Admin</span> @break
                                            @case(4) <span class="badge bg-secondary-lt">System</span> @break
                                            @default <span class="text-secondary">Unknown</span>
                                        @endswitch
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Cancelled At</div>
                                    <div class="datagrid-content">{{ $booking->cancelled_at ? $booking->cancelled_at->format('d M Y, h:i A') : 'N/A' }}</div>
                                </div>
                                @if($booking->cancellation_reason)
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Reason</div>
                                    <div class="datagrid-content">{{ $booking->cancellation_reason }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    @endif

                </div>

                {{-- ── Sessions Tab ──────────────────────────────────────── --}}
                <div class="tab-pane fade" id="tab-sessions" role="tabpanel">
                    <h3 class="mb-1">Sessions</h3>
                    <p class="text-secondary small mb-3">{{ $booking->completed_sessions }} of {{ $booking->total_sessions }} completed</p>
                    @include('admin.layouts.partials._table-loader', ['id' => 'sessions-loader'])
                    <div id="sessions-table-wrapper" class="table-responsive d-none">
                        <table id="sessions-table" class="table table-vcenter w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Started At</th>
                                    <th>Ended At</th>
                                    <th>Status</th>
                                    <th>OTP</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                {{-- ── Bids Tab ──────────────────────────────────── --}}
                <div class="tab-pane fade" id="tab-bids" role="tabpanel">
                    @if($booking->careRequest && $booking->careRequest->bids->count() > 0)
                        <h3 class="mb-1">All Bids</h3>
                        <p class="text-secondary small mb-3">{{ $booking->careRequest->bids->count() }} bids total</p>
                        @include('admin.layouts.partials._table-loader', ['id' => 'bids-loader'])
                        <div id="bids-table-wrapper" class="table-responsive d-none">
                            <table id="bids-table" class="table table-vcenter w-100">
                                <thead>
                                    <tr>
                                        <th>Nurse</th>
                                        <th>Nurse Amount</th>
                                        <th>Commission</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty">
                            <div class="empty-icon"><i class="ti ti-gavel"></i></div>
                            <p class="empty-title">No bids</p>
                            <p class="empty-subtitle text-secondary">No bids were placed for this booking.</p>
                        </div>
                    @endif
                </div>

                {{-- ── Reviews Tab ───────────────────────────────────────── --}}
                <div class="tab-pane fade" id="tab-reviews" role="tabpanel">
                    <h3 class="mb-3">Ratings & Reviews</h3>
                    @include('admin.layouts.partials._table-loader', ['id' => 'ratings-loader'])
                    <div id="ratings-table-wrapper" class="table-responsive d-none">
                        <table id="ratings-table" class="table table-vcenter w-100">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Rating</th>
                                    <th>Review</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                {{-- ── Payment Logs Tab ──────────────────────────────────── --}}
                <div class="tab-pane fade" id="tab-payments" role="tabpanel">
                    <h3 class="mb-3">Payment Logs</h3>
                    @include('admin.layouts.partials._table-loader', ['id' => 'payment-logs-loader'])
                    <div id="payment-logs-table-wrapper" class="table-responsive d-none">
                        <table id="payment-logs-table" class="table table-vcenter w-100">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Amount</th>
                                    <th>Gateway</th>
                                    <th>Order ID</th>
                                    <th>Payment ID</th>
                                    <th>Status</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Comments / Admin Notes --}}
    <x-comments type="{{ \App\Models\Comment::TYPE_BOOKING }}" :model-id="$booking->id" />

@endsection

@push('datatables_css')
    @include('admin.layouts.partials._datatable-cdn-css')
@endpush

@push('datatables_js')
    @include('admin.layouts.partials._datatable-cdn-js')
    <script>
        $(document).ready(function () {

            // Common DataTable options
            const getDtOpts = (loaderId, wrapperId) => {
                return {
                    processing: false,
                    serverSide: true,
                    paging: true,
                    pageLength: 5,
                    lengthMenu: [5, 10, 25],
                    searching: false,
                    info: true,
                    ordering: false,
                    dom:
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row mt-3'" +
                        "<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'i>" +
                        "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>>",
                    language: {
                        emptyTable: "No data available.",
                        info: "Showing _START_ to _END_ of _TOTAL_",
                        infoEmpty: "Showing 0 to 0 of 0",
                        paginate: {
                            previous: '<i class="ti ti-chevron-left"></i>',
                            next: '<i class="ti ti-chevron-right"></i>',
                        }
                    },
                    initComplete: function () {
                        $('#' + loaderId).remove();
                        $('#' + wrapperId).removeClass('d-none');
                    }
                };
            };

            // Track which tabs have been initialized
            var initialized = {};

            function initSessions() {
                if (initialized.sessions) return;
                initialized.sessions = true;
                $('#sessions-table').DataTable(Object.assign({}, getDtOpts('sessions-loader', 'sessions-table-wrapper'), {
                    ajax: '{{ route('admin.bookings.sessions-data', $booking->id) }}',
                    columns: [
                        { data: 'session_number', className: 'fw-bold' },
                        { data: 'session_date', className: 'fw-semibold' },
                        { data: 'start_time', className: 'text-secondary' },
                        { data: 'end_time', className: 'text-secondary' },
                        { data: 'started_at', className: 'text-secondary' },
                        { data: 'ended_at', className: 'text-secondary' },
                        { data: 'status' },
                        { data: 'otp_verified' },
                        { data: 'nurse_notes', className: 'text-secondary', render: function(data) {
                            return '<div style="max-width:150px; white-space:normal;">' + data + '</div>';
                        }}
                    ]
                }));
            }

            function initBids() {
                if (initialized.bids) return;
                initialized.bids = true;
                @if($booking->care_request_id)
                    $('#bids-table').DataTable(Object.assign({}, getDtOpts('bids-loader', 'bids-table-wrapper'), {
                        ajax: '{{ route('admin.bookings.bids-data', $booking->id) }}',
                        columns: [
                            { data: 'nurse' },
                            { data: 'nurse_amount' },
                            { data: 'commission' },
                            { data: 'total' },
                            { data: 'status' },
                            { data: 'notes' }
                        ]
                    }));
                @endif
            }

            function initReviews() {
                if (initialized.reviews) return;
                initialized.reviews = true;
                $('#ratings-table').DataTable(Object.assign({}, getDtOpts('ratings-loader', 'ratings-table-wrapper'), {
                    ajax: '{{ route('admin.bookings.reviews-data', $booking->id) }}',
                    columns: [
                        { data: 'user' },
                        { data: 'rating' },
                        { data: 'review', className: 'text-secondary small text-wrap' },
                        { data: 'created_at' }
                    ]
                }));
            }

            function initPayments() {
                if (initialized.payments) return;
                initialized.payments = true;
                $('#payment-logs-table').DataTable(Object.assign({}, getDtOpts('payment-logs-loader', 'payment-logs-table-wrapper'), {
                    ajax: '{{ route('admin.bookings.payment-logs-data', $booking->id) }}',
                    columns: [
                        { data: 'event' },
                        { data: 'amount' },
                        { data: 'gateway' },
                        { data: 'gateway_order_id' },
                        { data: 'gateway_payment_id' },
                        { data: 'status' },
                        { data: 'created_at', className: 'text-secondary' }
                    ]
                }));
            }

            // Initialize DataTables when their tab is shown (lazy loading)
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                var target = $(e.target).attr('href');
                if (target === '#tab-sessions') initSessions();
                else if (target === '#tab-bids') initBids();
                else if (target === '#tab-reviews') initReviews();
                else if (target === '#tab-payments') initPayments();
            });

        });
    </script>
@endpush
