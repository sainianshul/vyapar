@extends('admin.layouts.app')

@section('title', 'Care Requests')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'Care Requests', 'url' => route('admin.requests.index')],
                    ['label' => !empty($isToday) ? 'Today\'s Requests' : 'All Requests'],
                ]" />
                <h2 class="page-title">{{ !empty($isToday) ? 'Today\'s Requests' : 'All Requests' }}</h2>
            </div>
        </div>
    </div>

    <div class="card">

        {{-- Toolbar --}}
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-3">

                {{-- Search --}}
                <div class="input-icon">
                    <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                    <input
                        type="text"
                        id="dt-search"
                        class="form-control"
                        style="width: 260px;"
                        placeholder="Search requests..."
                    />
                </div>

                {{-- Right Controls --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">

                    {{-- Refresh Button --}}
                    <button type="button" class="btn btn-icon btn-ghost-secondary" id="refresh-table-btn" data-bs-toggle="tooltip" title="Refresh">
                        <i class="ti ti-refresh"></i>
                    </button>

                    @if(empty($isToday))
                    {{-- Date Filter --}}
                    <div class="input-icon" style="width: 175px;">
                        <span class="input-icon-addon"><i class="ti ti-calendar"></i></span>
                        <input
                            type="text"
                            class="form-control cursor-pointer"
                            placeholder="Filter by Date"
                            id="filter-date"
                        />
                        <span class="input-icon-addon cursor-pointer d-none" id="clear-date-btn" title="Clear Date">
                            <i class="ti ti-x"></i>
                        </span>
                    </div>
                    @endif

                    {{-- Status Filter --}}
                    <select id="filter-status" class="form-select" style="width: 160px;">
                        <option value="">All Status</option>
                        @foreach (\App\Models\CareRequest::getStatusList() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body">

            {{-- Loading Spinner --}}
            <div id="requests-loader" class="d-flex justify-content-center align-items-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading…</span>
                </div>
            </div>

            {{-- Table --}}
            <div id="requests-table-wrapper" class="table-responsive d-none">
                <table
                    id="requests-table"
                    class="table table-vcenter w-100"
                >
                    <thead>
                        <tr>
                            <th class="w-1">ID</th>
                            <th>User</th>
                            <th>Status</th>
                            <th>Date & Time</th>
                            <th>Location</th>
                            <th>Created At</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            @include('admin.layouts.partials._table-empty', [
                'id' => 'requests-empty'
            ])

        </div>
    </div>

@endsection

{{-- DataTables Bundle --}}
@push('datatables_css')
    @include('admin.layouts.partials._datatable-cdn-css')
@endpush

@push('datatables_js')
    @include('admin.layouts.partials._datatable-cdn-js')

    <script>
        $(function () {
            // ── Init ──────────────────────────────────────────────────────────
            let table = $('#requests-table').DataTable({
                serverSide: true,
                processing: false,
                ajax: {
                    url: '{{ route('admin.requests.data') }}',
                    data: function (d) {
                        d.status = $('#filter-status').val();
                        d.date = $('#filter-date').val();
                        d.is_today = '{{ $isToday ?? false }}';
                    }
                },
                columns: [
                    { data: 'reference_id', name: 'reference_id' },
                    { data: 'user', name: 'user', orderable: false, searchable: true },
                    { data: 'status', name: 'status', searchable: false },
                    { data: 'date_time', name: 'date_time', orderable: false, searchable: false },
                    { data: 'location', name: 'location', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at', searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end pe-3' },
                ],
                order: [[5, 'desc']],
                pageLength: 15,
                lengthMenu: [[10, 15, 25, 50], [10, 15, 25, 50]],
                dom:
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-3 pt-3 gy-2'" +
                    "<'col-sm-12 col-md-5'i>" +
                    "<'col-sm-12 col-md-7 d-flex justify-content-md-end align-items-center gap-3'lp>>",
                language: {
                    emptyTable: ' ',
                    zeroRecords: ' ',
                    loadingRecords: ' ',
                    info: 'Showing _START_–_END_ of _TOTAL_ requests',
                    infoEmpty: 'No requests to show',
                    infoFiltered: '(filtered from _MAX_)',
                    lengthMenu: 'Show _MENU_',
                    paginate: {
                        previous: '<i class="ti ti-chevron-left"></i>',
                        next: '<i class="ti ti-chevron-right"></i>',
                    },
                },
                initComplete: function () {
                    $('#requests-loader').remove();
                    $('#requests-table-wrapper').removeClass('d-none');
                },
                drawCallback: function () {
                    let total = this.api().page.info().recordsDisplay;
                    if (total === 0) {
                        $('#requests-table-wrapper').addClass('d-none');
                        $('#requests-empty').removeClass('d-none');
                    } else {
                        $('#requests-empty').addClass('d-none');
                        $('#requests-table-wrapper').removeClass('d-none');
                    }
                    $('[data-bs-toggle="tooltip"]').tooltip({ trigger: 'hover' });
                }
            });

            // ── Search ───────────────────────────────────────────────────────
            let searchTimer;
            $('#dt-search').on('input', function () {
                clearTimeout(searchTimer);
                let query = $(this).val();
                searchTimer = setTimeout(function () {
                    table.search(query).draw();
                }, 400);
            });

            // ── Status Filter ────────────────────────────────────────────────
            $('#filter-status').on('change', function () {
                table.ajax.reload();
            });

            // ── Refresh Button ───────────────────────────────────────────────
            $('#refresh-table-btn').on('click', function () {
                $(this).tooltip('hide');
                table.ajax.reload(null, false);
                Swal.fire({
                    toast: true,
                    position: 'top',
                    showConfirmButton: false,
                    timer: 1500,
                    icon: 'success',
                    title: 'Data refreshed successfully'
                });
            });

            @if(empty($isToday))
            // ── Date Filter ──────────────────────────────────────────────────
            let fp = $('#filter-date').flatpickr({
                altInput: true,
                altFormat: "d M Y",
                dateFormat: "Y-m-d",
                onChange: function(selectedDates, dateStr, instance) {
                    if (dateStr) {
                        $('#clear-date-btn').removeClass('d-none');
                    } else {
                        $('#clear-date-btn').addClass('d-none');
                    }
                    table.ajax.reload();
                }
            });

            $('#clear-date-btn').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                let fpInstance = document.querySelector('#filter-date')._flatpickr;
                if (fpInstance) {
                    fpInstance.clear();
                }
            });
            @endif


        });
    </script>
@endpush
