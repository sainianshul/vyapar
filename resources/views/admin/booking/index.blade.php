@extends('admin.layouts.app')

@section('title', 'Bookings')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'Bookings', 'url' => route('admin.bookings.index')],
                    ['label' => $title ?? 'All Bookings'],
                ]" />
                <h2 class="page-title">{{ $title ?? 'All Bookings' }}</h2>
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
                        placeholder="Search bookings..."
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

                    @if(!isset($hideStatusFilter))
                    {{-- Status Filter --}}
                    <select id="filter-status" class="form-select" style="width: 160px;">
                        <option value="">All Status</option>
                        @foreach (\App\Models\Booking::getStatusList() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @endif

                    {{-- Payment Status Filter --}}
                    <select id="filter-payment" class="form-select" style="width: 165px;">
                        <option value="">All Payments</option>
                        @foreach (\App\Models\Booking::getPaymentStatusList() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>

                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body">

            {{-- Loading Spinner --}}
            <div id="bookings-loader" class="d-flex justify-content-center align-items-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading…</span>
                </div>
            </div>

            {{-- Table --}}
            <div id="bookings-table-wrapper" class="table-responsive d-none">
                <table id="bookings-table" class="table table-vcenter w-100">
                    <thead>
                        <tr>
                            <th class="w-1">ID</th>
                            <th>User</th>
                            <th>Nurse</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Sessions</th>
                            <th>Created At</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            @include('admin.layouts.partials._table-empty', [
                'id' => 'bookings-empty'
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
            let table = $('#bookings-table').DataTable({
                serverSide: true,
                processing: false,
                ajax: {
                    url: '{!! $dataUrl ?? route("admin.bookings.data") !!}',
                    data: function (d) {
                        if ($('#filter-status').length) {
                            d.status = $('#filter-status').val();
                        }
                        d.payment_status = $('#filter-payment').val();
                        d.date = $('#filter-date').val();
                    }
                },
                columns: [
                    { data: 'reference_id', name: 'reference_id' },
                    { data: 'user', name: 'user', orderable: false, searchable: true },
                    { data: 'nurse', name: 'nurse', orderable: false, searchable: false },
                    { data: 'amount', name: 'total_amount' },
                    { data: 'status', name: 'status' },
                    { data: 'payment_status', name: 'payment_status' },
                    { data: 'sessions', name: 'sessions', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end pe-3' },
                ],
                order: [[7, 'desc']],
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
                    info: 'Showing _START_–_END_ of _TOTAL_ bookings',
                    infoEmpty: 'No bookings to show',
                    infoFiltered: '(filtered from _MAX_)',
                    lengthMenu: 'Show _MENU_',
                    paginate: {
                        previous: '<i class="ti ti-chevron-left"></i>',
                        next: '<i class="ti ti-chevron-right"></i>',
                    },
                },
                initComplete: function () {
                    $('#bookings-loader').remove();
                    $('#bookings-table-wrapper').removeClass('d-none');
                },
                drawCallback: function () {
                    let total = this.api().page.info().recordsDisplay;
                    if (total === 0) {
                        $('#bookings-table-wrapper').addClass('d-none');
                        $('#bookings-empty').removeClass('d-none');
                    } else {
                        $('#bookings-empty').addClass('d-none');
                        $('#bookings-table-wrapper').removeClass('d-none');
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
            $('#filter-status, #filter-payment').on('change', function () {
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
        });
    </script>
@endpush
