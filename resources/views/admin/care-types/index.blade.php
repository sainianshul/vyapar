@extends('admin.layouts.app')

@section('title', 'Care Types')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'Services'],
                    ['label' => 'Care Types', 'url' => route('admin.services.care-types.index')],
                ]" />
                <h2 class="page-title">All Care Types</h2>
            </div>
            <!-- Right Controls -->
            <div class="col-auto ms-auto d-print-none">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.services.care-types.create') }}" class="btn btn-primary d-none d-sm-inline-block">
                        <i class="ti ti-plus me-1"></i>Add Care Type
                    </a>
                    <a href="{{ route('admin.services.care-types.create') }}" class="btn btn-primary d-sm-none btn-icon" aria-label="Add Care Type">
                        <i class="ti ti-plus"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-dismissible bg-light-success border border-success border-dashed d-flex align-items-center p-4 mb-4">
            <i class="ti ti-check-circle fs-2x text-success me-3"></i>
            <div class="d-flex flex-column">
                <span class="fw-semibold text-body">{{ session('success') }}</span>
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                <i class="ti ti-x fs-1 text-success"></i>
            </button>
        </div>
    @endif

    <div class="card">
        
        {{-- Toolbar --}}
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-3">
                {{-- Search --}}
                <div class="input-icon">
                    <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                    <input
                        type="text"
                        id="search-care-types"
                        class="form-control"
                        style="width: 260px;"
                        placeholder="Search care types..."
                    />
                </div>
                
                {{-- Right Filter Controls --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-muted small fw-medium">
                        Total: {{ $careTypes->count() }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-vcenter table-striped w-100 m-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Commission Type</th>
                            <th>Commission Value</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th class="w-1 text-end pe-3">Options</th>
                        </tr>
                    </thead>
                    <tbody id="care-types-table-body">
                        @forelse($careTypes as $careType)
                            <tr class="care-type-row" data-name="{{ strtolower($careType->name) }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if(!empty(trim($careType->image_path ?? '')))
                                            <div class="avatar avatar-md me-3" style="background-image: url({{ Storage::url($careType->image_path) }})"></div>
                                        @else
                                            <div class="avatar avatar-md bg-light-primary text-primary me-3 fw-bold">
                                                {{ strtoupper(substr($careType->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('admin.services.care-types.edit', $careType) }}" class="text-body fw-medium text-decoration-none d-block">{{ $careType->name }}</a>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-body">{{ \App\Models\CareType::getCommisionTypeList()[$careType->commision_type] ?? 'Unknown' }}</div>
                                </td>
                                <td>
                                    <div class="fw-medium text-body">
                                        @if($careType->commision_type == \App\Models\CareType::COMMISION_TYPE_PERCENT)
                                            {{ $careType->commision_value ?? 0 }} %
                                        @elseif($careType->commision_type == \App\Models\CareType::COMMISION_TYPE_FLAT_FIXED)
                                            ₹{{ $careType->commision_value ?? 0 }} Flat
                                        @else
                                            ₹{{ $careType->commision_value ?? 0 }} / day
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($careType->status === \App\Models\CareType::STATUS_ACTIVE)
                                        <span class="badge badge-outline text-green border-green fs-9 px-2 py-1">Active</span>
                                    @elseif($careType->status === \App\Models\CareType::STATUS_INACTIVE)
                                        <span class="badge badge-outline text-red border-red fs-9 px-2 py-1">Inactive</span>
                                    @else
                                        <span class="badge badge-outline text-yellow border-yellow fs-9 px-2 py-1">Draft</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-body fw-medium">{{ $careType->created_at ? $careType->created_at->format('d M Y') : 'N/A' }}</div>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="{{ route('admin.services.care-types.show', $careType) }}" class="btn btn-sm btn-icon btn-light-primary border border-primary w-30px h-30px" data-bs-toggle="tooltip" title="View">
                                            <i class="ti ti-eye fs-5"></i>
                                        </a>
                                        <a href="{{ route('admin.services.care-types.edit', $careType) }}" class="btn btn-sm btn-icon btn-light-secondary border border-secondary w-30px h-30px" data-bs-toggle="tooltip" title="Edit">
                                            <i class="ti ti-edit fs-5"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-icon btn-light-danger border border-danger w-30px h-30px btn-delete" data-id="{{ $careType->id }}" data-name="{{ $careType->name }}" data-bs-toggle="tooltip" title="Delete">
                                            <i class="ti ti-trash fs-5"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted mb-3">No care types match your search or exist yet.</div>
                                    <a href="{{ route('admin.services.care-types.create') }}" class="btn btn-primary btn-sm">
                                        <i class="ti ti-plus me-1"></i>Add Care Type
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!--begin::No results (search)-->
            <div id="no-results" class="d-none text-center py-5">
                <p class="text-muted mb-0">No care types match your search.</p>
            </div>
            <!--end::No results-->
        </div>
    </div>

@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css" rel="stylesheet" />

@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
    <script>
        $(document).ready(function () {

            // ── Client-side search ──
            $('#search-care-types').on('input', function () {
                const q = $(this).val().toLowerCase().trim();
                let visible = 0;

                $('.care-type-row').each(function () {
                    const name = $(this).data('name') || '';
                    const match = name.includes(q);
                    $(this).toggleClass('d-none', !match);
                    if (match) visible++;
                });

                $('#no-results').toggleClass('d-none', visible > 0);
            });

            // ── Soft Delete ──
            $(document).on('click', '.btn-delete', function (e) {
                e.preventDefault();
                const id = $(this).data('id');
                const name = $(this).data('name');
                const row = $(this).closest('.care-type-row');

                Swal.fire({
                    title: 'Remove "' + name + '"?',
                    text: 'This care type will be archived and can be restored later.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove it',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-light ms-2'
                    },
                    buttonsStyling: false
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: '{{ route("admin.services.care-types.index") }}/' + id,
                        type: 'POST',
                        data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                        success: function () {
                            row.fadeOut(250, function () {
                                $(this).remove();
                                const remaining = $('.care-type-row:visible').length;
                                if (remaining === 0) {
                                    // Show inline empty message without full reload
                                    $('#care-types-table-body').append(`
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="text-muted mb-3">No care types found.</div>
                                                <a href="{{ route('admin.services.care-types.create') }}" class="btn btn-primary btn-sm">
                                                    <i class="ti ti-plus me-1"></i>Add Care Type
                                                </a>
                                            </td>
                                        </tr>
                                    `);
                                }
                            });
                            toastr.success('Care type archived successfully.');
                        },
                        error: function () {
                            toastr.error('Something went wrong. Please try again.');
                        }
                    });
                });
            });

            // ── Tooltips ──
            $('[data-bs-toggle="tooltip"]').tooltip({ trigger: 'hover' });

        });
    </script>
@endpush