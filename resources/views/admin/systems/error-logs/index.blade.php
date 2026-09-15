@extends('admin.layouts.app')

@section('title', 'Application Errors')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'System', 'url' => route('admin.system.error-logs')],
                    ['label' => 'Application Errors'],
                ]" />
                <h2 class="page-title">Application Errors</h2>
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
                        placeholder="Search error logs..."
                    />
                </div>

                {{-- Right Controls --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">

                    {{-- Refresh Button --}}
                    <button type="button" class="btn btn-icon btn-ghost-secondary" id="refresh-table-btn" data-bs-toggle="tooltip" title="Refresh">
                        <i class="ti ti-refresh"></i>
                    </button>

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

                    {{-- Status Filter --}}
                    <select id="filter-status" class="form-select" style="width: 160px;">
                        <option value="">All Status</option>
                        <option value="0">Pending</option>
                        <option value="1">Opened</option>
                        <option value="2">Resolved</option>
                    </select>

                    {{-- Severity Filter --}}
                    <select id="filter-severity" class="form-select" style="width: 160px;">
                        <option value="">All Severity</option>
                        <option value="1">Low</option>
                        <option value="2">Medium</option>
                        <option value="3">High</option>
                        <option value="4">Critical</option>
                    </select>

                    {{-- Empty Logs Button --}}
                    <button type="button" id="btn-empty-logs" class="btn btn-outline-danger">
                        <i class="ti ti-trash me-1"></i> Empty Logs
                    </button>

                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body">

            {{-- Loading Spinner --}}
            <div id="errors-loader" class="d-flex justify-content-center align-items-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading…</span>
                </div>
            </div>

            {{-- Table --}}
            <div id="errors-table-wrapper" class="table-responsive d-none">
                <table id="errors-table" class="table table-vcenter w-100">
                    <thead>
                        <tr>
                            <th class="w-1">Error ID</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Method</th>
                            <th>URL</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            @include('admin.layouts.partials._table-empty', [
                'id' => 'errors-empty'
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
            // ── Init DataTable ────────────────────────────────────────────────
            let table = $('#errors-table').DataTable({
                serverSide: true,
                processing: false,
                ajax: {
                    url: '{{ route('admin.system.errors.data') }}',
                    data: function (d) {
                        d.status = $('#filter-status').val();
                        d.severity = $('#filter-severity').val();
                        d.date = $('#filter-date').val();
                    }
                },
                columns: [
                    { data: 'error_id', name: 'error_id' },
                    { data: 'severity', name: 'severity', orderable: false, searchable: false },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'method', name: 'method', orderable: false, searchable: false },
                    { data: 'url', name: 'url', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at', orderable: true, searchable: false },
                ],
                order: [[5, 'desc']],
                pageLength: 15,
                lengthMenu: [[10, 15, 25, 50], [10, 15, 25, 50]],
                dom:
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-3 pt-3 flex-nowrap'" +
                    "<'col-sm-12 col-md-5'i>" +
                    "<'col-sm-12 col-md-7 d-flex justify-content-md-end align-items-center gap-3'lp>>",
                language: {
                    emptyTable: ' ',
                    zeroRecords: ' ',
                    loadingRecords: ' ',
                    info: 'Showing _START_–_END_ of _TOTAL_ error logs',
                    infoEmpty: 'No error logs to show',
                    infoFiltered: '(filtered from _MAX_)',
                    lengthMenu: 'Show _MENU_',
                    paginate: {
                        previous: '<i class="ti ti-chevron-left"></i>',
                        next: '<i class="ti ti-chevron-right"></i>',
                    },
                },
                initComplete: function () {
                    $('#errors-loader').remove();
                    $('#errors-table-wrapper').removeClass('d-none');
                },
                drawCallback: function () {
                    let total = this.api().page.info().recordsDisplay;
                    if (total === 0) {
                        $('#errors-table-wrapper').addClass('d-none');
                        $('#errors-empty').removeClass('d-none');
                    } else {
                        $('#errors-empty').addClass('d-none');
                        $('#errors-table-wrapper').removeClass('d-none');
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

            // ── Filters ─────────────────────────────────────────────────────
            $('#filter-status, #filter-severity').on('change', function () {
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

            // ── Date Filter (Flatpickr) ──────────────────────────────────────
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

            // ── Empty Logs Action ─────────────────────────────────────────────
            $('#btn-empty-logs').on('click', function () {
                Swal.fire({
                    title: 'Empty Error Logs?',
                    text: 'All application error logs will be permanently removed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, Empty Logs',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('{{ route('admin.system.errors.empty') }}', {
                            _token: '{{ csrf_token() }}'
                        }).done(function () {
                            table.ajax.reload();
                            toastr.success('All logs cleared successfully.');
                        }).fail(function () {
                            toastr.error('Failed to clear logs.');
                        });
                    }
                });
            });
        });
    </script>
@endpush
