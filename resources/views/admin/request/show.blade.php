@extends('admin.layouts.app')

@section('title', 'Request #' . ($careRequest->reference_id ?? $careRequest->id))

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'Care Requests', 'url' => route('admin.requests.index')],
                    ['label' => '#' . ($careRequest->reference_id ?? $careRequest->id)],
                ]" />
                <h2 class="page-title">Request <span class="text-primary">#{{ $careRequest->reference_id ?? 'N/A' }}</span></h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="{{ route('admin.requests.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    <x-alert-success />
    <x-form-errors />

    {{-- Header Card — People & Location --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">

                {{-- Requested By --}}
                <div class="col-md-4 col-sm-6">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Requested By</div>
                    @if($careRequest->user)
                        <div class="d-flex align-items-center">
                            @if($careRequest->user->profile_photo)
                                <span class="avatar avatar-sm rounded-circle me-2" style="background-image: url({{ Storage::url($careRequest->user->profile_photo) }})"></span>
                            @else
                                <span class="avatar avatar-sm rounded-circle bg-primary-lt me-2">{{ mb_strtoupper(mb_substr($careRequest->user->name ?? 'U', 0, 1)) }}</span>
                            @endif
                            <div>
                                <a href="{{ route('admin.patients.show', $careRequest->user->id) }}" class="fw-semibold text-reset d-block">{{ $careRequest->user->name }}</a>
                                <span class="text-secondary small">{{ $careRequest->user->phone ?? $careRequest->user->email ?? 'N/A' }}</span>
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
                                {{ $careRequest->patient_name ?? 'N/A' }}
                                @if($careRequest->patient_age)
                                    <span class="badge bg-cyan-lt ms-1">{{ $careRequest->patient_age }} yrs</span>
                                @endif
                                @if($careRequest->care_for === \App\Models\CareRequest::CARE_FOR_SELF)
                                    <span class="badge bg-green-lt ms-1">Self</span>
                                @else
                                    <span class="badge bg-yellow-lt ms-1">Family</span>
                                @endif
                            </span>
                            <span class="text-secondary small">{{ $careRequest->contact_phone ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Location --}}
                <div class="col-md-4 col-sm-6 border-start">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Location</div>
                    <div class="d-flex align-items-start">
                        <i class="ti ti-map-pin text-red me-2 mt-1"></i>
                        <div>
                            <span class="fw-semibold small d-block text-truncate" style="max-width: 250px;">{{ $careRequest->address ?? 'N/A' }}</span>
                            <span class="text-secondary small">{{ $careRequest->city ?? '' }} {{ $careRequest->pincode ?? '' }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    @php
        $statusBadgeColor = match(true) {
            str_contains($careRequest->status_color ?? '', 'success') => 'green',
            str_contains($careRequest->status_color ?? '', 'danger') => 'red',
            str_contains($careRequest->status_color ?? '', 'warning') => 'yellow',
            str_contains($careRequest->status_color ?? '', 'info') => 'cyan',
            default => 'primary',
        };

        $days = 1;
        if ($careRequest->start_date && $careRequest->end_date) {
            $days = $careRequest->start_date->diffInDays($careRequest->end_date) + 1;
        }

        $hours = 0;
        if ($careRequest->start_time && $careRequest->end_time) {
            $start = \Carbon\Carbon::parse($careRequest->start_time);
            $end = \Carbon\Carbon::parse($careRequest->end_time);
            if ($end->lessThan($start)) { $end->addDay(); }
            $hours = $start->diffInHours($end);
        }
    @endphp
    <div class="row mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-{{ $statusBadgeColor }}-lt text-{{ $statusBadgeColor }} avatar">
                                <i class="ti ti-clipboard-check"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold">
                                <span class="badge bg-{{ $statusBadgeColor }}-lt">{{ $careRequest->status_text ?? 'Unknown' }}</span>
                            </div>
                            <div class="text-secondary small">Status</div>
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
                                <i class="ti ti-stethoscope"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold">{{ $careRequest->careType->name ?? 'Unknown' }}</div>
                            <div class="text-secondary small">Care Type</div>
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
                                <i class="ti ti-calendar-stats"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-3">{{ $days }}</div>
                            <div class="text-secondary small">Day{{ $days > 1 ? 's' : '' }} Duration</div>
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
                                <i class="ti ti-gavel"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-3">{{ $careRequest->total_bids_received }}</div>
                            <div class="text-secondary small">Bids Received</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Card --}}
    <div class="card mb-3">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="request-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-overview" role="tab">
                        <i class="ti ti-info-circle me-1"></i>Overview
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-bids" role="tab">
                        <i class="ti ti-gavel me-1"></i>Bids
                    </a>
                </li>
                @if(in_array($careRequest->status, [\App\Models\CareRequest::STATUS_PENDING, \App\Models\CareRequest::STATUS_MATCHING, \App\Models\CareRequest::STATUS_FAILED_NO_BIDS]))
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-notified" role="tab">
                        <i class="ti ti-bell me-1"></i>Notified Nurses
                    </a>
                </li>
                @endif
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content" id="request-tabs-content">

                {{-- ── Overview Tab ──────────────────────────────────────── --}}
                <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">

                    <div class="row g-4 mb-4">
                        <div class="col-lg-6">
                            <h3 class="mb-3">Schedule & Timing</h3>
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Care Type</div>
                                    <div class="datagrid-content">{{ $careRequest->careType->name ?? 'Unknown' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Duration</div>
                                    <div class="datagrid-content">{{ $days }} Day{{ $days > 1 ? 's' : '' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Daily Shift</div>
                                    <div class="datagrid-content">{{ $hours }} Hour{{ $hours > 1 ? 's' : '' }}/Day</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Start Date</div>
                                    <div class="datagrid-content">
                                        {{ $careRequest->start_date ? $careRequest->start_date->format('d M Y') : 'N/A' }}
                                        @if($careRequest->start_time)
                                            <span class="text-secondary ms-1">{{ \Carbon\Carbon::parse($careRequest->start_time)->format('h:i A') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">End Date</div>
                                    <div class="datagrid-content">
                                        {{ $careRequest->end_date ? $careRequest->end_date->format('d M Y') : 'N/A' }}
                                        @if($careRequest->end_time)
                                            <span class="text-secondary ms-1">{{ \Carbon\Carbon::parse($careRequest->end_time)->format('h:i A') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Created At</div>
                                    <div class="datagrid-content">{{ $careRequest->created_at->format('d M Y, h:i A') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <h3 class="mb-3">Financials & Bidding</h3>
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Commission Type</div>
                                    <div class="datagrid-content">
                                        @if($careRequest->commission_type === 1) Percentage
                                        @elseif($careRequest->commission_type === 2) Flat Fixed
                                        @elseif($careRequest->commission_type === 3) Fixed Per Day
                                        @else Unknown @endif
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Commission Value</div>
                                    <div class="datagrid-content fw-bold">
                                        @if($careRequest->commission_type === 1)
                                            {{ $careRequest->commission_value }}%
                                        @else
                                            ₹{{ number_format($careRequest->commission_value, 2) }}
                                        @endif
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Pre-authorized Tip</div>
                                    <div class="datagrid-content fw-bold">₹{{ number_format($careRequest->tip_amount, 2) }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Bidding Ends At</div>
                                    <div class="datagrid-content">{{ $careRequest->bidding_ends_at ? $careRequest->bidding_ends_at->format('d M Y, h:i A') : 'N/A' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Radius Level</div>
                                    <div class="datagrid-content">{{ $careRequest->matching_attempt_level }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Total Bids</div>
                                    <div class="datagrid-content">
                                        <span class="badge bg-blue-lt">{{ $careRequest->total_bids_received }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($careRequest->notes)
                        <hr class="my-4">
                        <h3 class="mb-2">Patient Notes</h3>
                        <div class="text-secondary lh-lg">{{ $careRequest->notes }}</div>
                    @endif

                    {{-- Cancellation Info --}}
                    @if($careRequest->status === \App\Models\CareRequest::STATUS_CANCELLED)
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
                                        @switch($careRequest->cancelled_by)
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
                                    <div class="datagrid-content">{{ $careRequest->updated_at ? $careRequest->updated_at->format('d M Y, h:i A') : 'N/A' }}</div>
                                </div>
                                @if($careRequest->cancel_reason)
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Reason</div>
                                    <div class="datagrid-content">{{ $careRequest->cancel_reason }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    @endif

                </div>

                {{-- ── Bids Tab ──────────────────────────────────────────── --}}
                <div class="tab-pane fade" id="tab-bids" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="mb-0">Received Bids</h3>
                        <div class="d-flex align-items-center position-relative">
                            <i class="ti ti-search text-secondary position-absolute ms-2"></i>
                            <input type="text" id="bids-search"
                                class="form-control form-control-sm ps-7" style="width: 200px;"
                                placeholder="Search bids...">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="bids-table" class="table table-vcenter w-100">
                            <thead>
                                <tr>
                                    <th>Nurse</th>
                                    <th>Nurse Amt</th>
                                    <th>Comm.</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                {{-- ── Notified Nurses Tab ───────────────────────────────── --}}
                @if(in_array($careRequest->status, [\App\Models\CareRequest::STATUS_PENDING, \App\Models\CareRequest::STATUS_MATCHING, \App\Models\CareRequest::STATUS_FAILED_NO_BIDS]))
                <div class="tab-pane fade" id="tab-notified" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="mb-0">Notified Nurses</h3>
                        <div class="d-flex align-items-center position-relative">
                            <i class="ti ti-search text-secondary position-absolute ms-2"></i>
                            <input type="text" id="nurses-search"
                                class="form-control form-control-sm ps-7" style="width: 200px;"
                                placeholder="Search nurses...">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="notified-nurses-table" class="table table-vcenter w-100">
                            <thead>
                                <tr>
                                    <th>Nurse</th>
                                    <th>Dist.</th>
                                    <th>Notified At</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>

    {{-- Comments / Admin Notes --}}
    <x-comments type="{{ \App\Models\Comment::TYPE_CARE_REQUEST }}" :model-id="$careRequest->id" />

@endsection

@push('datatables_css')
    @include('admin.layouts.partials._datatable-cdn-css')
@endpush

@push('datatables_js')
    @include('admin.layouts.partials._datatable-cdn-js')
    <script>
        $(document).ready(function () {

            const dtOpts = {
                processing: false,
                serverSide: true,
                paging: true,
                pageLength: 5,
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                searching: true,
                info: true,
                dom:
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-3'" +
                    "<'col-sm-12 col-md-5 text-secondary'i>" +
                    "<'col-sm-12 col-md-7 d-flex justify-content-md-end align-items-center gap-2'lp>>",
                language: {
                    emptyTable: '<span class="text-secondary small">No data available.</span>',
                    zeroRecords: '<span class="text-secondary small">No matching results.</span>',
                    info: 'Showing _START_ to _END_ of _TOTAL_',
                    lengthMenu: '_MENU_',
                    paginate: {
                        previous: '<i class="ti ti-chevron-left"></i>',
                        next: '<i class="ti ti-chevron-right"></i>',
                    },
                }
            };

            // ── Bids DataTable ───────────────────────────────────────────
            let bidsTable = $('#bids-table').DataTable(Object.assign({}, dtOpts, {
                ajax: { url: '{{ route('admin.requests.bids-data', $careRequest->id) }}' },
                columns: [
                    { data: 'nurse', name: 'nurse', orderable: false, searchable: true },
                    { data: 'nurse_amount', name: 'nurse_amount' },
                    { data: 'commission_amount', name: 'commission_amount' },
                    { data: 'total_amount', name: 'total_amount' },
                    { data: 'status', name: 'status' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
                ],
                order: [[3, 'asc']],
            }));

            let bidsTimer;
            $('#bids-search').on('input', function () {
                clearTimeout(bidsTimer);
                let q = $(this).val();
                bidsTimer = setTimeout(() => bidsTable.search(q).draw(), 400);
            });

            // ── Notified Nurses DataTable ────────────────────────────────
            @if(in_array($careRequest->status, [\App\Models\CareRequest::STATUS_PENDING, \App\Models\CareRequest::STATUS_MATCHING, \App\Models\CareRequest::STATUS_FAILED_NO_BIDS]))
            let nursesTable = $('#notified-nurses-table').DataTable(Object.assign({}, dtOpts, {
                ajax: { url: '{{ route('admin.requests.notified-nurses-data', $careRequest->id) }}' },
                columns: [
                    { data: 'nurse', name: 'nurse', orderable: false, searchable: true },
                    { data: 'distance', name: 'distance', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'status', name: 'status' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
                ],
                order: [[2, 'desc']],
            }));

            let nursesTimer;
            $('#nurses-search').on('input', function () {
                clearTimeout(nursesTimer);
                let q = $(this).val();
                nursesTimer = setTimeout(() => nursesTable.search(q).draw(), 400);
            });
            @endif

            // Adjust columns when tabs are shown
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                let target = $(e.target).attr('href');
                if (target === '#tab-bids' && $.fn.DataTable.isDataTable('#bids-table')) {
                    $('#bids-table').DataTable().columns.adjust();
                } else if (target === '#tab-notified' && $.fn.DataTable.isDataTable('#notified-nurses-table')) {
                    $('#notified-nurses-table').DataTable().columns.adjust();
                }
            });

        });
    </script>
@endpush
