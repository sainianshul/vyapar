@extends('admin.layouts.app')

@section('title', 'Error Log #' . $error->error_id)

@section('content')

    <x-breadcrumb :items="[
        ['label' => 'System'],
        ['label' => 'Error Logs', 'url' => route('admin.system.error-logs')],
        ['label' => 'Error #' . $error->error_id],
    ]" />

    <!--begin::Header Card-->
    <div class="card shadow-sm mb-5 mb-xl-8 ep-border">
        <div class="card-header border-bottom border pt-6 pb-5">
            <div class="card-title m-0">
                <h3 class="fw-bold m-0 fs-3 d-flex align-items-center ep-label" style="letter-spacing: -0.5px;">
                    <i class="ti ti-shield-cross fs-1 text-danger me-3"></i>
                    Application Error Details
                </h3>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('admin.system.error-logs') }}" class="btn btn-sm btn btn-outline-dark fw-bold me-3">
                    <i class="ti ti-chevron-left h4 me-1"></i> Back to Logs
                </a>
                
                @if($error->status === \App\Models\ApplicationError::STATUS_PENDING)
                    <button type="button" class="btn btn-sm btn-warning fw-bold btn-status me-2" data-id="{{ $error->id }}" data-status="1">
                        <i class="ti ti-eye h4 me-1"></i> Mark Opened
                    </button>
                    <button type="button" class="btn btn-sm btn-success fw-bold btn-status" data-id="{{ $error->id }}" data-status="2">
                        <i class="ti ti-check h4 me-1"></i> Mark Resolved
                    </button>
                @elseif($error->status === \App\Models\ApplicationError::STATUS_OPENED)
                    <button type="button" class="btn btn-sm btn-success fw-bold btn-status" data-id="{{ $error->id }}" data-status="2">
                        <i class="ti ti-check h4 me-1"></i> Mark Resolved
                    </button>
                @else
                    <button type="button" class="btn btn-sm btn-warning fw-bold btn-status" data-id="{{ $error->id }}" data-status="1">
                        <i class="ti ti-refresh h4 me-1"></i> Re-open
                    </button>
                @endif
            </div>
        </div>

        <div class="card-body pt-8 pb-8">
            
            <div class="row g-5 mb-10">
                <!-- Message -->
                <div class="col-12">
                    <div class="p-6 rounded border ep-border ep-bg-light">
                        <span class="fs-8 text-uppercase fw-bold d-block mb-2 ep-label" style="letter-spacing: 0.5px;">
                            <i class="ti ti-message me-1 ep-icon"></i> Exception Message
                        </span>
                        <span class="h4 fw-normal ep-value" style="word-break: break-all; line-height: 1.5;">
                            {{ $error->message }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="row g-6 mb-10">
                <div class="col-md-3">
                    <div class="border rounded p-5 ep-border">
                        <div class="fs-8 text-uppercase fw-bold mb-3 d-flex align-items-center ep-label" style="letter-spacing: 0.5px;">
                            <i class="ti ti-chart-bar-line-up-2 me-2 ep-icon"></i> Severity
                        </div>
                        <div>
                            @if($error->severity === 1)
                                <span class="badge badge bg-blue-lt fw-bold px-3 py-2 small border border-primary border-dashed"><i class="ti ti-arrow-down me-1 text-primary"></i> Low</span>
                            @elseif($error->severity === 2)
                                <span class="badge badge bg-yellow-lt fw-bold px-3 py-2 small border border-warning border-dashed"><i class="ti ti-circle me-1 text-warning"></i> Medium</span>
                            @elseif($error->severity === 3)
                                <span class="badge badge bg-red-lt fw-bold px-3 py-2 small border border-danger border-dashed"><i class="ti ti-arrow-up me-1 text-danger"></i> High</span>
                            @elseif($error->severity === 4)
                                <span class="badge badge-dark fw-bold px-3 py-2 small"><i class="ti ti-circle me-1 text-white"></i> Critical</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="border rounded p-5 ep-border">
                        <div class="fs-8 text-uppercase fw-bold mb-3 d-flex align-items-center ep-label" style="letter-spacing: 0.5px;">
                            <i class="ti ti-circle me-2 ep-icon"></i> Status
                        </div>
                        <div>
                            @if($error->status === 0)
                                <span class="badge badge bg-red-lt fw-bold px-3 py-2 small border border-danger border-dashed"><i class="ti ti-clock me-1 text-danger"></i> Pending</span>
                            @elseif($error->status === 1)
                                <span class="badge badge bg-yellow-lt fw-bold px-3 py-2 small border border-warning border-dashed"><i class="ti ti-eye me-1 text-warning"></i> Opened</span>
                            @elseif($error->status === 2)
                                <span class="badge badge bg-green-lt fw-bold px-3 py-2 small border border-success border-dashed"><i class="ti ti-check-circle me-1 text-success"></i> Resolved</span>
                            @else
                                <span class="badge badge bg-secondary-lt fw-bold px-3 py-2 small border border-secondary border-dashed"><i class="ti ti-circle me-1 text-muted"></i> Unknown</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="border rounded p-5 ep-border">
                        <div class="fs-8 text-uppercase fw-bold mb-3 d-flex align-items-center ep-label" style="letter-spacing: 0.5px;">
                            <i class="ti ti-calendar-8 me-2 ep-icon"></i> Occurred At
                        </div>
                        <div class=" fw-normal ep-value">{{ $error->created_at->format('d M Y, h:i A') }}</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="border rounded p-5 ep-border">
                        <div class="fs-8 text-uppercase fw-bold mb-3 d-flex align-items-center ep-label" style="letter-spacing: 0.5px;">
                            <i class="ti ti-map-pin me-2 ep-icon"></i> IP Address
                        </div>
                        <div class=" fw-normal ep-value">{{ $error->ip_address ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
            
            <div class="row g-6">
                <div class="col-md-6">
                    <div class="border rounded p-5 h-100 ep-border">
                        <div class="fs-8 text-uppercase fw-bold mb-3 d-flex align-items-center ep-label" style="letter-spacing: 0.5px;">
                            <i class="ti ti-circle me-2 ep-icon"></i> Request Endpoint
                        </div>
                        <div class=" fw-normal d-flex align-items-center ep-value" style="word-break: break-all;">
                            @if($error->method)
                                <span class="badge badge-light-dark fw-bold me-2 px-2 py-1">{{ $error->method }}</span>
                            @endif
                            <span style="font-family: monospace;">{{ $error->url ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-5 h-100 ep-border">
                        <div class="fs-8 text-uppercase fw-bold mb-3 d-flex align-items-center ep-label" style="letter-spacing: 0.5px;">
                            <i class="ti ti-user me-2 ep-icon"></i> Authenticated User
                        </div>
                        <div class=" fw-normal d-flex align-items-center">
                            @if($error->user)
                                <div class="symbol symbol-30px symbol-circle me-3">
                                    <span class="symbol-label fw-bold ep-symbol">
                                        {{ strtoupper(substr($error->user->name, 0, 1)) }}
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold ep-value">{{ $error->user->name }}</span>
                                    <span class="fs-8 ep-value-muted">{{ $error->user->email }}</span>
                                </div>
                            @else
                                <span class="ep-value-muted">System Process / Guest Request</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!--end::Header Card-->

    <!--begin::Technical Details-->
    <div class="card shadow-sm mb-5 mb-xl-8 ep-border">
        <div class="card-header border-bottom border pt-6 pb-5">
            <h3 class="card-title fw-bold h4 align-items-center m-0 ep-label" style="letter-spacing: -0.5px;">
                <i class="ti ti-circle fs-3 me-2 ep-icon"></i>
                Technical Details
            </h3>
        </div>
        <div class="card-body pt-8 pb-8">
            
            <!-- Exception Class & File -->
            <div class="mb-10">
                <div class="d-flex flex-column gap-4">
                    <div class="d-flex align-items-center border rounded p-4 ep-border ep-bg-light-2">
                        <span class="fs-8 text-uppercase fw-bold min-w-100px ep-label" style="letter-spacing: 0.5px;">Class</span>
                        <span class="fw-normal ep-value" style="font-family: monospace; font-size: 0.95rem;">{{ $error->exception ?? 'N/A' }}</span>
                    </div>
                    <div class="d-flex align-items-center border rounded p-4 ep-border ep-bg-light-2">
                        <span class="fs-8 text-uppercase fw-bold min-w-100px ep-label" style="letter-spacing: 0.5px;">Location</span>
                        <span class="fw-normal ep-value" style="font-family: monospace; font-size: 0.95rem; word-break: break-all;">
                            {{ $error->file ?? 'N/A' }} 
                            <span class="fw-bold ms-3 px-2 py-1 rounded ep-symbol">Line: {{ $error->line ?? 'N/A' }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Request Data -->
            @if(!empty($error->request_data))
                <div class="mb-10">
                    <div class="fs-8 text-uppercase fw-bold mb-3 d-flex align-items-center ep-label" style="letter-spacing: 0.5px;">
                        <i class="ti ti-file me-2 ep-icon"></i> Request Payload
                    </div>
                    <div class="rounded border p-5 ep-border ep-bg-light-2" style="max-height: 300px; overflow-y: auto;">
                        <pre class="m-0 small fw-normal ep-value" style="font-family: 'Consolas', 'Monaco', monospace; white-space: pre-wrap;">{{ json_encode($error->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                </div>
            @endif

            <!-- Stack Trace -->
            @if(!empty($error->trace))
                <div class="mb-0">
                    <div class="fs-8 text-uppercase fw-bold mb-3 d-flex align-items-center ep-label" style="letter-spacing: 0.5px;">
                        <i class="ti ti-database me-2 ep-icon"></i> Stack Trace
                    </div>
                    <div class="rounded border p-5 ep-border ep-bg-light-2" style="max-height: 500px; overflow-y: auto;">
                        <pre class="m-0 fw-normal ep-value" style="font-family: 'Consolas', 'Monaco', monospace; white-space: pre-wrap; line-height: 1.6;">{{ $error->trace }}</pre>
                    </div>
                </div>
            @endif

        </div>
    </div>
    <!--end::Technical Details-->

    <x-comments type="{{ \App\Models\Comment::TYPE_LOGS }}" :model-id="$error->id" />

@endsection

@push('scripts')
<script>
    $(function () {
        $('.btn-status').on('click', function () {
            let id = $(this).data('id');
            let status = $(this).data('status');

            $.post(`/admin/system/error-logs/${id}/status`, {
                _token: '{{ csrf_token() }}',
                status: status
            }).done(function (res) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: res.message,
                    showConfirmButton: false,
                    timer: 1000
                });
                setTimeout(() => location.reload(), 1000);
            }).fail(function () {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Something went wrong.',
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        });
    });
</script>
@endpush
